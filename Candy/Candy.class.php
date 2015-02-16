<?php
/**
 * This file contains the Candy-Class.
 */

namespace SweetCode\Candy;

use \PDO;

/**
 * Class Candy
 * @author Yonas
 * @package SweetCode\Candy
 */
class Candy {

    /**
     * @var string holds the hostname
     */
    private $host;

    /**
     * @var string holds the username
     */
    private $user;

    /**
     * @var string holds the password
     */
    private $pass;

    /**
     * @var string holds the database name
     */
    private $database;

    /**
     * @var \PDO holds the PDO object
     */
    private $db;

    /**
     * @var \Exception|PDOException holds the latest error
     */
    private $error;

    /**
     * @var PDOStatement holds the latest PDOStatement to use it everywhere in the class
     */
    private $stmt;

    /**
     * @var array holds the default options
     */
    private static $options = [
        PDO::ATTR_PERSISTENT => true,
        PDO::ERRMODE_EXCEPTION => PDO::ERRMODE_EXCEPTION
    ];

    /**
     * The constructor to fill up the basic stuff.
     *
     * @param string $host the hostname
     * @param string $user the username to access the database
     * @param string $password the password to access teh database
     * @param string $database the used database
     */
    public function __construct($host, $user, $password, $database) {

        $this->host = $host;
        $this->user = $user;
        $this->pass = $password;
        $this->database = $database;

        try {

            $this->db = new PDO(sprintf('mysql:host=%s;dbname=%s', $this->host, $this->database), $this->user, $this->pass, Candy::$options);

        } catch(PDOException $e) {
            $this->error = $e;
        }

    }

    /**
     * Returns the PDO instance, used by this class.
     * @return PDO
     */
    public function getConnection() {
        return $this->db;
    }

    /**
     * This functions returns a new CandyBuilder to build up the statement
     * @param CandyAction $action
     * @throws \Exception When the given action is not a valid CandyAction syntax
     * @return CandyBuilder
     */
    public function newBuilder($action) {

        if(!(CandyAction::checkSyntax($action))) {
            throw new \Exception("The given action is invalid.");
        }

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
     * @return Candy
     */
    public function bindAll(array $params) {

        foreach($params as $param => $value) {
            $this->bind($param, $value);
        }
        return $this;
    }

    /**
     * In order to prepare the SQL query, the method bind the inputs with the placeholders that you placed.
     * @param string $param is the placeholder value that will be usin in the SQL statement.
     * @param mixed $value is the actual value that you want to bind to the placeholder.
     * @param null|integer $type Controls the kind of the given value. This value must be one of the PDO::PARAM_* constants, defaulting to value of PDO::PARAM_STR
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
     * The method executes the statement.
     * @return Candy
     */
    public function execute() {

        $this->stmt->execute();

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
     * Very similar to the @see \SweetCode\Candy\Candy::resultSet() method, the @see \SweetCode\Candy\Candy::resultSingle() returns a single record from the database.
     * @return mixed
     */
    public function resultSingle() {
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Returns an array which is filled up with information about the last error
     * @return array
     */
    public function errorInfo() {
        return $this->stmt->errorInfo();
    }

}

?>