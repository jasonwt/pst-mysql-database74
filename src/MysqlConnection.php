<?php

declare(strict_types=1);

namespace Pst\MysqlDatabase;

use Pst\Core\DD;
use Pst\Core\Enumerable\Enumerable;
use Pst\Core\Enumerable\IEnumerable;
use Pst\Core\Enumerable\IRewindableEnumerable;
use Pst\Core\Enumerable\Linq\Linq;
use Pst\Core\Enumerable\RewindableEnumerable;
use Pst\Core\ToString;
use Pst\Core\Types\Type;
use Pst\Core\Types\TypeHintFactory;
use Pst\Database\Column\Column;
use Pst\Database\Column\ColumnDefaultValue;
use Pst\Database\Column\ColumnType;
use Pst\Database\Column\IColumn;
use Pst\Database\Query\IQueryResults;
use Pst\Database\Query\QueryResults;

use Pst\Database\Connections\DatabaseConnection;

use Pst\Database\Exceptions\DatabaseException;
use Pst\Database\Index\IIndex;
use Pst\Database\Index\Index;
use Pst\Database\Index\IndexType;
use Pst\Database\Schema\ISchema;
use Pst\Database\Schema\Schema;
use Pst\Database\Table\Table;

use function Pst\Core\dd;

abstract class MysqlConnection extends DatabaseConnection implements IMysqlConnection {
    protected static array $cache = [];

    /**
     * Get the name of the schema being used
     * 
     * @return string 
     */
    public function getUsingSchema(): string {
        return $this->implQuery('SELECT DATABASE() as usingSchema')->fetchNext()['usingSchema'];
    }

    public function readIndexes(?string $schemaName = null, ?string $tableName = null, ?string $indexName = null): IRewindableEnumerable {
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
        ";

        $parameters = [];
        $wheres = [];

        if ($schemaName !== null) {
            $parameters['schemaName'] = $schemaName;
            $wheres[] = "TABLE_SCHEMA = :schemaName";
        }

        if ($tableName !== null) {
            $parameters['tableName'] = $tableName;
            $wheres[] = "TABLE_NAME = :tableName";
        }

        if ($indexName !== null) {
            $parameters['indexName'] = $indexName;
            $wheres[] = "INDEX_NAME = :indexName";
        }

        if (count($wheres) > 0) {
            $query .= " WHERE " . implode(" AND ", $wheres);
        }

        $queryResults = $this->query($query, $parameters);

        // TODO: NEED TO CATCH EDGE CASES WHERE THE DEFAULT PRIMARY KEY NAME OF 'PRIMARY' IS NOT USED
        $primaryKeyName = "PRIMARY";

        $results = $queryResults->groupBy(
            function($value, $key) {
                $value = array_map(fn($v) => is_string($v) ? preg_replace('/\/\*.*?\*\//s', '', $v) : $v, $value);
                
                return $value["schemaName"] . "." . $value["tableName"] . "." . $value["name"];
            }
        )->select(
            function(IEnumerable $value, $_) use($primaryKeyName) {
                $valuesArray = $value->toArray();

                $schemaName = $valuesArray[0]["schemaName"];
                $tableName = $valuesArray[0]["tableName"];
                $name = $valuesArray[0]["name"];
                $type = ($valuesArray[0]["type"] === "FULLTEXT") ? IndexType::FULLTEXT() : (
                    $valuesArray[0]["name"] === $primaryKeyName ? IndexType::PRIMARY() : (
                        $valuesArray[0]["nonUnique"] === 0 ? IndexType::UNIQUE() : IndexType::INDEX()
                    )
                );
                $columns = $value->select(function($value, $_) {
                    return $value["columnName"];
                })->toArray();

                return new Index($schemaName, $tableName, $name, $type, ...$columns);
            }, 
            function($value, $key) { return $value->first()["name"];}, 
            Type::interface(IIndex::class)
        )->toRewindableEnumerable();

        return $results;      
    }

