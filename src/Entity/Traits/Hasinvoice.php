<?php
/**
 * Created by PhpStorm.
 * User: odolinski
 * Date: 09/01/2021
 * Time: 02:54
 */

namespace App\Entity\Traits;


trait Hasinvoice
{

    /**
     * @var boolean
     *
     * @ORM\Column(name="invoice", type="boolean")
     */
    private $invoice = false;

    /**
     * @param boolean $invoice
     * @return $this
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isInvoice()
    {
        return $this->invoice;
    }


}