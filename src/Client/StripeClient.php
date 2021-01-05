<?php

namespace AppBundle\Client;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;


class StripeClient
{

  public function isSubscriber(User $user,EntityManagerInterface $em)
  {
      $load_checkout_session = new \Stripe\StripeClient('sk_test_51I0mX6L4sACyrZxifOb3sy4ExerZ8vd22tkbEDoH0LclFv4cKIfdxEA17vmaMNMx1LX7snYZAVo3A4mDWSBgURdG0013ar2A9E');
      $chargeId = $user->getChargeId();

      if ($chargeId) {


          $client_status = $load_checkout_session->checkout->sessions->retrieve($chargeId, []);
          $subscription = $client_status->subscription;
          $user_subscription = $load_checkout_session->subscriptions->retrieve(
              $subscription,
              []
          );

          if ($user_subscription->status == "active") {

              $user->setPremium(true);
              $em->flush();
          }
          if ($user_subscription->status == "canceled") {

              $user->setChargeId(false);
              $user->setPremium(false);
              $em->flush();
          }
      }
  }
}
