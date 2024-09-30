<?php

declare(strict_types=1);

// show all errors and warnings
error_reporting(E_ALL);
ini_set('display_errors', '1');

use Pst\Core\Enumerable\Enumerable;
use Pst\Database\Query\Builder\Clauses\Having\Having;
use Pst\Database\Query\Builder\Clauses\Select\Select;
use Pst\Database\Query\Builder\Clauses\Where\Where;
use Pst\Database\Query\Builder\QueryBuilder;

require_once __DIR__ . '/../../vendor/autoload.php';

$queryBuilder = QueryBuilder
    ::select("schema.table.column as alias")
    ->select("`schema2`.`table2`.`column2` as `alias2`")
    ->select("'this is a test' as `alias4`")
    ->from("schema.table")
    ->join("schema.table on schema.table.column = schema.table2.column2")
    ->leftJoin("schema.table on schema.table.column = schema.table2.column2")
    ->rightJoin("schema.table on schema.table.column = schema.table2.column2")
    ->where(Where::new(Where::new("schema.table.column = 789")))
    ->andWhere("schema.table.column = 456")
    ->orWhere("schema.table.column = 654")
    ->andWhere(Where::new("schema.table.column = 789")->and("schema.table.column = 987"))
    ->select("`schema3`.`table3`.`column3` as `alias3`")
    ->from("schema2.table2")
    ->groupBy("schema.table.column", "schema2.table2.column2")
    ->groupBy("schema3.table3.column3")
    ->having("schema.table.column = 123")
    ->andHaving("schema.table.column = 456")
    ->orHaving("schema.table.column = 654")
    ->andHaving(Having::new("schema.table.column = 789")->and("schema.table.column = 987"))
    ->orderBy("schema.table.column DESC", "schema2.table2.column2 ASC", "schema3.table3.column3")
    ->orderBy("schema3.table3.column3")
    ->limit(10)
    ->offset(5)
    ;

print_r($queryBuilder->getIdentifiers());

print_r($queryBuilder->getQuery());

