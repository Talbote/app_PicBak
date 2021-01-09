<?php
/**
 * Created by PhpStorm.
 * User: odolinski
 * Date: 23/12/2020
 * Time: 12:07
 */

namespace App\Entity\Traits;


trait Haspremium
{

    /**
     * @var boolean
     *
     * @ORM\Column(name="premium", type="boolean")
     */
    private $premium = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="recordInvoice", type="boolean")
     */
    private $recordInvoice = false;


    /**
     * @param boolean $premium
     * @return $this
     */
    public function setPremium($premium)
    {
        $this->premium = $premium;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isPremium()
    {
        return $this->premium;
    }



    /**
     * @param boolean $recordInvoice
     * @return $this
     */
    public function setRecordInvoice($recordInvoice)
    {
        $this->recordInvoice = $recordInvoice;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRecordInvoice()
    {
        return $this->recordInvoice;
    }



}