<?php

namespace App\Controller;

use App\Form\PaymentFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PremiumController extends AbstractController
{

    /**
     * @Route("/premium/", name="premium_index")
     */
    public function index(): Response
    {
        return $this->render('premium/index.html.twig', [
            'payment_config' => $this->getParameter('payment'),
        ]);
    }


    /**
     * @Route("/premium/payment", name="premium_payment",  methods="GET|POST")
     */
    public function premiumPayment(Request $request): Response
    {
        $user = $this->getUser();

        if ($user->isPremium()) {
            return $this->redirectToRoute('premium_index');
        }

        $form = $this->createForm(PaymentFormType::class);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $this->get('app.client.stripe')->createPremiumCharge($this->getUser(), $form->get('token')->getData());
                    $redirect = $this->get('session')->get('premium_redirect');
                } catch (\Stripe\Error\Base $e) {
                    $this->addFlash('warning', sprintf('Unable to take payment, %s', $e instanceof \Stripe\Error\Card ? lcfirst($e->getMessage()) : 'please try again.'));
                    $redirect = $this->generateUrl('premium_payment');
                } finally {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('premium/payment.html.twig', [
            'form' => $form->createView(),
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
        ]);


    }
}