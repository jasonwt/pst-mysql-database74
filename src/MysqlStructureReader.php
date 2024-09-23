<?php

declare(strict_types=1);

namespace Pst\MysqlDatabase;

use Pst\Core\CoreObject;

use Pst\Core\Types\TypeHintFactory;

use Pst\Core\Collections\ReadonlyCollection;
use Pst\Core\Collections\IReadonlyCollection;

use Pst\Database\Column\ColumnDefaultValue;
use Pst\Database\Column\ColumnType;
use Pst\Database\Index\IndexType;

use Pst\Database\Schema\Schema;
use Pst\Database\Schema\ISchemaReader;
use Pst\Database\Schema\SchemaReaderTrait;

use Pst\Database\Table\Table;
use Pst\Database\Table\TableReaderTrait;

use Pst\Database\Index\Index;
use Pst\Database\Index\IndexReaderTrait;

use Pst\Database\Column\Column;
use Pst\Database\Column\ColumnReaderTrait;

use Pst\Database\Exceptions\DatabaseException;

class MysqlStructureReader extends CoreObject implements ISchemaReader {
    use SchemaReaderTrait;
    use TableReaderTrait;
    use ColumnReaderTrait;
    use IndexReaderTrait;

    private IMysqlConnection $connection;

    public function __construct(IMysqlConnection $connection) {
        $this->connection = $connection;
    }

    /**
     * Implementation specific method to read schemas
     * 
     * @param null|string $schemaName 
     * 
     * @return IReadonlyCollection|Schema 
     * 
     * @throws Exception 
     * @throws PDOException 
     * @throws InvalidArgumentException 
     */
    protected function implReadSchemas(?string $schemaName = null) {
        $query = "SELECT SCHEMA_NAME AS schemaName FROM information_schema.SCHEMATA";
        $parameters = [];

        if ($schemaName !== null) {
            $query .= " WHERE SCHEMA_NAME LIKE :schemaName";
            $parameters['schemaName'] = $schemaName;
        }

        if (($queryResults = $this->connection->query($query, $parameters)) === false) {
            throw new DatabaseException("Error reading schemas");
        } else if ($queryResults->rowCount() === 0) {
            if ($schemaName === null) {
                return ReadonlyCollection::new([], TypeHintFactory::tryParse(Schema::class));
            }
            throw new DatabaseException("Schema '{$schemaName}' does not exist");
        }

        $results = [];

        foreach ($queryResults as $schemaData) {
            $schemaData = array_map(fn($v) => is_string($v) ? preg_replace('/\/\*.*?\*\//s', '', $v) : $v, $schemaData);

            $tables = $this->readTables($schemaData['schemaName']);
            $results[] = new Schema($schemaData['schemaName'], $tables->toArray());
        }

        return $schemaName === null ? ReadonlyCollection::new($results, TypeHintFactory::tryParse(Schema::class)) : $results[0];
    }

    /**
     * Implementation specific method to read tables
     * 
     * @param string $schemaName 
     * @param null|string $tableName 
     * 
     * @return IReadonlyCollection|Table 
     * 
     * @throws Exception 
     * @throws PDOException 
     * @throws InvalidArgumentException 
     */
    protected function implReadTables(string $schemaName, ?string $tableName = null) {
        $query = "
            SELECT 
                TABLE_NAME AS tableName 
            FROM 
                information_schema.TABLES
            WHERE 
                `TABLE_SCHEMA` LIKE :schemaName
        "; 

        $parameters = ['schemaName' => $schemaName];

        if ($tableName !== null) {
            $query .= " AND `TABLE_NAME` LIKE :tableName";
            $parameters['tableName'] = $tableName;
        }

        if (($queryResults = $this->connection->query($query, $parameters)) === false) {
            if ($tableName === null) {
                return ReadonlyCollection::new([], TypeHintFactory::tryParse(Table::class));
            }
            throw new DatabaseException("Error reading tables for schema '{$schemaName}'");
        } else if ($queryResults->rowCount() === 0) {
            throw new DatabaseException("Table '{$tableName}' does not exist");
        }

        $results = [];

        foreach ($queryResults as $tableData) {
            $tableData = array_map(fn($v) => is_string($v) ? preg_replace('/\/\*.*?\*\//s', '', $v) : $v, $tableData);

            $columns = $this->readColumns($schemaName, $tableData['tableName']);
            $indexes = $this->readIndexes($schemaName, $tableData['tableName']);

            $columnsArray = $columns->toArray();
            $indexesArray = $indexes->toArray();

            $results[] = new Table($schemaName, $tableData['tableName'], $columnsArray, $indexesArray);
        }
        
        return $tableName === null ? ReadonlyCollection::new($results, TypeHintFactory::tryParse(Table::class)) : $results[0];
    }

