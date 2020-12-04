<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 * @ORM\Table(name="comments")
 */
class Comment
{
    use Timestampable;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @ORM\ManyToOne(targetEntity=Picture::class, inversedBy="comments")
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="comments")
     */
    private $textComment;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $actif;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTextComment(): ?Picture
    {
        return $this->textComment;
    }

    public function setTextComment(?Picture $textComment): self
    {
        $this->textComment = $textComment;

        return $this;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }
}
