<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/{_locale<%app.supported_locales%>}/register", name="app_register", methods="GET|PUT|POST")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator,
                             EntityManagerInterface $em)
    {
        $user = $this->getUser();

        if (!$user) {

            $app_user = new User();

            $form = $this->createForm(RegistrationFormType::class, $app_user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // encode the plain password
                $app_user->setPassword(
                    $passwordEncoder->encodePassword(
                        $app_user,
                        $form->get('plainPassword', 'nickName')->getData()

                    )
                );

                $em->persist($app_user);
                $em->flush();


                // generate a signed url and email it to the user
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $app_user,
                    (new TemplatedEmail())
                        ->from(new Address('noreply@picbak.com', 'PicBak Bot'))
                        /*  ->from(new Address(
                                  $this->getParameter('app.mail_from_name'),
                                  $this->getParameter('app.mail_from_address')
                              )
                          )
                          */
                        ->to($app_user->getEmail())
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('emails/confirmation_email.html.twig')
                );


                // do anything else you need here, like send an email

                return $guardHandler->authenticateUserAndHandleSuccess(
                    $app_user,
                    $request,
                    $authenticator,
                    'main' // firewall name in security.yaml
                );
            }

            return $this->render('registration/register.html.twig', [
                'registrationForm' => $form->createView(),
            ]);

        } else {

            if ($user->getPassword() == true && $user->getGithubId() == false) {
                $this->addFlash('error', 'Already logged in!');
                return $this->redirectToRoute('app_pictures_index');

            } else {

                $github_user = $this->getUser();

                if ($github_user->getPassword() == false && $github_user->getGithubId() == true) {

                    $form_git = $this->createForm(RegistrationFormType::class, $github_user, [
                        'method' => 'PUT'
                    ]);

                    $form_git->handleRequest($request);

                    if ($form_git->isSubmitted() && $form_git->isValid()) {
                        // encode the plain password
                        $user->setPassword(
                            $passwordEncoder->encodePassword(
                                $github_user,
                                $form_git->get('plainPassword', 'nickName')->getData()
                            )
                        );

                        $em->persist($github_user);
                        $em->flush();

                        return $guardHandler->authenticateUserAndHandleSuccess(
                            $github_user,
                            $request,
                            $authenticator,
                            'main' // firewall name in security.yaml
                        );

                    }



                    return $this->render('registration/register.html.twig', [
                        'registrationForm' => $form_git->createView(),
                    ]);
                }
            }
        }

        return $this->redirectToRoute('app_pictures_index');
    }

    /**
     * @Route("/{_locale<%app.supported_locales%>}/resend/email", name="app_resend_email")
     */
    public
    function resendUserEmail(Request $request): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $user_not_confirmed = $user;

        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user_not_confirmed,
            (new TemplatedEmail())
                ->from(new Address('noreply@picbak.com', 'PicBak Bot'))
                /*  ->from(new Address(
                          $this->getParameter('app.mail_from_name'),
                          $this->getParameter('app.mail_from_address'
                      )
                  )
                  */
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('emails/confirmation_email.html.twig')
        );


        $this->addFlash('info', 'Verification email has been sent');

        return $this->redirectToRoute('app_check_email');
    }


    /**
     * @Route("/{_locale<%app.supported_locales%>}/verify/email", name="app_verify_email")
     */
    public
    function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $exception->getReason());

            return $this->redirectToRoute('app_pictures_index');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_pictures_index');
    }
}
