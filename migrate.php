<?php
include "config.php";

// Fetch the list of tables in the Oracle schema
$query = oci_parse($oracle_conn, "SELECT TABLE_NAME FROM USER_TABLES ORDER BY TABLE_NAME");
oci_execute($query);

// Loop through each table and run the migration script
while ($row = oci_fetch_assoc($query)) {
    $table_name = $row['TABLE_NAME'];

    // Display progress for each table
    echo "Processing table: $table_name\n";
    runMigrationWithProgress($table_name);
}
// Function to execute and display progress
function runMigrationWithProgress($table_name) {
    $command = "php migrate_table.php $table_name"; // Command to execute

    // Open process and get output in real-time
    $process = proc_open($command, [
        1 => ["pipe", "w"], // stdout
        2 => ["pipe", "w"], // stderr
    ], $pipes);

    if (is_resource($process)) {
        // Ensure stdout is non-blocking
        stream_set_blocking($pipes[1], 0); // Don't block while reading from stdout
        stream_set_blocking($pipes[2], 0); // Don't block while reading from stderr
        
        // Read output from the process and update progress
        while (!feof($pipes[1])) {
            $line = fgets($pipes[1]);
            if ($line) {
                echo $line; // Output the progress from the migration script
                flush(); // Ensure the progress is shown in real-time
            }
        }
        
        // Check for errors in stderr
        while (!feof($pipes[2])) {
            $line = fgets($pipes[2]);
            if ($line) {
                echo "Error: $line";
                flush();
            }
        }
        
        // Close pipes and process
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    } else {
        echo "Error starting process for table $table_name\n";
    }
}

// Close Oracle connection
oci_free_statement($query);
oci_close($oracle_conn);

echo "Table migration completed for all tables.\n";
?>
