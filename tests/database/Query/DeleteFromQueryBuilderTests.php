<?php

declare(strict_types=1);

// show all errors and warnings
error_reporting(E_ALL);
ini_set('display_errors', '1');

use Pst\Database\Query\Builder\Clauses\Where\Where;
use Pst\Database\Query\Builder\QueryBuilder;

require_once __DIR__ . '/../../vendor/autoload.php';

$queryBuilder = QueryBuilder
    ::deleteFrom("schema.table")
    ->join("schema.table on schema.table.column = schema.table2.column2")
    ->leftJoin("schema.table on schema.table.column = schema.table2.column2")
    ->rightJoin("schema.table on schema.table.column = schema.table2.column2")
    ->where("column1 = 123")
    ->andWhere("column3 = 'asdf'")
    ->orWhere(Where::new("column2 = 321")->and("name = 'John'"))
    ->limit(10)
    
    ;

print_r($queryBuilder->getIdentifiers());
print_r($queryBuilder->getQuery());
