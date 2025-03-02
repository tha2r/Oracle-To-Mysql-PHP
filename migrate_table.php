<?php
include "config.php";
if ($argc < 2) {
    die("Usage: php ".$argv[0]." <TABLE_NAME>\n");
}

$table_name = strtoupper($argv[1]); // Get table name from CLI argument

// Start a MySQL transaction by disabling autocommit
$mysql_conn->autocommit(FALSE);

// Data Type Mapping
function mapType($type, $length, $precision, $scale) {
    if (strtoupper($type) === 'VARCHAR2' || strtoupper($type) === 'NVARCHAR2') {
        // Convert large VARCHAR columns to TEXT to avoid row size issues
        if ($length > 255) {
            return "TEXT";
        } else {
            return "VARCHAR($length)";
        }
    }
    return match (strtoupper($type)) {
        'NUMBER' => $precision ? ($scale > 0 ? "DECIMAL($precision,$scale)" : "INT($precision)") : "BIGINT",
        'DATE' => "DATETIME",
        'CLOB' => "TEXT",
        'BLOB' => "LONGBLOB",
        default => strtoupper($type)
    };
}

// Fetch Columns
$query = oci_parse($oracle_conn, "SELECT COLUMN_NAME, DATA_TYPE, DATA_LENGTH, DATA_PRECISION, DATA_SCALE, NULLABLE 
                                  FROM USER_TAB_COLUMNS WHERE TABLE_NAME = '$table_name' ORDER BY COLUMN_ID");
oci_execute($query);

$columns = [];
while ($col = oci_fetch_assoc($query)) {
    $columns[] = "`{$col['COLUMN_NAME']}` " . 
                 mapType($col['DATA_TYPE'], $col['DATA_LENGTH'], $col['DATA_PRECISION'], $col['DATA_SCALE']) . 
                 ($col['NULLABLE'] === 'N' ? " NOT NULL" : "");
}

if (!$columns) die("Table '$table_name' not found in Oracle.\n");

// Drop table if it exists
$drop_sql = "DROP TABLE IF EXISTS `$table_name`";
if (!$mysql_conn->query($drop_sql)) {
    die("MySQL Error on DROP TABLE: " . $mysql_conn->error . "\n");
}

// Create table
$create_sql = "CREATE TABLE `$table_name` (\n  " . implode(",\n  ", $columns) . "\n)";
if ($mysql_conn->query($create_sql)) {
    echo "Table `$table_name` created successfully in MySQL.\n";
} else {
    die("MySQL Error on CREATE TABLE: " . $mysql_conn->error . "\n");
}

// Cleanup
oci_free_statement($query);

// Oracle Query (select all columns from the specified table)
$oracle_query = "SELECT * FROM $table_name";
$oracle_stmt = oci_parse($oracle_conn, $oracle_query);
oci_execute($oracle_stmt);

$total_oracle_query = "SELECT COUNT(*) AS total_rows FROM $table_name";
$total_oracle_stmt = oci_parse($oracle_conn, $total_oracle_query);
oci_execute($total_oracle_stmt);

$row = oci_fetch_assoc($total_oracle_stmt);

$total_rows = $row['TOTAL_ROWS'];

$row_progress = 0; // Initialize row progress

// Fetch column names dynamically
$column_names = [];
for ($i = 1; $i <= oci_num_fields($oracle_stmt); $i++) {
    $column_names[] = oci_field_name($oracle_stmt, $i);
}

// Build MySQL Insert Query Dynamically
$mysql_query = "INSERT INTO `$table_name` (" . implode(", ", $column_names) . ") VALUES (" . implode(", ", array_fill(0, count($column_names), "?")) . ")";

// Prepare MySQL statement
$mysql_stmt = $mysql_conn->prepare($mysql_query);
if (!$mysql_stmt) {
    die("MySQL statement prepare failed: " . $mysql_conn->error);
}

while ($row = oci_fetch_assoc($oracle_stmt)) {
    $params = [];

    // Bind column values to parameters
    foreach ($column_names as $column) {
        $value = $row[$column] ?? null; // Handle NULL values
        $params[] = $value === '' ? null : $value; // Convert empty string to NULL
    }

    // Debugging: Check if column count matches
    if (count($params) !== count($column_names)) {
        die("Column count mismatch: Expected " . count($column_names) . " but got " . count($params));
    }

    // Bind parameters dynamically (assumes all are strings for simplicity)
    $types = str_repeat('s', count($params)); // Adjust this according to your data types
    $mysql_stmt->bind_param($types, ...$params);

    // Execute the MySQL insert statement
    if (!$mysql_stmt->execute()) {
        echo "Error inserting data into MySQL: " . $mysql_stmt->error . "\n";
    }

    // Increment the row progress
    $row_progress++;

    // Calculate progress (you may already have this logic)
    $progress_percent = round(($row_progress / $total_rows) * 100);
    $progress_counter = $row_progress . " of " . $total_rows;

    // Output the progress on the same line using carriage return (\r)
    echo "\rProcessing table: $table_name  [$progress_counter] $progress_percent% ";
    flush();
}
// Commit the transaction
$mysql_conn->commit();

// Cleanup
oci_free_statement($oracle_stmt);
oci_close($oracle_conn);
$mysql_stmt->close();
$mysql_conn->close();

echo "Data migration completed successfully!\n";
?>
