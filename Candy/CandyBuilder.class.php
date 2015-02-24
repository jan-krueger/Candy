<?php
/**
 * This file contains the CandyBuilder-Class.
 */

namespace SweetCode\Candy;

use PDO;

/**
 * Class CandyBuilder
 * @author Yonas
 * @package SweetCode\Candy
 */
class CandyBuilder implements CandyPacking
{

    /**
     * @var Candy holds the instance of the Candy Class who is running this Builder
     */
    private $database;

    /**
     * @var PDOStatement holds the current PDOStatement
     */
    private $stmt;

    /**
     * @var string holds the query
     */
    private $workingQuery;

    /**
     * @var array holds the fields
     */
    private $fields;

    /**
     * @var string holds the table name
     */
    private $table;

    /**
     * @var array holds the conditions
     */
    private $where;

    /**
     * @var array holds the limit conditions
     */
    private $limit;

    /**
     * @var holds the prepared arguments
     */
    private $prepare = [];

    /**
     * @var bool holds the boolean if the builder is built-up or not.
     */
    private $build = false;


    /**
     * This is the constructor to create a new object of CandyBuilder
     * @param Candy $database the instance of the database
     * @param string $workingQuery the CandyAction
     */
    public function __construct(Candy $database, $workingQuery)
    {
        $this->database = $database;
        $this->workingQuery = $workingQuery;
    }

    /**
     * Sets the used fields
     * @param array $fields the array with the field names
     * @return CandyBuilder
     */
    public function fields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Sets the used table
     * @param string $table the table name
     * @return CandyBuilder
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Sets the 'where'-conditions
     * @param array $where the conditions array
     * @return CandyBuilder
     */
    public function where(array $where)
    {
        $this->where = $where;
        return $this;
    }

    /**
     * Sets the limit
     * @param int $max the maximum amount of results
     * @param int $range the range of the results
     * @return CandyBuilder
     */
    public function limit($max, $range = 0)
    {
        $this->limit['max'] = $max;
        $this->limit['range'] = $range;
        return $this;
    }

    /**
     * This function adds this CandyBuilder instance to a CandyBatch.
     * @param CandyBatch $batch The instance of the batch.
     * @return CandyBuilder
     */
    public function addBatch(CandyBatch $batch)
    {
        $batch->add($this);
        return $this;
    }

    /**
     * This functions returns true when the @see CandyBuilder::build() runs one time or more.
     * @return bool
     */
    public function isBuilt()
    {
        return $this->build;
    }

    /**
     * Build up the query
     * @throws InvalidArgumentException When do you missed to use the {@see CandyBuilder::table()} or you missed to use
     * @return CandyBuilder
     */
    public function build()
    {

        if ($this->workingQuery === null || $this->table === null) {
            throw new InvalidArgumentException(sprintf("Missing arguments (%s %s %s)", $this->workingQuery === null ? '\SweetCode\Candy\CandyBuilder::workingQuery' : null, $this->table === null ? '\SweetCode\Candy\CandyBuilder::table()' : null));
        }

        $this->prepare();

        switch ($this->workingQuery) {

            case CandyAction::SELECT:

                foreach ($this->fields as $field) {
                    if ($field == '*') {
                        $this->prepare['operator'] = ['*'];
                        break;
                    }

                    $this->prepare['operator'][] = "`{$field}`";
                }

                $this->query(sprintf($this->workingQuery, join(', ', $this->prepare['operator']), $this->table, $this->prepare['where'], $this->prepare['limit']));
                $this->bindAll($this->prepare['params']);

                break;

            case CandyAction::INSERT:

                foreach ($this->fields as $field => $value) {
                    $this->prepare['operator'][] = "`{$field}`";
                    $this->prepare['params'][":{$field}"] = $value;
                }

                $this->query(sprintf($this->workingQuery, $this->table, join(', ', $this->prepare['operator']), join(', ', array_keys($this->prepare['params']))));
                $this->bindAll($this->prepare['params']);

                break;

            case CandyAction::UPDATE:

                foreach ($this->fields as $field => $value) {
                    $this->prepare['operator'][] = "{$field} = :{$field}";
                    $this->prepare['params'][":{$field}"] = $value;
                }

                $this->query(sprintf($this->workingQuery, $this->table, join(', ', $this->prepare['operator']), $this->prepare['where'], $this->prepare['limit']));
                $this->bindAll($this->prepare['params']);

                break;

            case CandyAction::DELETE:

                $this->query(sprintf($this->workingQuery, $this->table, $this->prepare['where'], $this->prepare['limit']));
                $this->bindAll($this->prepare['params']);

                break;

        }

        $this->prepare = [];
        $this->build = true;

        return $this;

    }

