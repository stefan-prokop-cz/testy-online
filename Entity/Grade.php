<?php

namespace tests\testBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Grade
 */
class Grade
{
    /**
     * @var integer
     */
    private $testId;

    /**
     * @var integer
     */
    private $od;

    /**
     * @var integer
     */
    private $do;

    /**
     * @var integer
     */
    private $znamka;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set testId
     *
     * @param integer $testId
     * @return Grade
     */
    public function setTestId($testId)
    {
        $this->testId = $testId;

        return $this;
    }

    /**
     * Get testId
     *
     * @return integer 
     */
    public function getTestId()
    {
        return $this->testId;
    }

    /**
     * Set od
     *
     * @param integer $od
     * @return Grade
     */
    public function setOd($od)
    {
        $this->od = $od;

        return $this;
    }

    /**
     * Get od
     *
     * @return integer 
     */
    public function getOd()
    {
        return $this->od;
    }

    /**
     * Set do
     *
     * @param integer $do
     * @return Grade
     */
    public function setDo($do)
    {
        $this->do = $do;

        return $this;
    }

    /**
     * Get do
     *
     * @return integer 
     */
    public function getDo()
    {
        return $this->do;
    }

    /**
     * Set znamka
     *
     * @param integer $znamka
     * @return Grade
     */
    public function setZnamka($znamka)
    {
        $this->znamka = $znamka;

        return $this;
    }

    /**
     * Get znamka
     *
     * @return integer 
     */
    public function getZnamka()
    {
        return $this->znamka;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
