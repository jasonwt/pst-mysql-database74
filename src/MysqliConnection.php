<?php

declare(strict_types=1);

namespace Pst\MysqlDatabase;

use Pst\Database\Query\IQueryResults;
use Pst\Database\Query\QueryResults;

use Pst\Database\Connections\DatabaseConnection;

use Pst\Database\Exceptions\DatabaseException;

use mysqli;
use Generator;
use InvalidArgumentException;

class MysqliConnection extends DatabaseConnection implements IMysqlConnection {
    private mysqli $link;

    public function __construct(mysqli $link) {
        $this->link = $link;
    }

    /**
     * Get the last insert id
     * 
     * @return string 
     */
    public function lastInsertId(): string {
        return $this->link->insert_id;
    }

    /**
     * Get the name of the schema being used
     * 
     * @return string 
     */
    public function getUsingSchema(): string {
        return $this->link->query('SELECT DATABASE() as usingSchema')->fetch_row()[0];
    }

    /**
     * Perform a query on the database
     * 
     * @param string $query 
     * @param array $parameters 
     * 
     * @return IQueryResults
     * 
     * @throws DatabaseException 
     */
    protected function implQuery(string $query, array $parameters = []): IQueryResults {
        if (($query = trim($query)) === '') {
            throw new InvalidArgumentException("Query cannot be empty");
        }

        $query = $this->applyParametersToQuery($query, $parameters);

        $result = $this->link->query($query);

        if ($result === false) {
            echo "query: '$query'\n";
            throw new DatabaseException($this->link->error);
        }

        $resultsGenerator = (function() use ($result): Generator {
            while ($row = $result->fetch_assoc()) {
                yield $row;
            }
        })();

        return new class($resultsGenerator, $result->num_rows, $result->field_count) extends QueryResults {};
    }
}