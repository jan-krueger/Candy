<?php

namespace SweetCode\Candy;

/**
 * Class CandyAction
 * @author Yonas
 * @package SweetCode\Candy
 */
class CandyAction {

    const INSERT = "INSERT INTO `%s` (%s) VALUES (%s)"; //TABLE, FIELDS, VALUES
    const UPDATE = "UPDATE `%s` SET %s %s %s"; //TABLE, FIELDS'n'VALUES, WHERE, LIMIT
    const SELECT = "SELECT %s FROM `%s` %s %s"; //FIELDS, TABLE, WHERE, LIMIT
    const DELETE = "DELETE FROM `%s` %s %s"; //TABLE, WHERE, LIMIT

    const WHERE_AND = "AND";
    const WHERE_OR = "OR";
}

?>