<?php

namespace SweetCode\Candy;

use \PDO;

/**
 * Class CandyBuilder
 * @package SweetCode\Candy
 */
class CandyBuilder {

    private $database;

    /**
     * @author Yonas
     * @version 0.1-pre1
     * @var PDOStatement
     */
    private $stmt;

    //building section
    private $workingQuery = null, $fields = null, $table = null, $where = null, $limit = null;

    /**
     * @param Database $database
     * @param $workingQuery
     */
    public function __construct(Candy $database, $workingQuery) {
        $this->database = $database;
        $this->workingQuery = $workingQuery;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function fields(array $fields) {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param $table
     * @return $this
     */
    public function table($table) {
        $this->table = $table;
        return $this;
    }

    /**
     * @param array $where
     * @return $this
     */
    public function where(array $where) {
        $this->where = $where;
        return $this;
    }

    /**
     * @param $max
     * @param int $range
     * @return $this
     */
    public function limit($max, $range = 0) {
        $this->limit['max'] = $max;
        $this->limit['range'] = $range;
        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function build() {

        if(is_null($this->workingQuery) || is_null($this->table)) {
            throw new Exception(sprintf("Missing arguments (%s %s %s)",
                is_null($this->workingQuery) ? 'DatabaseBuilder#workingQuery' : null,
                is_null($this->table) ? 'DatabaseBuilder#table' : null
            ));
        }

        $operator = null;
        $params = null;

        $whereString = null;
        $limitString = null;

        if(!(is_null($this->where))) {

            $whereString = "WHERE %s";
            $tempWhere = null;

            foreach($this->where as $field => $options) {

                if(!(array_key_exists('value', $options)) || !(array_key_exists('comparator', $options))) {
                    continue;
                }

                $tempWhere .= "`{$field}` {$options['comparator']} :where{$field}";
                $params[":where{$field}"] = $options['value'];

                if(array_key_exists('operator', $options)) {

                    $tempWhere .= " {$options['operator']} ";

                }

            }

            $whereString = sprintf($whereString, $tempWhere);

        }

        if(!(is_null($this->limit))) {

            $limitString = "LIMIT %s";
            $tempLimit = null;

            $tempLimit .= "{$this->limit['max']}";

            if($this->limit['range'] != 0) {
                $tempLimit .= ", {$this->limit['range']}";
            }

            $limitString = sprintf($limitString, $tempLimit);

        }

        //$this->workingQuery = DatabaseAction::getPattern($this->workingQuery, $whereString, $limitString);

        switch($this->workingQuery) {

            //SELECT %s FROM `%s`
            //SELECT `name`, `email` FROM `users`
            case CandyAction::SELECT:

                foreach($this->fields as $field) {

                    $operator[] = "`{$field}`";

                }

                echo sprintf($this->workingQuery, join(', ', $operator), $this->table, $whereString, $limitString);
                $this->query(sprintf($this->workingQuery, join(', ', $operator), $this->table, $whereString, $limitString));
                $this->bindAll($params);

                break;

            //INSERT INTO `%s` (%s) VALUES (%s)
            //INSERT INTO `users` (`name`, `email`) VALUES (:name, :email)
            case CandyAction::INSERT:

                foreach($this->fields as $field => $value) {

                    $operator[] = "`{$field}`";
                    $params[":{$field}"] =  $value;

                }

                $this->query(sprintf($this->workingQuery, $this->table, join(', ', $operator), join(', ', array_keys($params))));
                $this->bindAll($params);

                break;

            //UPDATE `%s` SET %s
            //UPDATE `users` SET `name` = :name, `email` = :email
            case CandyAction::UPDATE:

                foreach($this->fields as $field => $value) {

                    $operator[] = "{$field} = :{$field}";
                    $params[":{$field}"] =  $value;

                }

                $this->query(sprintf($this->workingQuery, $this->table, join(', ', $operator), $whereString, $limitString));
                $this->bindAll($params);

                break;

            //DELETE FROM `%s` %s %s
            //DELETE FROM `users`
            case CandyAction::DELETE:

                $this->query(sprintf($this->workingQuery, $this->table, $whereString, $limitString));
                $this->bindAll($params);

                break;

        }

        return $this;

    }

    /**
     * @param $query
     * @return $this
     */
    private function query($query) {
        $this->stmt = $this->database->getDatabase()->prepare($query);
        return $this;
    }

    /**
     * @param $params
     */
    private function bindAll($params) {

        if(is_null($params)) {
            return;
        }

        foreach($params as $param => $value) {
            $this->bind($param, $value);
        }

    }

    /**
     * @param $param
     * @param $value
     * @param null $type
     */
    private function bind($param, $value, $type = null) {

        if (is_null($type)) {
            switch (true) {
                case is_int($value): $type = PDO::PARAM_INT; break;
                case is_bool($value): $type = PDO::PARAM_BOOL; break;
                case is_null($value): $type = PDO::PARAM_NULL; break;
                default: PDO::PARAM_STR; break;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Returns an array of the result set rows.
     * @return array
     */
    public function resultSet($method = PDO::FETCH_ASSOC) {
        return $this->stmt->fetchAll($method);
    }

    /**
     * Very similar to the @see Database#resultSet methd, the @see Database#resultSingle returns a single record from the database.
     * @return mixed
     */
    public function resultSingle($method = PDO::FETCH_ASSOC) {
        return $this->stmt->fetch($method);
    }

    /**
     * Returns the number of effected rows from the previous statement.
     * @return int
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    /**
     * @return $this
     */
    public function execute() {
        $this->stmt->execute();
        return $this;
    }

    /**
     * @return mixed
     */
    public function errorInfo() {
        return $this->stmt->errorInfo();
    }

}

?>