    /**
     * Implementation specific method to read columns
     * 
     * @param string $schemaName 
     * @param string $tableName 
     * @param null|string $columnName 
     * 
     * @return IReadonlyCollection|Column 
     * 
     * @throws Exception 
     * @throws PDOException 
     * @throws InvalidArgumentException 
     */
    protected function implReadColumns(string $schemaName, string $tableName, ?string $columnName = null) {
        $query = "
            SELECT 
                TABLE_SCHEMA as `schemaName`,
                TABLE_NAME as `tableName`,
                COLUMN_NAME as `name`,
                COLUMN_TYPE as `type`,
                CHARACTER_MAXIMUM_LENGTH as `length`, 
                COLUMN_DEFAULT as `defaultValue`,
                IS_NULLABLE as `isNullable`,
                EXTRA as `isAutoIncrementing`,
                COLUMN_KEY as `indexType`
            FROM information_schema.COLUMNS
            WHERE `TABLE_SCHEMA` LIKE :schemaName AND `TABLE_NAME` LIKE :tableName
        ";

        $parameters = ['schemaName' => $schemaName, 'tableName' => $tableName];

        if ($columnName !== null) {
            $query .= " AND `COLUMN_NAME` LIKE :columnName";
            $parameters['columnName'] = $columnName;
        }

        if (($queryResults = $this->connection->query($query, $parameters)) === false) {
            throw new DatabaseException("Error reading columns for table '{$tableName}'");
        } else if ($queryResults->rowCount() === 0) {
            if ($columnName === null) {
                return ReadonlyCollection::new([], TypeHintFactory::tryParse(Column::class));
            }

            throw new DatabaseException("Column '{$columnName}' does not exist");
        }

        $results = [];

        foreach ($queryResults as $columnData) {
            $columnData = array_map(fn($v) => is_string($v) ? preg_replace('/\/\*.*?\*\//s', '', $v) : $v, $columnData);
            
            $columnData["defaultValue"] = ColumnDefaultValue::tryFromName($columnData["defaultValue"] ?? "NONE") ?? $columnData["defaultValue"];
            $columnData['isNullable'] = $columnData['isNullable'] === 'YES';
            $columnData['type'] = ColumnType::fromMysqlColumnType(
                $columnData['type'], 
                strpos(strtoupper($columnData['isAutoIncrementing'] ?? ""), 'AUTO_INCREMENT') !== false
            );

            unset($columnData['isAutoIncrementing']);
            
            if ($columnData['indexType'] !== null && !empty($columnData['indexType'])) {
                $columnData['indexType'] = IndexType::fromName($columnData['indexType']);
            } else if (empty($columnData['indexType'])) {
                $columnData['indexType'] = null;
            }

            $columnData['length'] = $columnData['length'] === null ? null : (int) $columnData['length'];

            $results[] = new Column(...array_values($columnData));
        }

        return $columnName === null ? ReadonlyCollection::new($results, TypeHintFactory::tryParse(Column::class)) : $results[0];
    }

    /**
     * Implementation specific method to read indexes
     * 
     * @param string $schemaName 
     * @param string $tableName 
     * @param null|string $indexName 
     * 
     * @return IReadonlyCollection|Index 
     * 
     * @throws Exception 
     * @throws PDOException 
     * @throws InvalidArgumentException 
     */
    protected function implReadIndexes(string $schemaName, string $tableName, ?string $indexName = null) {
        $selectPrimaryKeyQuery = "
            SELECT 
                COLUMN_NAME as `primaryKeyName`
            FROM 
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE 
                TABLE_SCHEMA = :schemaName AND TABLE_NAME = :tableName AND CONSTRAINT_NAME = 'PRIMARY';
        ";

        $parameters = ['schemaName' => $schemaName, 'tableName' => $tableName];

        if (($queryResults = $this->connection->query($selectPrimaryKeyQuery, $parameters)) === false) {
            throw new DatabaseException("Error reading primary key for table '{$tableName}'");
        }

        $primaryKeyName = null;

        foreach ($queryResults as $primaryKeyData) {
            $primaryKeyName = $primaryKeyData['primaryKeyName'];
        }

        $query = "
            SELECT 
                TABLE_SCHEMA as `schemaName`,
                TABLE_NAME as `tableName`,
                INDEX_NAME as `name`,
                NON_UNIQUE as `nonUnique`,
                COLUMN_NAME as `columnName`,
                INDEX_TYPE as `type`
                
            FROM 
                information_schema.STATISTICS
            WHERE 
                `TABLE_SCHEMA` LIKE :schemaName AND `TABLE_NAME` LIKE :tableName
        ";
        
        $parameters = ['schemaName' => $schemaName, 'tableName' => $tableName];

        if ($indexName !== null) {
            $query .= " AND `INDEX_NAME` LIKE :indexName";
            $parameters['indexName'] = $indexName;
        }

        if (($queryResults = $this->connection->query($query, $parameters)) === false) {
            throw new DatabaseException("Error reading indexes for table '{$tableName}'");
        } else if ($queryResults->rowCount() === 0) {
            if ($indexName === null) {
                return ReadonlyCollection::new([], TypeHintFactory::tryParse(Index::class));
            }

            throw new DatabaseException("Index '{$indexName}' does not exist");
        }
        
        // TODO: NEED TO CATCH EDGE CASES WHERE THE DEFAULT PRIMARY KEY NAME OF 'PRIMARY' IS NOT USED
        $primaryKeyName = "PRIMARY";

        $indexes = [];
        foreach ($queryResults as $indexData) {
            $indexData = array_map(fn($v) => is_string($v) ? preg_replace('/\/\*.*?\*\//s', '', $v) : $v, $indexData);

            if ($indexData['type'] !== "FULLTEXT") {
                $indexData['type'] = $indexData['name'] === $primaryKeyName ? IndexType::PRIMARY() : (
                    $indexData['nonUnique'] === 0 ? IndexType::UNIQUE() : IndexType::INDEX()
                );
            } else {
                $indexData['type'] = IndexType::FULLTEXT();
            }

            $indexes[$indexData['name']] ??= [
                'schemaName' => $indexData['schemaName'],
                'tableName' => $indexData['tableName'],
                'name' => $indexData['name'],
                'type' => $indexData['type'],
                'columns' => []
            ];

            $indexes[$indexData['name']]['columns'][] = $indexData['columnName'];
        }

        $results = [];

        foreach ($indexes as $index) {
            $results[] = new Index($index['schemaName'], $index['tableName'], $index['name'], $index['type'], $index['columns']);
        }

        return $indexName === null ? ReadonlyCollection::new($results, TypeHintFactory::tryParse(Index::class)) : $results[0];
    }
}