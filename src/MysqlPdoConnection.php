<?php

declare(strict_types=1);

namespace Pst\MysqlDatabase;

use Pst\Database\Query\QueryResults;
use Pst\Database\Query\IQueryResults;

use Pst\Database\Connections\DatabaseConnection;

use Pst\Database\Exceptions\DatabaseException;

use PDO;
use Generator;
use InvalidArgumentException;
use PDOException;
use Pst\Database\Exceptions\QueryConstraintException;
use Pst\Database\Exceptions\QueryConstraintExceptionType;

use function Pst\Core\pd;

class MysqlPdoConnection extends MysqlConnection implements IMysqlConnection {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Get the last insert id
     * 
     * @return string 
     */
    public function lastInsertId(): ?string {
        if (($lastInsertId = $this->pdo->lastInsertId()) === false) {
            return null;
        }

        return $lastInsertId;
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

        $stmt = null;

        try {
            if (count($parameters) > 0) {
                $stmt = $this->pdo->prepare($query);

                if ($stmt === false) {
                    throw new DatabaseException("Error preparing query");
                }

                if ($stmt->execute($parameters) === false) {
                    throw new DatabaseException("Error executing query");
                }
                
            } else {
                $stmt = $this->pdo->query($query);

                if ($stmt === false) {
                    throw new DatabaseException("Error executing query");
                }
            }

        } catch (PDOException $e) {
            $errorInfo = $e->errorInfo;

            if ($errorInfo[1] == 1452) {
                throw new QueryConstraintException(QueryConstraintExceptionType::FOREIGN_KEY(), $query, $errorInfo[2], $errorInfo[1]);
            } else if ($errorInfo[1] == 1062) {
                throw new QueryConstraintException(QueryConstraintExceptionType::UNIQUE_KEY(), $query, $errorInfo[2], $errorInfo[1]);
            } else if ($errorInfo[1] == 1064) {
                throw new QueryConstraintException(QueryConstraintExceptionType::SYNTAX_ERROR(), $query, $errorInfo[2], $errorInfo[1]);
            } else {
                throw $e;
            }
        }

        if ($stmt->errorCode() !== '00000') {
            throw new DatabaseException($stmt->errorInfo()[2]);
        }

        $resultsGenerator = (function() use ($stmt): Generator {
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                yield $row;
            }
        })();

        return new class($resultsGenerator, $stmt->rowCount(), $stmt->columnCount()) extends QueryResults {
        };
    }
}