<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use App\Entity\Traits\Timestampable;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="invoices")
 * @ORM\Entity(repositoryClass=InvoiceRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */

class Invoice
{

    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="urlPdf", type="string", length=255)
     */
    private $urlPdf;



    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="invoice")
     */
    private $user;


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUrlPdf(): ?string
    {
        return $this->urlPdf;
    }

    public function setUrlPdf(string $urlPdf): self
    {
        $this->urlPdf = $urlPdf;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

}
