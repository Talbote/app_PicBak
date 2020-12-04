<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Picture;
use App\Form\CommentFormType;
use App\Form\PictureType;
use App\Repository\UserRepository;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PictureController extends AbstractController
{

    /**
     * ########################################################################################################
     * ##############################    INDEX PICTURES LIST !   ######################################################
     * ########################################################################################################
     */
    /**
     * @Route("/", name="app_pictures_index", methods="GET")
     */
    public function index(PictureRepository $pictureRepository, Request $request, PaginatorInterface $paginator): Response
    {
        /* dd($pictureRepository->findAll());  ( recupere tout le tableau) */

        $data = $pictureRepository->findBy([], ['createdAt' => 'DESC']);

        $pictures = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1), 3

        );

        /* compact return un tableau pictures */

        return $this->render('pictures/index.html.twig', compact('pictures'));
    }



    /**
     * ########################################################################################################
     * ##############################    SHOW PICTURES    ######################################################
     * ########################################################################################################
     */
    /**
     * @Route("/picture/{id<[0-9]+>}", name="app_picture_show", methods="GET|POST")
     */

    public function show(Picture $picture, Request $request, EntityManagerInterface $em): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');
        /* vérification de l'user_id du picture */
        $data_picture_user = $picture->getUser();

        $comment = new Comment();

        // création du formulaire d'ajoute de commentaire
        $form = $this->createForm(CommentFormType::class, $comment);

        // on récupere  les donnés du formulaire
        $form->handleRequest($request);
        // verification du formulaire si  envoyer et donnée valides
        if($form->isSubmitted() && $form->isValid()){

            $user = $this->getUser();

            $comment->addComment($user);
            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'comment send');


        }


        if ($data_picture_user !== $this->getUser()) {
            return $this->render('pictures/show.html.twig', [
                'picture' => $picture,
                'commentForm' => $form->createView()
            ]);
        } else {
            return $this->render('pictures/show_owner.html.twig', [
                'picture' => $picture,
                'commentForm' => $form->createView()
            ]);
        }
    }
    /**
     * ########################################################################################################
     * ##############################    CREATE PICTURES    ####################################################
     * ########################################################################################################
     */
    /**
     * @Route("/picture/create", name="app_picture_create", methods="GET|POST")
     */

    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $picture = new Picture();
        $form = $this->createForm(PictureType::class, $picture);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // dd($form->getData());

            /*recupere les données dans le form*/
            $user = $this->getUser();
            $picture->setUser($user);
            $em->persist($picture);
            $em->flush();

            $this->addFlash('success', 'Picture successfully created!');

            return $this->redirectToRoute('app_pictures_index');
        }

        /*  dd($form); */
        return $this->render('pictures/create.html.twig', ['form' => $form->createView()]);

    }

    /**
     * ########################################################################################################
     * ##############################    EDIT PICTURES    ######################################################
     * ########################################################################################################
     */

    /**
     * @Route("/picture/{id<[0-9]+>}/edit", name="app_picture_edit", methods="GET|PUT")
     */

    public function edit(Request $request, EntityManagerInterface $em, Picture $picture): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');
        /* vérification de l'user_id du picture */

        $user = $picture->getUser();

        if ($user !== $this->getUser()) {

            $this->addFlash('error', 'Not allowed to do that !');

            return $this->redirectToRoute('app_pictures_index');

        } else {

            $form = $this->createForm(PictureType::class, $picture, [

                'method' => 'PUT'

            ]);


            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {


                /*recupere les données dans le form*/
                $em->flush();

                $this->addFlash('success', 'Picture successfully updated!');


                return $this->redirectToRoute('app_pictures_index');
            }

            return $this->render('pictures/edit.html.twig', [

                'picture' => $picture,
                'form' => $form->createView()

            ]);
        }
    }

    /**
     * ########################################################################################################
     * ##############################    DELETE PICTURES    ####################################################
     * ########################################################################################################
     */

    /**
     * @Route("/picture/{id<[0-9]+>}/", name="app_picture_delete", methods="DELETE")
     */

    public
    function delete(Request $request, Picture $picture, EntityManagerInterface $em): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');
        /* vérification de l'user_id du picture */
        $data_user = $picture->getUser();
        if ($data_user !== $this->getUser()) {

            $this->addFlash('error', 'Not allowed to do that !');

            return $this->redirectToRoute('app_pictures_index');

        } else {
            /* si le token est valid on applique la suppression */

            if ($this->isCsrfTokenValid('picture_deletion_' . $picture->getId(), $request->request->get('csrf_token_picture_delete'))) {

                $em->remove($picture);
                $em->flush();

                $this->addFlash('info', 'Picture successfully deleted!');
            }

            return $this->redirectToRoute('app_pictures_index');
        }
    }
}