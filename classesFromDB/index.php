<?php
require_once "../basePath.php";
require_once BASE_PATH . '/classes/Config.php'; // Include the Config class file

class DbClassesCreator extends Config
{
    private $createdFiles = []; // To keep track of created files

    public function getTableNames()
    {
        $stmt = $this->conn->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function createTableClasses()
    {
        $tableNames = $this->getTableNames();
        foreach ($tableNames as $tableName) {
            $this->createClassFile($tableName);
        }
        $this->createIncludeFile(); // After creating all classes, create the final include file
    }

    private function createClassFile($tableName)
    {
        $className = ucfirst($tableName);
        $fileContent = "<?php\n";
        $fileContent .= "require_once BASE_PATH . '/classes/Config.php';\n";
        $fileContent .= "require_once BASE_PATH . '/classes/Db.php';\n\n";
        $fileContent .= "class $className extends Database\n";
        $fileContent .= "{\n";
        $fileContent .= "\tpublic \$table = '$tableName';\n";

        // Fetch column names for the table
        $stmt = $this->conn->query("SHOW COLUMNS FROM $tableName");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Convert column names to quoted strings for the $columns array
        $quotedColumns = array_map(function ($column) {
            return "'$column'";
        }, $columns);

        $fileContent .= "\tpublic \$columns = [" . implode(', ', $quotedColumns) . "];\n\n";

        // Add properties for each column
        foreach ($columns as $column) {
            $fileContent .= "\tpublic \$$column;\n";
        }

        $fileContent .= "}\n";

        $fileAdress = "./classes/$className.php";
        $fileName = "$className.php";

        file_put_contents($fileAdress, $fileContent);

        // Add the created file to the list
        $this->createdFiles[] = $fileName;
    }

    private function createIncludeFile()
    {
        $includeContent = "<?php\n";
        foreach ($this->createdFiles as $file) {
            $includeContent .= "require_once BASE_PATH . '/classes/$file';\n";
        }

        // Path to the final include file
        $finalIncludeFile = "./classes/includeClasses.php";
        file_put_contents($finalIncludeFile, $includeContent);
    }
}

// Usage
$handler = new DbClassesCreator();
$handler->createTableClasses();
