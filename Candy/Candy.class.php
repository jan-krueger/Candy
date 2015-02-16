<?php
/**
 * This file contains the Candy-Class.
 */

namespace SweetCode\Candy;

use \PDO;
use \PDOException;

/**
 * Class Candy
 * @author Yonas
 * @package SweetCode\Candy
 */
class Candy
{

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
    private $options;

    /**
     * The constructor to fill up the basic stuff.
     *
     * @param string $host the hostname
     * @param string $user the username to access the database
     * @param string $password the password to access the database
     * @param string $database the used database
     * @param array $options contains the options for the PDO object
     * @throws \InvalidArgumentException If one or more given arguments are null.
     */
    public function __construct($host, $user, $password, $database, $options = null)
    {

        $this->host = $host;
        $this->user = $user;
        $this->pass = $password;
        $this->database = $database;
        $this->options  = ($options === null ? [PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => PDO::ERRMODE_EXCEPTION] : []);

        if ($host === null || $user === null || $password === null || $database === null) {
            throw new \InvalidArgumentException("One or more given arguments are null.");
        }

        try {
            $this->db = new PDO(sprintf('mysql:host=%s;dbname=%s', $this->host, $this->database), $this->user, $this->pass, $this->options);
        } catch (PDOException $e) {
            $this->error = $e;
        }

    }

    /**
     * Returns the PDO instance, used by this class.
     * @return PDO
     */
    public function getConnection()
    {
        return $this->db;
    }

    /**
     * This functions returns a new CandyBuilder to build up the statement
     * @param CandyAction $action
     * @throws \InvalidArgumentException When the given action is not a valid CandyAction syntax
     * @return CandyBuilder
     */
    public function newBuilder($action)
    {

        if (!(CandyAction::checkSyntax($action))) {
            throw new \InvalidArgumentException("The given action is invalid.");
        }

        return new CandyBuilder($this, $action);
    }


    /**
     * The method introduces the stmt variable to hold the statement.
     * @param $query
     * @throws \InvalidArgumentException When the given query value is null
     * @return $this
     */
    public function query($query)
    {

        if ($query === null) {
            throw new \InvalidArgumentException("The query can't be null.");
        }

        $this->stmt = $this->db->prepare($query);
        return $this;
    }

    /**
     * In order to prepare the SQL query, the method bind the inputs with the placeholders you placed.
     * @param array $params is an associative array. The key is the placeholder value and the value is the actual value that you want to bind to the placeholder.
     * @throws \InvalidArgumentException When the given argument is null or the given argument is not an array
     * @return Candy
     */
    public function bindAll($params)
    {

        if (!(is_array($params))) {
            throw new \InvalidArgumentException("The given params is not an array.");
        }

        if ($params === null) {
            throw new \InvalidArgumentException("The given params is null.");
        }

        foreach ($params as $param => $value) {
            $this->bind($param, $value);
        }
        return $this;
    }

    /**
     * In order to prepare the SQL query, the method bind the inputs with the placeholders that you placed.
     * @param string $param is the placeholder value that will be usin in the SQL statement.
     * @param mixed $value is the actual value that you want to bind to the placeholder.
     * @param null|integer $type Controls the kind of the given value. This value must be one of the PDO::PARAM_* constants, defaulting to value of PDO::PARAM_STR
     * @throws \InvalidArgumentException When the given param is null.
     * @return $this
     */
    public function bind($param, $value, $type = null)
    {

        if ($param === null) {
            throw new \InvalidArgumentException("The given param is null.");
        }

        $this->stmt->bindValue($param, $value, ($type === null ? Candy::getValueType($value) : $type));
        return $this;
    }

    /**
     * The method executes the statement.
     * @return Candy
     */
    public function execute()
    {

        $this->stmt->execute();

        return $this;
    }

    /**
     * Returns an array of the result set rows.
     * @return array
     */
    public function resultSet()
    {
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Very similar to the @see \SweetCode\Candy\Candy::resultSet() method, the @see \SweetCode\Candy\Candy::resultSingle() returns a single record from the database.
     * @return mixed
     */
    public function resultSingle()
    {
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
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
     * This function checks the value and returns the given
     * @param $value the value to check the type of
     * @return int
     */
    public static function getValueType($value)
    {
        switch ($value) {

            case is_int($value):
                return PDO::PARAM_INT;

            case is_bool($value):
                return PDO::PARAM_BOOL;

            case ($value === null):
                return PDO::PARAM_NULL;

            default:
                return PDO::PARAM_STR;

        }
    }

}
