<?php
/**
 * This is the class to test all functions of Candy.
 */


require_once 'Candy/Candy.class.php';
require_once 'Candy/CandyPacking.class.php';
require_once 'Candy/CandyBuilder.class.php';
require_once 'Candy/CandyAction.class.php';

use SweetCode\Candy\Candy;
use SweetCode\Candy\CandyAction;

/**
 * Class CandyTest
 * @author Yonas
 */
class CandyTest extends PHPUnit_Extensions_Database_TestCase {

    /**
     * @var Candy holds the Candy instance
     */
    private $database;

    /**
     * @var PHPUnit_Extensions_Database_DB_IDatabaseConnection holds the default db connection
     */
    private $defaultDBConnection;

    /**
     * This is the constructor it has no function.
     */
    public function __construct() {}

    /**
     * This methods returns the current connection.
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection() {

        if(is_null($this->database)) {
            $candy = $this->database = new Candy("localhost", "root", "", "candy");
            return $this->defaultDBConnection = $this->createDefaultDBConnection($candy->getConnection(), 'candy:');
        }

        return $this->defaultDBConnection;

    }

    /**
     * This method returns the data set.
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()  {
        return $this->createFlatXmlDataSet('tests/users.xml');
    }

    /**
     * This function tries to add a new user to the database
     * @return void
     */
    public function testInsertAction() {
        $builder = $this->database->newBuilder(CandyAction::INSERT)
            ->fields(['name' => 'Yonas', 'email' => 'yonas@example.com', 'password' => 'blb'])
            ->table('users')
            ->build()
            ->execute();

        $this->assertEquals(0, $builder->errorInfo()[0], $builder->errorInfo());
    }

    /**
     * This function tests the update action
     * @return void
     */
    public function testUpdateAction() {
        $builder = $this->database->newBuilder(CandyAction::UPDATE)
            ->fields([
                'password' => 'itsokaybecauseitssave',
                'email' => 'awe@awesome.com'
             ])
            ->table('users')
            ->where([
                'name' => [
                    'value' => 'Yonas',
                    'comparator' => '=',
                    'operator' => CandyAction::WHERE_AND
                ],
                'email' => [
                    'value' => 'yonas@example.com',
                    'comparator' => '='
                ]
            ])
            ->build()
            ->execute();

        $this->assertEquals(0, $builder->errorInfo()[0], $builder->errorInfo());
    }


    /**
     * This function tests the select function
     * @return void
     */
    public function testSelectAction() {
        $result = $this->database->newBuilder(CandyAction::SELECT)
            ->fields(['*'])
            ->table('users')
            ->limit(1)
            ->where(['name' => ['value' => 'Yonas', 'comparator' => "="]])
            ->build()->execute()->resultSet();

        $this->assertEquals(
            ['name' => 'Yonas', 'email' => 'awe@awesome.com', 'password' => 'itsokaybecauseitssave'],
            $result[0],
            "Select Action failed!"
        );
    }

    /**
     * This function tests the delete function
     * @return void
     */
    public function testDeleteAction() {
        $builder = $this->database->newBuilder(CandyAction::DELETE)
                    ->table('users')
                    ->limit(1)
                    ->build()
                    ->execute();

        $this->assertEquals(0, $builder->errorInfo()[0], $builder->errorInfo());
    }


}

?>