<?php

declare(strict_types=1);

use Pst\Core\CoreObject;
use Pst\Core\DependencyInjection\DI;

use Pst\Database\DB;
use Pst\Database\TableRow\ITableRow;
use Pst\Database\TableRow\TableRowTrait;
use Pst\Database\Connections\IDatabaseConnection;
use Pst\Database\Schema\ISchemaReader;

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

class Administrator extends CoreObject implements ITableRow {
    use TableRowTrait {
        __construct as private tableRowTraitConstruct;
    }

    public function __construct(iterable $columnValues = []) {
        $this->tableRowTraitConstruct($columnValues);
    }

    public static function schemaName(): string {
        return "sct2";
    }
}

echo "schemaName: " . Administrator::schemaName() . "\n";
echo "tableName : " . Administrator::tableName() . "\n";
echo "\n";
echo "indexes   : " . Administrator::indexes()->select(fn ($index) => $index->name())->join(", ") . "\n";
echo "columns   : " . Administrator::columns()->select(fn ($column) => $column->name())->join(", ") . "\n";
echo "\n";

if (!Administrator::read(["id" => 1], $objectOrException)) {
    echo "Error: " . $objectOrException->getMessage() . "\n";
} else {
    print_r($objectOrException);
}

//exit;

$readPredicates = [
    //"id" => 1
];

if (!Administrator::list($readPredicates, $objectOrException)) {
    echo "Error: " . $objectOrException->getMessage() . "\n";
} else {
    print_r($objectOrException->toArray());
}




$admin = new Administrator([
    
    "name" => "Jason Thompson",
    "initials" => "CZT",
    "level" => "4",
    "password" => "imtytftdo",
    "email" => "jasonwt@gmail.com"
]);

//print_r($admin->getColumnValues()->toArray());

//print_r($admin->updatedColumnValues()->toArray());

$admin->setColumnValue("name", "Brent Bullard");
//print_r($admin->pendingDatabaseChanges()->toArray());

//print_r($admin);

//print_r($admin->updatedColumnValues()->toArray());

// print_r($admin->getColumnValues()->toArray());

// if (!$admin->writeToDatabase($results, true)) {
//     echo "Error: " . $results->getMessage() . "\n";
// } else {
//     print_r($results);
// }

// print_r($results);

//print_r($admin);

// $admin->setColumnValue("id", 25, false);
// print_r($admin);

// print_r($admin->getColumnValues()->toArray());

// //print_r($admin);

















// function generateCoreTableRowObjectTrait(string $className, string $schemaName, array $parameters = []): string {
//     if (empty($className = trim($className))) {
//         throw new InvalidArgumentException("Parameter 'className' cannot be empty.");
//     }

//     if (empty($schemaName = trim($schemaName))) {
//         throw new InvalidArgumentException("Parameter 'schemaName' cannot be empty.");
//     }

//     if (($tableName = $parameters["tableName"] ?? null) !== null) {
//         if (!is_string($tableName)) {
//             throw new InvalidArgumentException("Parameter 'tableName' must be a null or a string.");
//         } else if (empty($tableName = trim($tableName))) {
//             throw new InvalidArgumentException("Parameter 'tableName' cannot be empty if not null.");
//         }
//     }
    

//     $schemaMethod = "
//         public static function schemaName(): string {
//             return '$schemaName';
//         }";

//     $tableMethod = $tableName === null ? "" : "
//         public static function tableName(): string {
//             return '$tableName';
//         }";


//     return "
//         <?php

//         declare(strict_types=1);

//         class {$className} extends CoreObject implments ICoreTableRowObjectTrait {
//             use ReadOnlyTableRowObjectTrait;

//             {$schemaMethod}{$tableMethod}
//         }
//     ";
// }






// class Administrator implements ITableRowObjectTrait, ITableRowObjectFactoryTrait {
//     use TableRowObjectTrait;
//     use TableRowObjectFactoryTrait;

//     public static function schemaName(): string {
//         return "sct2";
//     }

//     protected static function createInstance(array $columnValues): ITableRowObjectTrait {
//         throw new NotImplementedException("createInstance not implemented.");
//     }

//     protected static function createReadOnlyInstance(array $columnValues): IReadOnlyTableRowObjectTrait {
//         return new self($columnValues);
//     }
// }

// echo "schemaName: " . Administrator::schemaName() . "\n";
// echo "tableName : " . Administrator::tableName() . "\n";
// echo "\n";
// echo "indexes   : " . Administrator::indexes()->select(fn ($index) => $index->name())->join(", ") . "\n";
// echo "columns   : " . Administrator::columns()->select(fn ($column) => $column->name())->join(", ") . "\n";
// echo "\n";

// $admin = Administrator::read(["id" => 1]);

// // $admin = new Administrator([
// //     "id" => 1,
// //     "name" => "Jason Thompson",
// //     "initials" => "JWT",
// //     "level" => "4",
// //     "password" => "imtytftdo",
// //     "email" => "jasonwt@gmail.com"
// // ]);

// print_r($admin);

// $admin->setColumnValue("name", "Jason W. Thompson");

// print_r($admin);


