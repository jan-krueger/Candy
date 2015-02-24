<?php
/**
 * This is the class to test all functions of Candy.
 */


require_once dirname(__FILE__) . '/../Candy/Candy.class.php';
require_once dirname(__FILE__) . '/../Candy/CandyPacking.class.php';
require_once dirname(__FILE__) . '/../Candy/CandyBatch.class.php';
require_once dirname(__FILE__) . '/../Candy/CandyBuilder.class.php';
require_once dirname(__FILE__) . '/../Candy/CandyAction.class.php';

use SweetCode\Candy\Candy;
use SweetCode\Candy\CandyAction;

/**
 * Class CandyTest
 * @author Yonas
 */
class CandyTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Candy holds the Candy instance
     */
    private $database;


    /**
     * This is the constructor it has no function.
     */
    public function __construct()
    {
        $this->database = new Candy("localhost", "root", "", "candy");
    }

    /**
     * This function tries to add a new user to the database
     * @return void
     */
    public function testInsertAction()
    {
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
    public function testUpdateAction()
    {
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
    public function testSelectAction()
    {
        $result = $this->database->newBuilder(CandyAction::SELECT)
            ->fields(['*'])
            ->table('users')
            ->limit(1)
            ->where(
                [
                    'name' => ['value' => 'Yonas', 'comparator' => "=", 'operator' => CandyAction::WHERE_AND],
                    'email' => ['value' => 'awe@awesome.com', 'comparator' => "="]
                ]
            )
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
    public function testDeleteAction()
    {
        $builder = $this->database->newBuilder(CandyAction::DELETE)
                    ->table('users')
                    ->limit(1)
                    ->build()
                    ->execute();

        $this->assertEquals(0, $builder->errorInfo()[0], $builder->errorInfo());
    }

    public function testBatchFunction()
    {

        $batch = new \SweetCode\Candy\CandyBatch();

        $this->database->newBuilder(CandyAction::INSERT)
            ->fields(['name' => 'Obama', 'email' => 'obama@example.com', 'password' => 'abac'])
            ->table('users')
            ->build()->addBatch($batch);

        $this->database->newBuilder(CandyAction::SELECT)
            ->fields(['*'])
            ->table('users')
            ->addBatch($batch);

        $batch->execute(true);

        $this->assertEquals(true, is_array($batch->getResults()), "Batch returns invalid results.");
        $this->assertEquals(true, is_array($batch->getErrors()), "Batch returns invalid errros.");

    }

    /**
     * Tests the pagination function.
     */
    public function testPagination()
    {

        $pagination = $this->database->newBuilder(CandyAction::SELECT)
                    ->fields(['*'])
                    ->table('articles')
                    ->build()->execute()->asPagination(3);

        $this->assertEquals(3, count($pagination), "The pagination function stops working.");

    }

    /**
     * Tests the Exception of the @see \SweetCode\Candy\Candy::newBuilder() method
     * @expectedException \InvalidArgumentException
     */
    public function testBuilderExceptionIfInvalidActionIsGiven()
    {

        $this->database->newBuilder("JUST A STUPID TEST");

    }

    /**
     * Tests the Exception of the @see \SweetCode\Candy\Candy::__constructor() method
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorIfInvalidArgumentIsGiven()
    {

        new Candy("localhost", "root", "", null);

    }

    /**
     * Tests the Exception of the @see \SweetCode\Candy\Candy::query() method
     * @expectedException \InvalidArgumentException
     */
    public function testQueryIfInvalidArgumentIsGiven()
    {

        $this->database->query(null);

    }

    /**
     * Tests the Exception of the @see \SweetCode\Candy\Candy::bindAll() method when a non-array argument is given
     * @expectedException \InvalidArgumentException
     */
    public function testBindAllIfInvalidArgumentIsGivenInThisCaseAString()
    {

        $this->database->bindAll("JUST A STUPID TEST");

    }

    /**
     * Tests the Exception of the @see \SweetCode\Candy\Candy::bindAll() method when the argument is null
     * @expectedException \InvalidArgumentException
     */
    public function testBindAllIfInvalidArgumentIsGivenInThisCaseNull()
    {

        $this->database->bindAll(null);

    }

    /**
     * Tests the Exception of the @see \SweetCode\Candy\Candy::bind() method when the argument is null
     * @expectedException \InvalidArgumentException
     */
    public function testBindIfInvalidArgumentIsGivenInThisCaseNull()
    {

        $this->database->bind(null, "JUST A STUPID TEST");

    }

}

?>