    public function readColumns(?string $schemaName = null, ?string $tableName = null, ?string $columnName = null): IRewindableEnumerable {
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
        ";

        $parameters = [];
        $wheres = [];

        if ($schemaName !== null) {
            $parameters['schemaName'] = $schemaName;
            $wheres[] = "TABLE_SCHEMA = :schemaName";
        }

        if ($tableName !== null) {
            $parameters['tableName'] = $tableName;
            $wheres[] = "TABLE_NAME = :tableName";
        }

        if ($columnName !== null) {
            $parameters['columnName'] = $columnName;
            $wheres[] = "COLUMN_NAME = :columnName";
        }

        if (count($wheres) > 0) {
            $query .= " WHERE " . implode(" AND ", $wheres);
        }

        $queryResults = $this->query($query, $parameters);
        $queryResults = $queryResults->select(
            function($value, $_) {
                return array_map(fn($v) => is_string($v) ? preg_replace('/\/\*.*?\*\//s', '', $v) : $v, $value);
            }, TypeHintFactory::undefined(), TypeHintFactory::keyTypes()
        );

        dd($queryResults);
        exit;

        $queryResults = $queryResults->select(
            function($value, $_) {
                Linq::select($value, function($v, $k) {
                    return is_string($v) ? preg_replace('/\/\*.*?\*\//s', '', $v) : $v;
                })->toArray();
            },
            TypeHintFactory::array()
        )->select(
            function($columnData, $_){
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

                return new Column(...array_values($columnData));
            },
            function($value, $key) { 
                return $value->name(); 
            },
            Type::interface(IColumn::class)
        )->keyMap(fn($value, $_) => $value->name())->toRewindableEnumerable();
    }

    public function readTables(?string $schemaName = null, ?string $tableName = null): IRewindableEnumerable {
        $query = "
            SELECT 
                TABLE_SCHEMA AS schemaName,
                TABLE_NAME AS tableName 
            FROM 
                information_schema.TABLES
        ";

        $parameters = [];
        $wheres = [];

        if ($schemaName !== null) {
            $parameters['schemaName'] = $schemaName;
            $wheres[] = "TABLE_SCHEMA = :schemaName";
        }

        if ($tableName !== null) {
            $parameters['tableName'] = $tableName;
            $wheres[] = "TABLE_NAME = :tableName";
        }

        if (count($wheres) > 0) {
            $query .= " WHERE " . implode(" AND ", $wheres);
        }

        $queryResults = $this->query($query, $parameters);

        return $queryResults->select(
            function($value, $_) {
                $tableData = array_map(fn($v) => is_string($v) ? preg_replace('/\/\*.*?\*\//s', '', $v) : $v, $value);

                return new Table(
                    $tableData['schemaName'], 
                    $tableData['tableName'], 
                    $this->readColumns($tableData['schemaName'], $tableData['tableName']),
                    $this->readIndexes($tableData['schemaName'], $tableData['tableName'])
                );
            }
        )->keyMap(fn($value, $_) => $value->name())->toRewindableEnumerable();
    }

    public function readSchemas(?string $schemaName = null): IRewindableEnumerable {
        $query = "SELECT SCHEMA_NAME AS schemaName FROM information_schema.SCHEMATA";
        
        $parameters = [];
        $wheres = [];

        if ($schemaName !== null) {
            $parameters['schemaName'] = $schemaName;
            $wheres[] = "TABLE_SCHEMA = :schemaName";
        }

        if (count($wheres) > 0) {
            $query .= " WHERE " . implode(" AND ", $wheres);
        }

        $queryResults = $this->query($query, $parameters);

        return $queryResults->select(
            function($value, $_) {
                $schemaData = array_map(fn($v) => is_string($v) ? preg_replace('/\/\*.*?\*\//s', '', $v) : $v, $value);

                return new Schema($schemaData['schemaName'], $this->readTables($schemaData['schemaName']));
            }
        )->keyMap(fn($value, $_) => $value->name())->toRewindableEnumerable();

        

        foreach ($queryResults as $schemaData) {
            $schemaData = array_map(fn($v) => is_string($v) ? preg_replace('/\/\*.*?\*\//s', '', $v) : $v, $schemaData);

            $tables = $this->readTables($schemaData['schemaName']);
            $results[] = new Schema($schemaData['schemaName'], $tables->toArray());
        }

        return RewindableEnumerable::create($results, TypeHintFactory::tryParseTypeName(ISchema::class));
    }
}