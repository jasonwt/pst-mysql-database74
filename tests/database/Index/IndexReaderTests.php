<?php

/*TDD*/

declare(strict_types=1);

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
    //$indexReader = new MysqlStructureReader($databaseConnection);

    //$readIndexes = $indexReader->readIndexes("sct2", "administrators");
    //print_r($readIndexes);

    dd($databaseConnection->readIndexes("sct2", "administrators")->toArray());

    
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