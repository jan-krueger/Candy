<?php

require_once '../Candy/Candy.class.php';
require_once '../Candy/CandyBuilder.class.php';
require_once '../Candy/CandyAction.class.php';

use SweetCode\Candy\Candy;
use SweetCode\Candy\CandyAction;

class CandyTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Candy holds the Candy instance
     */
    private $database;

    public function __construct() {
        $this->database = new Candy("localhost", "root", "", "candy");
    }

    /*
     * This function tries to add a new user to the database
     */
    public function testInsertAction() {
        $this->database->newBuilder(CandyAction::INSERT)
            ->fields(['name' => 'Yonas', 'email' => '@', 'password' => 'blb'])
            ->table('users')
            ->build()
            ->execute();
    }

    /*
     * This function tests the update action
     */
    public function testUpdateAction() {
        $this->database->newBuilder(CandyAction::UPDATE)
            ->fields(['password' => 'itsokaybecauseitssave'])
            ->table('users')
            ->where([
                'name' => [
                    'value' => 'Yonas',
                    'comparator' => '=',
                    'operator' => CandyAction::WHERE_AND
                ],
                'email' => [
                    '@',
                    'comparator' => '='
                ]
            ])
            ->build()
            ->execute();
    }


    /*
     * This function tests the select function
     */
    public function testSelectAction() {
        $result = $this->database->newBuilder(CandyAction::SELECT)
            ->fields(['name'])
            ->table('users')
            ->limit(1)
            ->where(['name' => ['value' => 'Yonas', 'comparator' => "="]])
            ->build()->execute()->resultSet();
    }


}

?>