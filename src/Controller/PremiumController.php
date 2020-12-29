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


        $user = $this->getUser();

        if ($user->isPremium(true)) {
            return $this->redirectToRoute('app_pictures_index');
        }


        \Stripe\Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $success_url = 'success_url';
        $cancel_url = 'cancel_url';

        $checkout_session = \Stripe\Checkout\Session::create([
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
            $success_url => $this->generateUrl('success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $cancel_url => $this->generateUrl('error', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        if ($success_url) {
            $user->setPremium(true);
            $em->flush();

        }

        return new JsonResponse([
            'id' => $checkout_session->id,
        ]);


    }

    /**
     * @Route("/success/", name="success")
     */
    public function successSubscription(): Response
    {

        $user = $this->getUser();

        if ($user->isPremium(false)) {

            return $this->redirectToRoute('app_pictures_index');

        }

        $premium = $user->getPremium();

        return $this->redirectToRoute('success', [
            'premium' => $premium,
        ]);

    }

    /**
     * @Route("/error/", name="error")
     */
    public function errorSubscription(): Response
    {

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