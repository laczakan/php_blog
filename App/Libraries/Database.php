<?php

namespace App\Libraries;

use PDO;
use PDOStatement;

/**
 * Database library to execute db queries.
 *
 * @category Library
 * @package  App
 */
class Database
{
    private $pdo;

    /**
     * Class constructor. Create a new PDO object.
     *
     * @see https://www.php.net/manual/en/class.pdo.php
     */
    public function __construct()
    {
        // Set the connection to the database (constants are from the `App/config/database.php` config file)
        $this->pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        // Set an attribute to throw an exceptions if any error occurs
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Get the PDO object.
     *
     * @return PDO
     */
    public function getDb(): PDO
    {
        return $this->pdo;
    }

    /**
     * Execute a SQL query.
     * Used for SELECT queries.
     *
     * @param string $sql    SQL query to execute
     * @param array  $params Parameters to bind
     *
     * @return PDOStatement
     * @see    https://www.php.net/manual/en/class.pdostatement.php
     */
    public function executeQuery($sql, $params = []): PDOStatement
    {
        // Prepare SQL query
        $query = $this->getDb()->prepare($sql);
        if ($query) {
            // Execute the query with given params
            $query->execute($params);
        }
        return $query;
    }

    /**
     * Find all records.
     *
     * @param string $sql     SQL query to execute
     * @param array  $params  Parameters to bind
     * @param int    $results results type (get generic object - stdClass)
     *
     * @return array Array of objects
     * @see    https://www.php.net/manual/en/pdostatement.fetchall.php
     */
    public function findAll($sql, $params = [], $results = PDO::FETCH_OBJ): array
    {
        // Execute PDO statement
        $query = $this->executeQuery($sql, $params);
        // Set fetch style to objects and return the results
        return $query->fetchAll($results);
    }

    /**
     * Find one record.
     *
     * @param string $sql    SQL query to execute
     * @param array  $params Parameters to bind
     *
     * @return object One object
     * @see    https://www.php.net/manual/en/pdostatement.fetch.php
     */
    public function findOne($sql, $params = [])
    {
        // Execute PDO statement
        $query = $this->executeQuery($sql, $params);
        // Set fetch style to objects and return a result
        $result = $query->fetch(PDO::FETCH_OBJ);
        // return result if true null if false
        return $result ? $result : null;
    }

    /**
     * Execute a SQL query.
     * Used for UPDATE, DELETE queries.
     *
     * @param string $sql    SQL query to execute
     * @param array  $params Parameters to bind
     *
     * @return bool
     * @see    https://www.php.net/manual/en/pdostatement.execute.php
     */
    public function execute($sql, $params = []): bool
    {
        // Prepare SQL query
        $query = $this->getDb()->prepare($sql);
        if ($query) {
            // Execute the query and return the status
            return $query->execute($params);
        }
        return false;
    }
}
