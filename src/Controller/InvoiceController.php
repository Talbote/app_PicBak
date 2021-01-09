<?php

namespace App\Controller;

use App\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class InvoiceController extends AbstractController
{
    /**
     * @Route("/invoice", name="app_invoice", methods="GET")
     */
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();


        if ($user->isPremium() == true && $user->isRecordInvoice() == false) {

            $load_checkout_session = new \Stripe\StripeClient($this->getParameter('stripe_secret_key'));
            $chargeId = $user->getChargeId();

            $client_status = $load_checkout_session->checkout->sessions->retrieve($chargeId, []);

            $subscription = $client_status->subscription;
            $user_subscription = $load_checkout_session->subscriptions->retrieve(
                $subscription,
                []
            );

            $invoice = $user_subscription->latest_invoice;
            $latest_invoice = $load_checkout_session->invoices->retrieve(
                $invoice,
                []
            );

                $invoice = new Invoice();
                $invoice->setName();

            $user->setRecordInvoice(true);
            $em->flush();



            return $this->render('invoice/index.html.twig', [
                    'subscription' => $user_subscription,
                    'user' => $user,
                    'invoice' => $latest_invoice
                ]
            );
        } else {






            return $this->render('invoice/index.html.twig');

        }
    }
}
