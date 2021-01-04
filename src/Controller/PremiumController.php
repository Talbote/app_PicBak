<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class PremiumController extends AbstractController
{

    /**
     * @Route("/subscriber", name="app_subscriber_index", methods="GET")
     */
    public function index(): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if ($user->isPremium(true)) {

            return $this->redirectToRoute('app_subscriber_status');

        }

        return $this->render('premium/index.html.twig');
    }


    /**
     * @Route("/create-checkout-session", name="app_checkout")
     */
    public function createSubscription(EntityManagerInterface $em): Response
    {

        \Stripe\Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $user = $this->getUser();

        if ($user->isPremium(true)) {

            return $this->render('premium/status.html.twig');
        }

        if ($user->getChargeId() == null) {

            $create_checkout_session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'recurring' => [
                            'interval' => 'week',
                        ],
                        'unit_amount' => 300,
                        'product_data' => [
                            'name' => 'PicBak Plus+',
                            'images' => ["https://i.imgur.com/EHyR2nP.png"],
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => $this->generateUrl('app_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('app_error', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            $user->setChargeId($create_checkout_session->id);
            $em->flush();


            return $this->json([
                'id' => $create_checkout_session->id,
            ]);

        } else {


            $apiKey = new \Stripe\StripeClient($this->getParameter('stripe_secret_key'));
            $chargeId = $user->getChargeId();

            $load_checkout_session = $apiKey->checkout->sessions->retrieve($chargeId, [

            ]);
            return $this->json([
                'id' => $load_checkout_session->id,
            ]);
        }
    }

    /**
     * @Route("/success", name="app_success", methods="GET")
     */
    public function successSubscription(EntityManagerInterface $em): Response
    {


        $user = $this->getUser();
        $load_checkout_session = new \Stripe\StripeClient($this->getParameter('stripe_secret_key'));
        $chargeId = $user->getChargeId();

        $client_status = $load_checkout_session->checkout->sessions->retrieve($chargeId, []);

        if ($client_status->payment_status == "paid") {

            $user->setPremium(true);
            $em->flush();

            return $this->render('premium/success.html.twig');

        } else {

            return $this->redirectToRoute('app_pictures_index');
        }
    }

    /**
     * @Route("/error", name="app_error")
     */
    public function errorSubscription(): Response
    {

        return $this->render('premium/error.html.twig');
    }

    /**
     * @Route("/subscriber-status", name="app_subscriber_status", methods="GET")
     */
    public function statusSubscription(): Response
    {
        $user = $this->getUser();
        $load_checkout_session = new \Stripe\StripeClient($this->getParameter('stripe_secret_key'));
        $chargeId = $user->getChargeId();

        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($chargeId) {

            $clientChargeId = $load_checkout_session->checkout->sessions->retrieve(
                $chargeId,
                []
            );

            $subscription = $clientChargeId->subscription;

            $user_subscription = $load_checkout_session->subscriptions->retrieve(
                $subscription,
                []
            );


            return $this->render('premium/status.html.twig', [
                'subscription' => $user_subscription,
                'user' => $user,
            ]);


        } else {

            return $this->redirectToRoute('app_pictures_index');

        }

    }

    /**
     * @Route("/subscription-cancel", name="app_subscription_cancel")
     */
    public function cancelSubscription(): Response
    {

        \Stripe\Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $user = $this->getUser();

        $load_checkout_session = new \Stripe\StripeClient($this->getParameter('stripe_secret_key'));
        $chargeId = $user->getChargeId();

        if ($chargeId) {

            $clientChargeId = $load_checkout_session->checkout->sessions->retrieve(
                $chargeId,
                []
            );

            $subscription = $clientChargeId->subscription;

            \Stripe\Subscription::update(
                $subscription,
                [
                    'cancel_at_period_end' => true,
                ]
            );

            return $this->render('premium/cancel.html.twig');

        }

        return $this->redirectToRoute('app_subscriber_index');

    }

    /**
     * @Route("/subscriptions-reactivating-canceled", name="app_subscription_reactivating_canceled")
     */
    public function reactivatingSubscription(): Response
    {

        \Stripe\Stripe::setApiKey($this->getParameter('stripe_secret_key'));
        $user = $this->getUser();

        $load_checkout_session = new \Stripe\StripeClient($this->getParameter('stripe_secret_key'));
        $chargeId = $user->getChargeId();


        if ($chargeId) {
            $clientChargeId = $load_checkout_session->checkout->sessions->retrieve(
                $chargeId,
                []
            );

            $subscription_user = $clientChargeId->subscription;
            $subscription = \Stripe\Subscription::retrieve($subscription_user);


           /* $items = $subscription->items;
            $data = $items->data;
            $price = $data[0]->price;
            $id_price = $price->id; */



            \Stripe\Subscription::update($subscription_user, [
                'cancel_at_period_end' => false,
                'proration_behavior' => 'create_prorations',
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                       
                    ],
                ],
            ]);

            return $this->render('premium/reactivating.html.twig');

        }

    }

}