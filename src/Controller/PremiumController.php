<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Stripe\ApiOperations\All;
use Stripe\Charge;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class PremiumController extends AbstractController
{


    /**
     * @Route("/create-checkout-session", name="checkout")
     */
    public function createSubscription(EntityManagerInterface $em): Response
    {

        \Stripe\Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $user = $this->getUser();

        if ($user->isPremium(true)) {

            return $this->render('premium/status.html.twig');
        }

        if($user->getChargeId() == null ){

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
                'success_url' => $this->generateUrl('success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('error', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            $user->setChargeId($create_checkout_session->id);
            $em->flush();


            return $this->json([
                'id' => $create_checkout_session->id,
            ]);

        }else{


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
     * @Route("/success/", name="success")
     */
    public function successSubscription(EntityManagerInterface $em): Response
    {


        $user = $this->getUser();
        $load_checkout_session = new \Stripe\StripeClient($this->getParameter('stripe_secret_key'));
        $chargeId = $user->getChargeId();

        $client_status = $load_checkout_session->checkout->sessions->retrieve($chargeId, [] );

        if($client_status->payment_status == "paid"){

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
     * @Route("/status/", name="status")
     */
    public function statusSubscription(): Response
    {

        $user = $this->getUser();

        $load_checkout_session = new \Stripe\StripeClient($this->getParameter('stripe_secret_key'));

        $chargeId = $user->getChargeId();

        $client_status = $load_checkout_session->checkout->sessions->retrieve(
            $chargeId,
            []
        );

       dd($client_status);

        return $this->render('premium/status.html.twig');

    }


    /**
     * @Route("/error/", name="error")
     */
    public function errorSubscription(EntityManagerInterface $em): Response
    {

        $user = $this->getUser();
        $user->setPremium(false);
        $em->flush();


        return $this->render('premium/error.html.twig');
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