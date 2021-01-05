<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use App\Entity\User;
use App\Event\StripeEvent;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Charge;


class EventStripeSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            'charge.refunded' => ['onChargeRefunded'],
        ];
    }

    public function onChargeRefunded(StripeEvent $event)
    {
        /** @var Charge $charge */
        $charge = $event->getResource();

        if ($charge->refunded) {
            /** @var User $user */
            $user = $this->em->getRepository(User::class)->findPremiumByChargeId($charge->id);

            if ($user) {
                $user->setPremium(false);
                $this->em->flush();
            }
        }
    }
}
