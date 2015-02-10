<?php

namespace SweetCode\Candy;

use \PDO;

/**
 * Class Candy
 * @author Yonas
 * @version 0.1-pre1
 * @package SweetCode\Candy
 */
class Candy {

    /**
     * @var string holds the hostname
     * @var string holds the username
     * @var string holds the password
     * @var string holds the database ma,e
     */
    private $host = "localhost", $user = "root", $pass = "", $name = "quickdatabase";

    /**
     * @var \PDO holds the PDO object
     * @var string holds the latest error
     */
    private $db, $error;

    /**
     * @var PDOStatement holds the latest PDOStatement to use it everywhere in the class
     */
    private $stmt;

    private static $options = [
        PDO::ATTR_PERSISTENT => true,
        PDO::ERRMODE_EXCEPTION => PDO::ERRMODE_EXCEPTION
    ];

    /**
     * The constructor to fill up the basic stuff.
     * @param $host the hostname
     * @param $user the username to access the database
     * @param $pass the password to access teh database
     * @param $name the used database
     */
    public function __construct($host, $user, $pass, $name) {

        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->name = $name;

        try {

            $this->db = new PDO(sprintf('mysql:host=%s;dbname=%s', $this->host, $this->name), $this->user, $this->pass, Candy::$options);

        } catch(PDOException $e) {
            $this->error = $e;
        }

    }

    /**
     * Returns the PDO instance, used by this class.
     * @return PDO
     */
    public function getDatabase() {
        return $this->db;
    }

    /**
     * @param CandyAction $action
     */
    public function newBuilder($action) {
        return new CandyBuilder($this, $action);
    }


    /**
     * The method introduces the stmt variable to hold the statement.
     * @param $query
     * @return $this
     */
    public function query($query) {
        $this->stmt = $this->db->prepare($query);
        return $this;
    }

    /**
     * In order to prepare the SQL query, the method bind the inputs with the placeholders you placed.
     * @param array $params is an associative array. The key is the placeholder value and the value is the actual value that you want to bind to the placeholder.
     */
    public function bindAll(array $params) {

        foreach($params as $param => $value) {
            $this->bind($param, $value);
        }
        return $this;
    }

    /**
     * In order to prepare the SQL query, the method bind the inputs with the placeholders that you placed.
     * @param $param is the placeholder value that will be usin in the SQL statement.
     * @param $value is the actual value that you want to bind to the placeholder.
     * @return $this
     */
    public function bind($param, $value, $type = null) {

        if (is_null($type)) {
            switch ($value) {
                case is_int($value): $type = PDO::PARAM_INT; break;
                case is_bool($value): $type = PDO::PARAM_BOOL; break;
                case is_null($value): $type = PDO::PARAM_NULL; break;
                default: $type = PDO::PARAM_STR; break;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }

    /**
     * The method executes the prepared statement.
     * @return $this
     */
    public function execute() {

        //reset
        $this->workingQuery = null;
        $this->fields = null;
        $this->table = null;
        $this->where = null;

        return $this;
    }

    /**
     * Returns an array of the result set rows.
     * @return array
     */
    public function resultSet() {
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Very similar to the @see Candy::resultSet() method, the @see Candy::resultSingle() returns a single record from the database.
     * @return mixed
     */
    public function resultSingle() {
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

}

?>