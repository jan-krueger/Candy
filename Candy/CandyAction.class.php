<?php
/**
 * This file contains the CandyAction-Class.
 */

namespace SweetCode\Candy;

/**
 * Class CandyAction
 * @author Yonas
 * @package SweetCode\Candy
 */
class CandyAction
{

    /**
     * @var string holds the syntax of the insert query
     */
    const INSERT = "INSERT INTO `%s` (%s) VALUES (%s)"; //TABLE, FIELDS, VALUES

    /**
     * @var string holds the syntax of the update query
     */
    const UPDATE = "UPDATE `%s` SET %s %s %s"; //TABLE, FIELDS'n'VALUES, WHERE, LIMIT

    /**
     * @var string holds the syntax of the select query
     */
    const SELECT = "SELECT %s FROM `%s` %s %s"; //FIELDS, TABLE, WHERE, LIMIT

    /**
     * @var string holds the syntax of the delete query
     */
    const DELETE = "DELETE FROM `%s` %s %s"; //TABLE, WHERE, LIMIT

    /**
     * @var string holds the where 'and' delimiter
     */
    const WHERE_AND = "AND";

    /**
     * @var string holds the where 'or' delimiter
     */
    const WHERE_OR = "OR";

    /**
     * This method checks whether the given Syntax-String has a correct syntax.
     * @param string $given The syntax string.
     * @return bool Returns true when the syntax is right.
     */
    public static function checkSyntax($given) {

        switch($given) {

            case CandyAction::INSERT:
                return true;

            case CandyAction::UPDATE:
                return true;

            case CandyAction::SELECT:
                return true;

            case CandyAction::DELETE:
                return true;

        }

        return false;

    }

}

?>