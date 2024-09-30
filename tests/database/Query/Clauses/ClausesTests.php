<?php

declare(strict_types=1);

namespace Pst\Database\Tests\Query\Clauses;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Pst\Database\Query\Clauses\SelectClause;
use Pst\Database\Query\Clauses\FromClause;
use Pst\Database\Query\Clauses\WhereClause;
use Pst\Database\Query\Clauses\GroupByClause;
use Pst\Database\Query\Clauses\HavingClause;
use Pst\Database\Query\Clauses\OrderByClause;
use Pst\Database\Query\Clauses\LimitClause;
use Pst\Database\Query\Clauses\OffsetClause;

$selectClause = SelectClause::new('id, name, email');
echo "SelectClause::name(): " . SelectClause::name() . PHP_EOL;
echo "SelectClause->getQueryParameters(): " . print_r($selectClause->getQueryParameters(), true);
echo "SelectClause->getQuery(): " . $selectClause->getQuery() . PHP_EOL;
echo "SelectClause->getParameterlessQuery(): " . $selectClause->getParameterlessQuery() . PHP_EOL . PHP_EOL;

$fromClause = FromClause::new('accounts, attachments');
echo "FromClause::name(): " . FromClause::name() . PHP_EOL;
echo "FromClause->getQueryParameters(): " . print_r($fromClause->getQueryParameters(), true);
echo "FromClause->getQuery(): " . $fromClause->getQuery() . PHP_EOL;
echo "FromClause->getParameterlessQuery(): " . $fromClause->getParameterlessQuery() . PHP_EOL . PHP_EOL;

$whereClause = WhereClause::new('id = 1 AND name != "John" OR email LIKE "asdf"');
echo "WhereClause::name(): " . WhereClause::name() . PHP_EOL;
echo "WhereClause->getQueryParameters(): " . print_r($whereClause->getQueryParameters(), true);
echo "WhereClause->getQuery(): " . $whereClause->getQuery() . PHP_EOL;
echo "WhereClause->getParameterlessQuery(): " . $whereClause->getParameterlessQuery() . PHP_EOL . PHP_EOL;

$groupByClause = GroupByClause::new('id, name');
echo "GroupByClause::name(): " . GroupByClause::name() . PHP_EOL;
echo "GroupByClause->getQueryParameters(): " . print_r($groupByClause->getQueryParameters(), true);
echo "GroupByClause->getQuery(): " . $groupByClause->getQuery() . PHP_EOL;
echo "GroupByClause->getParameterlessQuery(): " . $groupByClause->getParameterlessQuery() . PHP_EOL . PHP_EOL;

$havingClause = HavingClause::new('id = 1 AND name != "John" OR email LIKE "asdf"');
echo "HavingClause::name(): " . HavingClause::name() . PHP_EOL;
echo "HavingClause->getQueryParameters(): " . print_r($havingClause->getQueryParameters(), true);
echo "HavingClause->getQuery(): " . $havingClause->getQuery() . PHP_EOL;
echo "HavingClause->getParameterlessQuery(): " . $havingClause->getParameterlessQuery() . PHP_EOL . PHP_EOL;

$orderByClause = OrderByClause::new('id DESC, name ASC');
echo "OrderByClause::name(): " . OrderByClause::name() . PHP_EOL;
echo "OrderByClause->getQueryParameters(): " . print_r($orderByClause->getQueryParameters(), true);
echo "OrderByClause->getQuery(): " . $orderByClause->getQuery() . PHP_EOL;
echo "OrderByClause->getParameterlessQuery(): " . $orderByClause->getParameterlessQuery() . PHP_EOL . PHP_EOL;

$limitClause = LimitClause::new('10');
echo "LimitClause::name(): " . LimitClause::name() . PHP_EOL;
echo "LimitClause->getQueryParameters(): " . print_r($limitClause->getQueryParameters(), true);
echo "LimitClause->getQuery(): " . $limitClause->getQuery() . PHP_EOL;
echo "LimitClause->getParameterlessQuery(): " . $limitClause->getParameterlessQuery() . PHP_EOL . PHP_EOL;

$offsetClause = OffsetClause::new('5');
echo "OffsetClause::name(): " . OffsetClause::name() . PHP_EOL;
echo "OffsetClause->getQueryParameters(): " . print_r($offsetClause->getQueryParameters(), true);
echo "OffsetClause->getQuery(): " . $offsetClause->getQuery() . PHP_EOL;
echo "OffsetClause->getParameterlessQuery(): " . $offsetClause->getParameterlessQuery() . PHP_EOL . PHP_EOL;