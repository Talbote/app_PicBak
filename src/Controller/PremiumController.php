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
     * @Route("/subscriber", name="app_subscriber")
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
     * @Route("/success", name="app_success")
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

            $user->setPremium(false);
            $em->flush();
            return $this->render('pictures/index.html.twig');

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
     * @Route("/subscriber_status", name="app_subscriber_status", methods="GET")
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

            return $this->redirectToRoute('app_index_picture');

        }

    }

    /**
     * @Route("/cancel-subscription", name="app_cancel_subscription")
     */
    public function cancelSubscription(): Response
    {

        \Stripe\Stripe::setApiKey('sk_test_51I0mX6L4sACyrZxifOb3sy4ExerZ8vd22tkbEDoH0LclFv4cKIfdxEA17vmaMNMx1LX7snYZAVo3A4mDWSBgURdG0013ar2A9E');

        \Stripe\Subscription::update(
            'sub_IeIZGmw0cNpyxA',
            [
                'cancel_at_period_end' => true,
            ]
        );

        return $this->render('premium/cancel.html.twig');

    }

    /**
     * @Route("/reactivating-canceled-subscriptions", name="app_reactivating_canceled_subscription")
     */
    public function reactivatingSubscription(): Response
    {

        \Stripe\Stripe::setApiKey('sk_test_51I0mX6L4sACyrZxifOb3sy4ExerZ8vd22tkbEDoH0LclFv4cKIfdxEA17vmaMNMx1LX7snYZAVo3A4mDWSBgURdG0013ar2A9E');

        $subscription = \Stripe\Subscription::retrieve('sub_IeIZGmw0cNpyxA');
        \Stripe\Subscription::update('sub_IeIZGmw0cNpyxA', [
            'cancel_at_period_end' => false,
            'proration_behavior' => 'create_prorations',
            'items' => [
                [
                    'id' => $subscription->items->data[0]->id,
                    'price' => 'price_1I2zxtL4sACyrZxiE6cTWgYA',
                ],
            ],
        ]);

        return $this->render('premium/reactivating.html.twig');
    }


}