<?php

/*TDD*/

declare(strict_types=1);

use Pst\Core\DebugDump\DD;
use Pst\Database\Column\ColumnDefaultValue;
use Pst\Database\Column\ColumnType;
use Pst\Database\Index\IndexType;
use Pst\MysqlDatabase\MysqlPdoConnection;
use Pst\MysqlDatabase\MysqlStructureReader;
use Pst\Testing\Should;

use function Pst\Core\dd;

require_once __DIR__ . '/../../../vendor/autoload.php';

Should::executeTests(function() {
    $pdoConnection = new PDO('mysql:host=mariadb;dbname=information_schema', 'root', 'mbdcdevRootPassword');
    $databaseConnection = new MysqlPdoConnection($pdoConnection);
    
    dd($databaseConnection->readColumns("sct2", "administrators"), DD::SHOW_PRIVATE_CLASS_METHODS(true));

    // $types = [
    //     "",
    //     "1;",
    //     "2;",
    // ];

    // foreach ($types as $type) {
        
    //     // for ($i = 30; $i < 38; $i++) {
    //     //     echo "\033[" . $type . ($i+10) . "m    \033[0m";
    //     // }
    //     echo "\n";
    //     for ($i = 30; $i < 38; $i++) {
    //         echo "\033[" . $type . ($i) . "m████\033[0m";
    //     }
    //     echo "\n";
    //     for ($i = 90; $i < 98; $i++) {
    //         echo "\033[" . $type . ($i) . "m████\033[0m";
    //     }
    //     echo "\n";
        
    // }

    // /*

    // 34m rgba(0,0,187,255)
    // 1;34m rgba(73,85,255,255)
    // 2;34m rgba(0,0,98,255)
    // 94m rgba(85,85,255,255)
    // 1;94m rgba(43,69,255,255)
    // 2;94m rgba(48,56,170,255)


    // 34m   [0  , 0  , 0.5]
    // 1;34m [73 , 85 , 255]
    // 2;34m [0  , 0  , 98]
    // 94m   [85 , 85 , 255]
    // 1;94m [43 , 69 , 255]
    // 2;94m [48 , 56 , 170]



    //  DARK_BLUE = [0  , 0  , 128]
    //       BLUE = [0  , 0  , 255]
    // LIGHT_BLUE = [85 , 85 , 255] 34m

    // 2;33m
    // 34m
    // 2;34m


    // "" => [
    // ]


    // */


    
    
    // // Example usage
    // foreach ($ansiColors as $code => $color) {
    //     echo "Code $code: {$color['name']}\n";
    //     echo "  Regular RGB: " . implode(', ', $color['regular']) . "\n";
    //     echo "  Bold RGB: " . implode(', ', $color['bold']) . "\n";
    //     echo "  Dim RGB: " . implode(', ', $color['dim']) . "\n";
    // }

    

    // exit;
    

    // $types = [
    //     "",
    //     "1;",
    //     "2;",
    // ];

    // foreach ($types as $type) {
    //     for ($i = 30; $i < 38; $i++) {
    //         echo "\033[" . $type . $i . "m##\033[0m";
    //     }
    //     echo "\n";
    //     for ($i = 90; $i < 98; $i++) {
    //         echo "\033[" . $type . $i . "m##\033[0m";
    //     }
    //     echo "\n";
    // }

    // echo "\n";
    
    // // echo "\033[34m 34m \033[0m\n";
    // // echo "\033[1;34m 1;34m \033[0m\n";
    // // echo "\033[2;34m 2;34m \033[0m\n";
    // // echo "\n";

    // // echo "\033[94m 94m \033[0m\n";
    // // echo "\033[1;94m 1;94m \033[0m\n";
    // // echo "\033[2;94m 2;94m \033[0m\n";
    

    // exit;
    
    //echo "queryResultsCount: " . count($queryResults) . "\n";

    // foreach ($queryResults as $key => $value) {
    //     echo "key: " . $key . "\n";
    //     echo "value: " . $value . "\n";
    // }

    // foreach ($queryResults as $key => $value) {
    //     echo "key: " . $key . "\n";
    //     echo "value: " . $value . "\n";
    // }

    // $mysqlConnection = Should::notThrow(Exception::class, fn() => new MysqlPdoConnection($pdoConnection))[0];
    // $databaseStructureReader = Should::notThrow(Exception::class, fn() => new MysqlStructureReader($mysqlConnection))[0];

    // $sct2AdministratorsIdColumn = Should::notThrow(Exception::class, fn() => $databaseStructureReader->readColumn("sct2", "administrators", "id"))[0];
    // Should::equal("sct2", $sct2AdministratorsIdColumn->schemaName());
    // Should::equal("administrators", $sct2AdministratorsIdColumn->tableName());
    // Should::equal("id", $sct2AdministratorsIdColumn->name());
    // Should::equal(ColumnType::AUTO_INCREMENTING_INT(), $sct2AdministratorsIdColumn->type());
    // Should::beFalse($sct2AdministratorsIdColumn->isNullable());
    // Should::equal(ColumnDefaultValue::NONE(), $sct2AdministratorsIdColumn->defaultValue());
    // Should::equal(null, $sct2AdministratorsIdColumn->length());
    // Should::equal(IndexType::PRIMARY(), $sct2AdministratorsIdColumn->indexType());

    // $sct2AdministratorsTable = Should::notThrow(Exception::class, fn() => $databaseStructureReader->readTable("sct2", "administrators"))[0];
    // $expectedColumnNames = ["id", "name", "initials", "level", "password", "sesskey", "clients", "directcustomer", "email", "active", "projectcodes"];
    // Should::equal($expectedColumnNames, $sct2AdministratorsTable->columns()->select(function($column, $index) {
    //     return $column->name();
    // })->toArray());

    // $sct2Schema = Should::notThrow(Exception::class, fn() => $databaseStructureReader->readSchema("sct2"))[0];
    // $expectedTablesNames = ["new_files", "mixtureIngredients", "new_adminfiles", "requests", "mixtureLog", "rslnames", "new_chemfiles", "clients", "rsls", "tblChemAssess", "mixtures", "administrators", "rslupdates", "tfa", "logger", "states", "projects", "countries", "passwdreset"];
    // Should::equal($expectedTablesNames, $sct2Schema->tables()->select(function($table, $index) {
    //     return $table->name();
    // })->toArray());
});