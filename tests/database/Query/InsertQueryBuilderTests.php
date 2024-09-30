<?php

declare(strict_types=1);

// show all errors and warnings
error_reporting(E_ALL);
ini_set('display_errors', '1');

use Pst\Database\Query\Builder\QueryBuilder;

require_once __DIR__ . '/../../vendor/autoload.php';

$queryBuilder = QueryBuilder
    ::insertInto("schema.table")
    ->set("schema4.table4.column4 = 123", "column1 = column3")
    ;

print_r($queryBuilder->getIdentifiers());
print_r($queryBuilder->getQuery());

$queryBuilder = QueryBuilder
    ::insertIgnore("schema.table")
    ->set("schema4.table4.column4 = 123", "column1 = column3")
    ;

print_r($queryBuilder->getIdentifiers());
print_r($queryBuilder->getQuery());
