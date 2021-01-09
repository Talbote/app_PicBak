<?php

namespace App\EventSubscriber;

use App\Entity\Comment;
use App\Entity\Picture;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;


class PictureSubscriber implements EventSubscriberInterface
{

    /**
     * chercher l'user
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {

        $this->security = $security;
    }

    /**
     * on recupere l'evenement en utilisant BeforeEntityPersistedEvent
     */
    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['setUser']
        ];
    }

    /**
     * avant de faire le setUser sur une image/commentaire,  on cree un event
     * et on verifie si l'image/commentaire ah bien été cree par un utilisateur
     * sinon  on enregistre l'user connecté en tant que owner de l'image/commentaire
     */

    public function setUser(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof Picture) {

            $entity->setUser($this->security->getUser());
        }

        if ($entity instanceof Comment) {

            $entity->setUser($this->security->getUser());
        }

    }
}
