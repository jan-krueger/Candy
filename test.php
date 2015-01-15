<?php

require_once 'Candy.class.php';
require_once 'CandyBuilder.class.php';
require_once 'CandyAction.enum.php';

use SweetCode\Candy\Candy;
use SweetCode\Candy\CandyAction;

$database = new Candy("localhost", "root", "", "quickdatabase");



//insert
$database->newBuilder(CandyAction::INSERT)
                ->fields(['name' => 'Yonas', 'email' => '@', 'password' => 'blb'])
                ->table('users')
                ->build()
                ->execute();

//update
$database->newBuilder(CandyAction::UPDATE)
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


//select
$result = $database->newBuilder(CandyAction::SELECT)
        ->fields(['name'])
        ->table('users')
        ->limit(1)
        ->where(['name' => ['value' => 'Yonas', 'comparator' => "="]])
        ->build()->execute()->resultSet();



print_r($result);

?>