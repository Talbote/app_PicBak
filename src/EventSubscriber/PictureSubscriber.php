<?php

namespace App\EventSubscriber;

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
     * on recupere l'evenement
     */
    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['setUser']
        ];
    }

    /**
     * avant de faire le setUser on cree un event
     * et on verifie si l'image ah bien été cree par le utilisateur connecté
     * si c'est le cas on enregistre en tant que owner de l'image
     */

    public function setUser(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof Picture) {

            $entity->setUser($this->security->getUser());
        }

    }
}
