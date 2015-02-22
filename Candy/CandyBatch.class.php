<?php
/**
 * This file contains the CandyBatch class.
 */

namespace SweetCode\Candy;

/**
 * Class CandyBatch
 * @author Yonas
 * @package SweetCode\Candy
 */
class CandyBatch
{

    /**
     * @var array Holds the list of CandyBuilders.
     */
    private $list = [];

    /**
     * @var array Holds the returned results.
     */
    private $result = [];

    /**
     * @var array Holds all errors.
     */
    private $error = [];

    /**
     * This is the constructor.
     */
    public function __construct()
    {
        //nothing to-do here
    }

    /**
     * This function adds a CandyBuilder to the batch.
     * @param CandyBuilder $builder
     */
    public function add(CandyBuilder $builder)
    {
        $this->list[] = $builder;
    }

    /**
     * This function returns the array with all results.
     * @return array
     */
    public function getResults()
    {
        return $this->result;
    }

    /**
     * This function returns all errors.
     * @return array
     */
    public function getErrors()
    {
        return $this->error;
    }

    /**
     * This function executes the batch.
     * @param bool $forceBuild This forces the CandyBuilder to be built-up.
     * @return void
     */
    public function execute($forceBuild = false)
    {

        $this->result = [];
        $this->error = [];

        foreach ($this->list as $entry) {
            if ($entry->isBuilt()) {
                $entry->execute();
                $this->error[] = $entry->errorInfo();

                $this->result[] = $entry->resultSet();


                continue;
            }

            if ($forceBuild && !($entry->isBuilt())) {
                $entry->build()->execute();
                $this->error[] = $entry->errorInfo();
                $this->result[] = $entry->resultSet();

            }

        }
    }

}