    /**
     * Returns an array of the result set rows
     * @param integer $method Controls how the next row will be returned to the caller. This value must be one of the PDO::FETCH_* constants, defaulting to value of PDO::ATTR_DEFAULT_FETCH_MODE (which defaults to PDO::FETCH_BOTH)
     * @return array
     */
    public function resultSet($method = PDO::FETCH_ASSOC)
    {
        return $this->stmt->fetchAll($method);
    }

    /**
     * Very similar to the {@see Database::resultSet()} method, the {@see Database::resultSingle} returns a single record from the database.
     * @param integer $method Controls how the next row will be returned to the caller. This value must be one of the PDO::FETCH_* constants, defaulting to value of PDO::ATTR_DEFAULT_FETCH_MODE (which defaults to PDO::FETCH_BOTH)
     * @return mixed
     */
    public function resultSingle($method = PDO::FETCH_ASSOC)
    {
        return $this->stmt->fetch($method);
    }

    /**
     * Returns the number of effected rows from the previous statement.
     * @return int
     */
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    /**
     * Executes the Query
     * @return CandyBuilder
     */
    public function execute()
    {
        $this->stmt->execute();
        return $this;
    }

    /**
     * Returns an array which is filled up with information about the last error
     * @return array
     */
    public function errorInfo()
    {
        return $this->stmt->errorInfo();
    }

    /**
     * This function returns the results as pagination.
     * @param int $perPage how many entrys per page
     * @return array
     */
    public function asPagination($perPage = 5)
    {

        $page = [];
        $pageIndex = 0;
        $entryIndex = 0;

        if (!($this->isBuilt())) {
            return $page;
        }

        $results = $this->resultSet();

        foreach ($results as $result) {
            if ($entryIndex >= $perPage) {
                $pageIndex++;
                $entryIndex = 0;
            }

            $page[$pageIndex][$entryIndex] = $result;
            $entryIndex++;

        }

        return $page;


    }

    /**
     * This function sets the query
     * @param string $query
     * @return CandyBuilder
     */
    private function query($query)
    {
        $this->stmt = $this->database->getConnection()->prepare($query);
        return $this;
    }

    /**
     * Binds a array of parameters to the specified variable name
     * @param $params the array with the parameters
     * @return void
     */
    private function bindAll($params)
    {

        if ($params === null) {
            return;
        }

        if (!(is_array($params))) {
            return;
        }

        foreach ($params as $param => $value) {
            $this->bind($param, $value);
        }

    }

    /**
     * Binds a parameter to the specified variable name
     * @param string $param the parameter
     * @param mixed $value the value of the parameter
     * @param null|integer $type Controls the kind of the given value. This value must be one of the PDO::PARAM_* constants, defaulting to value of PDO::PARAM_STR
     * @return void
     */
    private function bind($param, $value, $type = null)
    {
        $this->stmt->bindValue($param, $value, ($type === null ? Candy::getValueType($value) : $type));
    }

    /**
     * This method prepares the @see \SweetCode\Candy\CandyBuilder::build() method.
     */
    private function prepare()
    {

        $whereString = null;
        $limitString = null;
        $params = null;

        if (!($this->where === null)) {
            $whereString = "WHERE %s";
            $tempWhere = null;

            foreach ($this->where as $field => $options) {
                if (!(array_key_exists('value', $options)) || !(array_key_exists('comparator', $options))) {
                    continue;
                }

                $tempWhere .= "`{$field}` {$options['comparator']} :where{$field}";
                $params[":where{$field}"] = $options['value'];

                if (array_key_exists('operator', $options)) {
                    $tempWhere .= " {$options['operator']} ";
                }
            }

            $whereString = sprintf($whereString, $tempWhere);
        }

        $this->prepare['where'] = $whereString;

        if (!($this->limit === null)) {
            $limitString = "LIMIT %s";
            $tempLimit = null;

            $tempLimit .= "{$this->limit['max']}";

            if ($this->limit['range'] != 0) {
                $tempLimit .= ", {$this->limit['range']}";
            }

            $limitString = sprintf($limitString, $tempLimit);
        }

        $this->prepare['limit'] = $limitString;
        $this->prepare['params'] = $params;

    }

}
