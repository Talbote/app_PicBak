<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class InvoiceController extends AbstractController
{
    /**
     * @Route("/{_locale<%app.supported_locales%>}/invoice/", name="app_invoices_index", methods="GET")
     */
    public function index(InvoiceRepository $invoiceRepository, EntityManagerInterface $em): Response
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

            $email = $latest_invoice->customer_email;
            $url = $latest_invoice->invoice_pdf;


            $invoice = new Invoice();
            $invoice->setName($email);
            $invoice->setUrlPdf($url);
            $invoice->setUser($this->getUser());

            $em->persist($invoice);
            $user->setRecordInvoice(true);
            $em->flush();
        }


        $id = $user->getId();
        $invoices_user = $invoiceRepository->findByUserIdInvoice($id);
        // dd($invoices_user);

        if ($user->isPremium() == true && $user->isRecordInvoice() == true) {

            return $this->render('invoices/index.html.twig', [
                    'user' => $user,
                    'invoice' => $invoices_user
                ]
            );

        }

        if ($user->isPremium() == false && $user->isRecordInvoice() == false) {

            return $this->render('invoices/index.html.twig', [
                    'user' => $user,
                    'invoice' => $invoices_user
                ]
            );

        }

    }


    /**
     * ########################################################################################################
     * ##############################    DELETE INVOICE    ####################################################
     * ########################################################################################################
     */

    /**
     * @Route("/{_locale<%app.supported_locales%>}/invoice/{id<[0-9]+>}/delete/", name="app_invoice_delete", methods="DELETE")
     *
     */

    public function delete(Request $request, Invoice $invoice, EntityManagerInterface $em): Response
    {


        $this->denyAccessUnlessGranted('ROLE_USER');
        /* vérification de l'user_id du invoice */

        if ($this->isCsrfTokenValid('invoice_deletion_' . $invoice->getId(),
            $request->request->get('csrf_token_invoice_delete'))
        ) {

            $em->remove($invoice);
            $em->flush();

            return $this->redirectToRoute('app_invoices_index', [
                'invoice' => $invoice
            ]);
        }


        $this->addFlash('error', 'Not allowed to delete this comment');

        return $this->redirectToRoute('app_invoices_index', [
            'invoice' => $invoice
        ]);
            /* si le token est valid on applique la suppression */

    }


}
