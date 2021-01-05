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
     * @ORM\Column(name="premium", type="boolean", options={"default":false})
     */
    private $premium = false;

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

}