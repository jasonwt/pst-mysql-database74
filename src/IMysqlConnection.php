<?php

declare(strict_types=1);

namespace Pst\MysqlDatabase;

use Pst\Database\Connections\IColumnReader;
use Pst\Database\Connections\IDatabaseConnection;
use Pst\Database\Connections\IIndexReader;
use Pst\Database\Connections\ITableReader;

interface IMysqlConnection extends IDatabaseConnection, IIndexReader, IColumnReader, ITableReader {
    
}