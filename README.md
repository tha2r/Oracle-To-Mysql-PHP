# Oracle to MySQL Migration Tool

This repository provides a set of PHP scripts designed to help you migrate tables from an Oracle database to a MySQL database. It includes functionality to migrate both individual tables and all tables in an Oracle schema.

## Features

- **Schema Migration**: The tool extracts table schemas from Oracle and converts them into MySQL-compatible format.
- **Data Migration**: It migrates data from Oracle to MySQL, supporting batch inserts for optimized performance.
- **Error Handling**: Detailed error messages for debugging.
- **Batch Insertion**: Inserts data in batches (1000 rows per batch) to minimize the load on the target MySQL server.
- **Full Schema Migration**: Supports migrating all tables within an Oracle schema with a single command.

## Requirements

- PHP 7.4 or higher
- Oracle Instant Client (for Oracle connection)
- MySQL Server
- Composer (optional, for managing dependencies)

## Setup

1. Clone the repository:
   ```
   git clone https://github.com/tha2r/oracle-to-mysql-php.git
   ```

2. Navigate into the project folder:
   ```
   cd oracle-to-mysql-php
   ```

3. Configure the connection settings by editing the `config.php` file. This file contains the credentials and connection details for both the Oracle and MySQL databases.

   Update the following values:
   - **Oracle Database**:
     - `$oracle_host` (Oracle server hostname or IP address)
     - `$oracle_username` (Oracle database username)
     - `$oracle_password` (Oracle database password)
     - `$oracle_dbname` (Oracle database name)
   - **MySQL Database**:
     - `$mysql_host` (MySQL server hostname or IP address)
     - `$mysql_username` (MySQL database username)
     - `$mysql_password` (MySQL database password)
     - `$mysql_dbname` (MySQL database name)

4. Ensure that both Oracle and MySQL servers are running and accessible from your environment.

## Usage

### Migrate a Single Table

To migrate a single table from Oracle to MySQL, use the `migrate_table.php` script by passing the table name as an argument:

```
php migrate_table.php <TABLE_NAME>
```

This will:
- Retrieve the schema of the specified table from Oracle.
- Create the table in MySQL if it doesn’t exist.
- Migrate the data from Oracle to MySQL in batches of 1000 rows.

Example:
```
php migrate_table.php EMPLOYEES
```

### Migrate All Tables in the Oracle Schema

To migrate all tables from the Oracle schema to MySQL, run the `migrate.php` script:

```
php migrate.php
```

This script will:
- Fetch a list of all tables from Oracle.
- Call the `migrate_table.php` script for each table in the schema, migrating both the schema and data.

### Example Output

```
Processing table: EMPLOYEES
Table `EMPLOYEES` created successfully in MySQL.
Processing table: EMPLOYEES [1000 of 1000] 100%
Data migration completed successfully!

Processing table: DEPARTMENTS
Table `DEPARTMENTS` created successfully in MySQL.
Data migration completed successfully!
```

## Batch Insertion

To optimize performance, the tool inserts data in batches of 1000 rows at a time. You can adjust the batch size in the code if needed.

## Error Handling

If an error occurs during the migration process, the script will display an error message to help you troubleshoot the issue. For example:

```
Error inserting data into MySQL: MySQL error message
```

### Error Handling Example:

```
MySQL Error on DROP TABLE: Duplicate entry for table name
MySQL Error on CREATE TABLE: Syntax error in CREATE statement
Error inserting data into MySQL: Connection timed out
```

## Cleanup

Once the migration is completed, the tool will:
- Free Oracle database resources.
- Commit the MySQL transaction to ensure that all data is saved.
- Close the connections to both Oracle and MySQL databases.

## Performance Considerations

- **Batch Insertions**: The script is optimized to insert data in batches of 1000 rows. You can adjust the batch size in the code if needed.
- **Transaction Handling**: MySQL transactions are used to ensure data consistency during the migration.
- **Connection Reuse**: The script uses persistent connections for improved performance when processing large datasets.

## Contributing

Contributions are welcome! If you find any issues or would like to contribute improvements, please fork the repository, create a new branch, and submit a pull request. Be sure to update the README and any relevant documentation with your changes.

### Reporting Issues

If you encounter any problems or bugs during the migration, please create an issue on the [GitHub issues page](https://github.com/tha2r/oracle-to-mysql/issues). Provide details about your environment, the specific error message, and steps to reproduce the issue.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contact

For any questions or inquiries, feel free to contact the repository owner: [tha2r](https://github.com/tha2r).

---

**Note:** This tool was designed to help migrate data from Oracle to MySQL. Ensure that you have appropriate backups before starting the migration process to avoid accidental data loss.
