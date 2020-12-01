<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\Repository\RepositoryFactory;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * @Route("/reset-password")
 */
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    protected $userRepository;
    private $resetPasswordHelper;

    public function __construct(UserRepository $userRepository, ResetPasswordHelperInterface $resetPasswordHelper)
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->userRepository = $userRepository;
    }

    /**
     * Display & process form to request a password reset.
     *
     * @Route("", name="app_forgot_password_request")
     */
    public function request(Request $request, MailerInterface $mailer): Response
    {

        if ($this->getUser()) {
            $this->addFlash('error', 'Already logged in!');
            return $this->redirectToRoute('app_pictures_index');

        }

        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(

            /* on recupere les données du formulaire*/

                $form->get('email')->getData(),
                $mailer
            );
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     *
     * @Route("/check-email", name="app_check_email")
     */
    public function checkEmail(): Response
    {
        // est ce que cette utilisateur est autorise a acceder à cette page
        // si on ne passe  pas par la page de reinitialisation on pourrat acceder à la vue ci dessous
        //
        // -> reset_password/check_email.html.twig
        if (!$this->canCheckEmail()) {
            return $this->redirectToRoute('app_forgot_password_request');
        }

        // on poura rendre se template en lui passant un token lifetimme ( la durée de vie du token en seconde )
        return $this->render('reset_password/check_email.html.twig', [
            'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     *
     * @Route("/reset/{token}", name="app_reset_password")
     */
    public function reset(Request $request, UserPasswordEncoderInterface $passwordEncoder, string $token = null): Response
    {
        //  si on a un token
        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.`
            // on le sauvegarde en session
            $this->storeTokenInSession($token);

            //  et on faut une redirection de l'utilisateur vers la page  app_reset_password
            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }


        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                'There was a problem validating your reset request - %s',
                $e->getReason()
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // The token is valid; allow the user to change their password.
        // si le token est valide  on autorise l'utilisateur a changer son password
        // un creer un formulaire  ChangePasswordFormType
        $form = $this->createForm(ChangePasswordFormType::class);
        // on injecte le formulaire
        $form->handleRequest($request);

        // on regarde si le formulaire est valid et accepter
        if ($form->isSubmitted() && $form->isValid()) {
            // un token doit etre utilisé une seule fois donc le supprime
            $this->resetPasswordHelper->removeResetRequest($token);

            // on prend le nouveau MDP et on le HASH avec l'encoder qui à été définit pour App/Entity/User.php
            $encodedPassword = $passwordEncoder->encodePassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            // on enregistre le code HASHER EN BDD
            $user->setPassword($encodedPassword);
            $this->getDoctrine()->getManager()->flush();

            // La session est nettoyer apres que le password à été changé
            $this->cleanSessionAfterReset();

            return $this->redirectToRoute('app_pictures_index');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    // on recupere les 2 informations $emailFormData et $mailer
    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
    {
        // via doctrine , on récupere le UserRepository associé à l'entité USER
        // et on recupere une adresse de type 'findOneByEmail' email qui à été entré  par l'utilisateur

        $user = $this->userRepository->findOneByEmail($emailFormData);

        // Permet de marquer que l'utilisateur est autorisé à acceder à la page app_check_email page
        $this->setCanCheckEmailInSession();

        // si on a pas d'utilisateur redirection route -> app_check_email
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        // si on a un utilisateur , on va essayer de generer un token de reinitialisation
        // pour cette utilisateur via une methode venant de resetPasswordHelper et on va les sauvegarder grace a cette methode
        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {

            // Si on veut dire à l'utilisateur pourquoi l'email n'a pas été renvoyé ,
            // on decommente les lignes en dessous et on change la redirection  vers 'app_forgot_password_request'.
            // Attention cela peut reveler si l'utilisateur est enregistre  ou pas.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     'There was a problem handling your password reset request - %s',
            //     $e->getReason()
            // ));

            return $this->redirectToRoute('app_check_email');
        }

        // on envoit le token par email en utilisant notre amis TemplatedEmail()
        $email = (new TemplatedEmail())
            ->from(new Address('Hello@PicBak.com', 'PicBak'))
            // on envoit l'email à l'utilisateur qui est associé à l'email qui a été entré
            ->to($user->getEmail())
            // le sujet
            ->subject('Your password reset request')
            // le template qu'on utilise
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
            ]);
        // on envois l'email à l'utilisateur
        $mailer->send($email);

        // redirige utilisateur vers la page app_check_email
        return $this->redirectToRoute('app_check_email');
    }
}
