<?php

namespace App\Entity;


use App\Entity\Traits\Haspremium;
use App\Entity\Traits\Hasinvoice;
use App\Entity\Traits\Timestampable;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="users")
 * @Vich\Uploadable
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 * @UniqueEntity(fields={"nickName"}, message="There is already an account with this nick name")
 */
class User implements UserInterface, \Serializable
{
    use Timestampable;
    use Haspremium;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Please enter your nick name")
     *
     */
    private $nickName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Please enter your first name")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Please enter your last name")
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Please enter your email address ")
     * @Assert\Email(message="Please enter a valid email address")
     */
    private $email;

    // ... other fields

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="picture_image", fileNameProperty="imageName")
     * @Assert\Image(maxSize = "10M")
     *
     * @var File|null
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageName;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="user", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=Picture::class, mappedBy="user", orphanRemoval=true)
     */
    private $pictures;

    /**
     * @ORM\OneToMany(targetEntity=PictureLike::class, mappedBy="user")
     */
    private $likes;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isBanned = false;

    /**
     * @var string
     *
     * @ORM\Column(name="charge_id", type="string", length=255, nullable=true)
     */
    protected $chargeId;


    /**
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Gedmo\Slug(fields={"nickName"})
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity=Invoice::class, mappedBy="user")
     */
    private $invoice;


    /**
     * @return mixed
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->invoice = new ArrayCollection();


    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNickName(): ?string
    {
        return $this->nickName;
    }

    public function setNickName(string $nickName): self
    {
        $this->nickName = $nickName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // pour toute information temporaire sensible au niveau des données de  l'utilisateur ,
        // on nettoyer tout ici  via a la command  $this->plainPassword = null;

    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setTextComment($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTextComment() === $this) {
                $comment->setTextComment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Picture[]
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures[] = $picture;
            $picture->setUser($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): self
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getUser() === $this) {
                $picture->setUser(null);
            }
        }

        return $this;
    }


    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->setUpdatedAt(new \DateTimeImmutable);
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }


    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isBanned(): bool
    {
        return $this->isBanned;
    }

    public function setIsBanned(bool $isBanned): self
    {
        $this->isBanned = $isBanned;

        return $this;
    }

    /**
     * @param string $chargeId
     * @return $this
     */
    public function setChargeId($chargeId)
    {
        $this->chargeId = $chargeId;

        return $this;
    }

    /**
     * @return string
     */
    public function getChargeId()
    {
        return $this->chargeId;
    }


    /**
     * si l'utilisateur est abonné
     */
    public function isSubscriber(User $user, EntityManagerInterface $em)
    {

        if ($user->getChargeId(true)) {

            $load_checkout_session = new \Stripe\StripeClient('sk_test_51I0mX6L4sACyrZxifOb3sy4ExerZ8vd22tkbEDoH0LclFv4cKIfdxEA17vmaMNMx1LX7snYZAVo3A4mDWSBgURdG0013ar2A9E');
            $chargeId = $user->getChargeId();

            $client_status = $load_checkout_session->checkout->sessions->retrieve($chargeId, []);

            $subscription = $client_status->subscription;
            $user_subscription = $load_checkout_session->subscriptions->retrieve(
                $subscription,
                []
            );


            if ($user_subscription->status == "canceled") {


                $user->setChargeId(false);
                $user->setPremium(false);
                $em->flush();

            }
            if ($user_subscription->status == "active") {

                $user->setPremium(true);
                $em->flush();
            }
            if ($user_subscription->status == "unpaid") {

                $user->setPremium(false);
                $em->flush();
            }

        }

        $user->getChargeId(false);
        $em->flush();

        return false;

    }


    public function serialize()
    {

        return serialize(array(
            $this->id,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->password

        ));

    }

    public function unserialize($serialized)
    {

        list (
            $this->id,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->password
            ) = unserialize($serialized);
    }


    /**
     * @return Collection|PictureLike[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(PictureLike $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setUser($this);
        }

        return $this;
    }

    public function removeLike(PictureLike $like): self
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getUser() === $this) {
                $like->setUser(null);
            }
        }

        return $this;
    }

    /**
     * METHODE getFullName();
     */

    public function getFullName(): string
    {
        return (string)$this->getFirstName() . '  ' . $this->getLastName();
    }

    public function __toString()
    {
        return $this->email;
    }

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoice(): Collection
    {
        return $this->invoice;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoice->contains($invoice)) {
            $this->invoice[] = $invoice;
            $invoice->setUser($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoice->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getUser() === $this) {
                $invoice->setUser(null);
            }
        }

        return $this;
    }


}
