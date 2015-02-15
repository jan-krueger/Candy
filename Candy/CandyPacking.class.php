<?php

namespace SweetCode\Candy;

interface CandyPacking {

    /**
     * Sets the used fields
     * @param array $fields the array with the field names
     * @return mixed
     */
    public function fields(array $fields);

    /**
     * Sets the used table
     * @param string $table the table name
     * @return mixed
     */
    public function table($table);

    /**
     * Sets the 'where'-conditions
     * @param array $where the conditions array
     * @return mixed
     */
    public function where(array $where);

    /**
     * Sets the limit
     * @param $max the maximum amount of results
     * @param int $range the range of the results
     * @return mixed
     */
    public function limit($max, $range = 0);

    /**
     * Build up the query
     * @return mixed
     */
    public function build();

    /**
     * Returns an array of the result set rows
     * @param integer $method Controls how the next row will be returned to the caller. This value must be one of the PDO::FETCH_* constants, defaulting to value of PDO::ATTR_DEFAULT_FETCH_MODE (which defaults to PDO::FETCH_BOTH)
     * @return mixed
     */
    public function resultSet($method = PDO::FETCH_ASSOC);

    /**
     * Very similar to the {@see Database::resultSet()} method, the {@see Database::resultSingle} returns a single record from the database.
     * @param integer $method Controls how the next row will be returned to the caller. This value must be one of the PDO::FETCH_* constants, defaulting to value of PDO::ATTR_DEFAULT_FETCH_MODE (which defaults to PDO::FETCH_BOTH)
     * @return mixed
     */
    public function resultSingle($method = PDO::FETCH_ASSOC);

    /**
     * Returns the number of effected rows from the previous statement.
     * @return int
     */
    public function rowCount();

    /**
     * Executes the Query
     * @return mixed
     */
    public function execute();

    /**
     * Returns an array which is filled up with information about the last error
     * @return array
     */
    public function errorInfo();

}

?>