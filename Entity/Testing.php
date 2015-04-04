<?php

namespace tests\testBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Testing
 */
class Testing
{
    /**
     * @var float
     */
    private $loginTime;

    /**
     * @var float
     */
    private $tconfirmTime;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set loginTime
     *
     * @param float $loginTime
     * @return Testing
     */
    public function setLoginTime($loginTime)
    {
        $this->loginTime = $loginTime;

        return $this;
    }

    /**
     * Get loginTime
     *
     * @return float 
     */
    public function getLoginTime()
    {
        return $this->loginTime;
    }

    /**
     * Set tconfirmTime
     *
     * @param float $tconfirmTime
     * @return Testing
     */
    public function setTconfirmTime($tconfirmTime)
    {
        $this->tconfirmTime = $tconfirmTime;

        return $this;
    }

    /**
     * Get tconfirmTime
     *
     * @return float 
     */
    public function getTconfirmTime()
    {
        return $this->tconfirmTime;
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
    /**
     * @var float
     */
    private $selectTestTime;


    /**
     * Set selectTestTime
     *
     * @param float $selectTestTime
     * @return Testing
     */
    public function setSelectTestTime($selectTestTime)
    {
        $this->selectTestTime = $selectTestTime;

        return $this;
    }

    /**
     * Get selectTestTime
     *
     * @return float 
     */
    public function getSelectTestTime()
    {
        return $this->selectTestTime;
    }
}
