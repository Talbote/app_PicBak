<?php

namespace App\Controller;

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
    public function checkoutCreate(): Response
    {

           \Stripe\Stripe::setApiKey('sk_test_51I0mX6L4sACyrZxifOb3sy4ExerZ8vd22tkbEDoH0LclFv4cKIfdxEA17vmaMNMx1LX7snYZAVo3A4mDWSBgURdG0013ar2A9E');

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
                 'success_url' => $this->generateUrl('success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                 'cancel_url' => $this->generateUrl('error', [], UrlGeneratorInterface::ABSOLUTE_URL),

           ]);

           return new JsonResponse([
               'id' => $checkout_session->id,
           ]);


    }

    /**
     * @Route("/success/", name="success")
     */
    public function success(): Response
    {

        return $this->render('premium/success.html.twig');
    }

    /**
     * @Route("/error/", name="error")
     */
    public function error(): Response
    {

        return $this->render('premium/error.html.twig');
    }
}