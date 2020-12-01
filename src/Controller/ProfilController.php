<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ProfilController extends AbstractController
{

    /**
     * ########################################################################################################
     * ##############################    SHOW PROFIL    ######################################################
     * ########################################################################################################
     */
    /**
     * @Route("/profil/show", name="app_profil_show", methods="GET")
     */

    public function show(): Response
    {
        $user = $this->getUser();
        $id = $user->getId();

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {


            if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
                $form = $this->createForm(userType::class, $user, ['method' => 'POST']);
            } else {
                $form = $this->createForm(MemberType::class, $user, ['method' => 'POST']);
            }

            return $this->render('profil/show.html.twig', [
                'userForm' => $form->createView(), 'id' => $id, 'user' => $user]);
        } else {
            return $this->redirectToRoute('error_typeUser'); //Page erreur si mauvais rôle
        }
    }

    /**
     * ########################################################################################################
     * ##############################    EDIT PROFIL    ######################################################
     * ########################################################################################################
     */

    /**
     * @Route("/profil/edit", name="app_profil_edit", methods="GET|PUT")
     */


    public function edit(Request $request,EntityManagerInterface $em): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');
        /* vérification de l'user_id du picture */

        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user, [

            'method' => 'PUT'

        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /*recupere les données dans le form*/
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Picture successfully updated!');


            return $this->redirectToRoute('app_profil_show');
        }

        return $this->render('profil/edit.html.twig', [
            'form' => $form->createView()

        ]);
    }
}

