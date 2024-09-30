<?php

declare(strict_types=1);

namespace Pst\MysqlDatabase;

use Pst\Core\CoreObject;
use Pst\Core\Enumerable\IRewindableEnumerable;
use Pst\Core\Enumerable\Iterators\RewindableIterator;
use Pst\Core\Enumerable\RewindableEnumerable;
use Pst\Core\Types\TypeHintFactory;
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
     * @return IRewindableEnumerable
     * 
     * @throws Exception 
     * @throws PDOException 
     * @throws InvalidArgumentException 
     */
    protected function implReadSchemas(?string $schemaName = null): IRewindableEnumerable {
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
                return RewindableEnumerable::create([], TypeHintFactory::tryParseTypeName(Schema::class));
            }
            throw new DatabaseException("Schema '{$schemaName}' does not exist");
        }

        $results = [];

        foreach ($queryResults as $schemaData) {
            $schemaData = array_map(fn($v) => is_string($v) ? preg_replace('/\/\*.*?\*\//s', '', $v) : $v, $schemaData);

            $tables = $this->readTables($schemaData['schemaName']);
            $results[] = new Schema($schemaData['schemaName'], $tables->toArray());
        }

        return RewindableEnumerable::create($results, TypeHintFactory::tryParseTypeName(Schema::class));

        //return $schemaName === null ? RewindableEnumerable::create($results, TypeHintFactory::tryParseTypeName(Schema::class)) : $results[0];
    }

    /**
     * Implementation specific method to read tables
     * 
     * @param string $schemaName 
     * @param null|string $tableName 
     * 
     * @return IRewindableEnumerable
     * 
     * @throws Exception 
     * @throws PDOException 
     * @throws InvalidArgumentException 
     */
    protected function implReadTables(string $schemaName, ?string $tableName = null): IRewindableEnumerable {
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
                return RewindableEnumerable::create([], TypeHintFactory::tryParseTypeName(Table::class));
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

        return RewindableEnumerable::create($results, TypeHintFactory::tryParseTypeName(Table::class));
        
        //return $tableName === null ? RewindableEnumerable::create($results, TypeHintFactory::tryParseTypeName(Table::class)) : $results[0];
    }

    /**
     * Implementation specific method to read columns
     * 
     * @param string $schemaName 
     * @param string $tableName 
     * @param null|string $columnName 
     * 
     * @return IRewindableEnumerable
     * 
     * @throws Exception 
     * @throws PDOException 
     * @throws InvalidArgumentException 
     */
    protected function implReadColumns(string $schemaName, string $tableName, ?string $columnName = null): IRewindableEnumerable {
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
                return RewindableEnumerable::create([], TypeHintFactory::tryParseTypeName(Column::class));
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

        return RewindableEnumerable::create($results, TypeHintFactory::tryParseTypeName(Column::class));
    }

    /**
     * Implementation specific method to read indexes
     * 
     * @param string $schemaName 
     * @param string $tableName 
     * @param null|string $indexName 
     * 
     * @return IRewindableEnumerable
     * 
     * @throws Exception 
     * @throws PDOException 
     * @throws InvalidArgumentException 
     */
    protected function implReadIndexes(string $schemaName, string $tableName, ?string $indexName = null): IRewindableEnumerable {
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
                return RewindableEnumerable::create([], TypeHintFactory::tryParseTypeName(Index::class));
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

        return RewindableEnumerable::create($results, TypeHintFactory::tryParseTypeName(Index::class));
        //return $indexName === null ? RewindableEnumerable::create($results, TypeHintFactory::tryParseTypeName(Index::class)) : $results[0];
    }
}