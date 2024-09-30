<?php

declare(strict_types=1);

use Pst\Core\Interfaces\IToString;
use Pst\Core\Enumerable\Enumerable;
use Pst\Core\Collections\IReadonlyCollection;
use Pst\Core\DependencyInjection\DI;
use Pst\Core\DynamicPropertiesObject\DynamicPropertiesObject;
use Pst\Database\DB;
use Pst\Database\Column\ColumnDefaultValue;
use Pst\Database\Connections\IDatabaseConnection;
use Pst\Database\Table\Table;
use Pst\Database\Schema\Schema;
use Pst\Database\Schema\ISchemaReader;

use Pst\Core\Exceptions\NotImplementedException;
use Pst\Database\TableRowObject\CoreTableRowObjectTrait;
use Pst\Database\TableRowObject\ICoreTableRowObjectTrait;
use Pst\MysqlDatabase\MysqlPdoConnection;
use Pst\MysqlDatabase\MysqlStructureReader;

require_once(__DIR__ . '/../vendor/autoload.php');










$CONFIG = [
    "Database" => [
        "hostname" => "mariadb",
        "database" => "sct2",
        "username" => "root",
        "password" => "mbdcdevRootPassword",
    ]
];

DI::addSingleton(
    PDO::class,
    new PDO(
        "mysql:host=" . $CONFIG["Database"]["hostname"] . ";dbname=" . $CONFIG["Database"]["database"],
        $CONFIG["Database"]["username"],
        $CONFIG["Database"]["password"],
        [
            PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
        ]
    )
);

DI::addSingleton(IDatabaseConnection::class, 
    function(): IDatabaseConnection {
        return new MysqlPdoConnection(DI::get(PDO::class));
    }
);

DI::addSingleton(ISchemaReader::class, 
    function(): ISchemaReader {
        return new MysqlStructureReader(DI::get(IDatabaseConnection::class));
    }
);

DB::addConnection(DI::get(IDatabaseConnection::class), DI::get(ISchemaReader::class));


class CoreTableRowObject implements ICoreTableRowObjectTrait {
    use CoreTableRowObjectTrait;

    public static function schemaName(): string {
        return "sct2";
    }

}



// interface IReadOnlyTableRowObjectTrait extends ICoreTableRowObjectTrait {
//     public function getColumnValues(): array;

//     public function tryGetColumnValue(string $columnName, &$columnValue): bool;
//     public function getColumnValue(string $columnName);

//     public static function validateColumnValue(string $columnName, $columnValue, ?string &$errorMessage): bool;
// }

// interface ITableRowObjectTrait extends IReadOnlyTableRowObjectTrait {
//     public function trySetColumnValues(array $columnValues, bool $updateDatabase = false): bool;
//     public function setColumnValues(array $columnValues, bool $updateDatabase): void;

//     public function trySetColumnValue(string $columnName, $columnValue, bool $updateDatabase = false): bool;
//     public function setColumnValue(string $columnName, $columnValue, bool $updateDatabase = false): void;
// }

// interface ITableRowObjectFactoryTrait {
//     public static function tryRead(array $predicate, ?IReadOnlyTableRowObjectTrait &$readObject): bool;
//     public static function read(array $predicate): IReadOnlyTableRowObjectTrait;

//     public static function tryCreate(array $columnValues, ?IReadOnlyTableRowObjectTrait &$createdObject): bool;
//     public static function create(array $columnValues): IReadOnlyTableRowObjectTrait;

//     public static function tryUpdate(ITableRowObjectTrait $objectToUpdate): bool;
//     public static function update(ITableRowObjectTrait $objectToUpdate): void;

//     public static function tryDelete(IReadOnlyTableRowObjectTrait $objectToDelete): bool;
//     public static function delete(IReadOnlyTableRowObjectTrait $objectToDelete): void;

//     public static function tryList(array $predicate = [], int $skip = 0, int $take = 0): ?IReadonlyCollection;
//     public static function list(array $predicate = [], int $skip = 0, int $take = 0): IReadonlyCollection;
// }



// trait TableRowObjectFactoryTrait {
//     use CoreTableRowObjectTrait;

//     protected abstract static function createInstance(array $columnValues): ITableRowObjectTrait;
//     protected abstract static function createReadOnlyInstance(array $columnValues): ITableRowObjectTrait;

//     public static function tryRead(array $predicate, ?IReadOnlyTableRowObjectTrait &$readObject): bool {
//         $errors = [];

//         foreach ($predicate as $columnName => $columnValue) {
//             if (!static::validateColumnValue($columnName, $columnValue, $errorMessage)) {
//                 $errors[$columnName] = $errorMessage;
//             }
//         }

//         if (count($errors) > 0) {
//             static::$tableRowObjectCache["lastTryReadErrors"] = $errors;
//             return false;
//         }

//         $selectQuery = "SELECT * FROM " . static::tableName();
//         $selectQuery .= " WHERE " . implode(" AND ", array_map(fn ($columnName) => "$columnName = ?", array_keys($predicate)));

//         $records = static::db()->
//             query($selectQuery, array_values($predicate))->
//             toArray();

//         if (count($records) === 0) {
//             return false;
//         } else if (count($records) > 1) {
//             throw new LogicException("Multiple records found.");
//         }

        
//         return true;
//     }
//     public static function read(array $predicate): IReadOnlyTableRowObjectTrait {
//         throw new NotImplementedException();
//     }

//     public static function tryCreate(array $columnValues, ?IReadOnlyTableRowObjectTrait &$createdObject): bool {
//         throw new NotImplementedException();
//     }
//     public static function create(array $columnValues): IReadOnlyTableRowObjectTrait {
//         throw new NotImplementedException();
//     }

//     public static function tryUpdate(ITableRowObjectTrait $objectToUpdate): bool {
//         throw new NotImplementedException();
//     }
//     public static function update(ITableRowObjectTrait $objectToUpdate): void {
//         throw new NotImplementedException();
//     }

//     public static function tryDelete(IReadOnlyTableRowObjectTrait $objectToDelete): bool {
//         throw new NotImplementedException();
//     }
//     public static function delete(IReadOnlyTableRowObjectTrait $objectToDelete): void {
//         throw new NotImplementedException();
//     }

//     public static function tryList(array $predicate = [], int $skip = 0, int $take = 0): ?IReadonlyCollection {
//         throw new NotImplementedException();
//     }
//     public static function list(array $predicate = [], int $skip = 0, int $take = 0): IReadonlyCollection {
//         throw new NotImplementedException();
//     }
// }

// trait ReadOnlyTableRowObjectTrait {
//     use CoreTableRowObjectTrait;
// }

// trait TableRowObjectTrait {
//     use ReadOnlyTableRowObjectTrait;

// }

