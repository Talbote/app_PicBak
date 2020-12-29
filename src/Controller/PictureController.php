<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Picture;
use App\Entity\PictureLike;
use App\Form\CommentFormType;
use App\Form\PictureType;
use App\Repository\CommentRepository;
use App\Repository\PictureLikeRepository;
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

        $data_pictures = $pictureRepository->findBy([], ['createdAt' => 'DESC']);


        $pictures = $paginator->paginate(
            $data_pictures,
            $request->query->getInt('page', 1), 3

        );

        /* compact return un tableau pictures */

        return $this->render('pictures/index.html.twig', compact('pictures'));
    }



    /**
     * ########################################################################################################
     * ##############################    SHOW PICTURES + CREATE COMMENT    ######################################################
     * ########################################################################################################
     */
    /**
     * @Route("/picture/{id<[0-9]+>}/", name="app_picture_show", methods="GET|POST")
     *
     */

    public function showALL(Picture $picture, Request $request, EntityManagerInterface $em, CommentRepository $commentRepository): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        //création d'une commentaire
        $comment = new Comment();

        // création du formulaire d'ajoute de commentaire
        $form = $this->createForm(CommentFormType::class, $comment);

        // on récupere  les donnés du formulaire
        $form->handleRequest($request);
        // verification du formulaire si  envoyer et donnée valides
        if ($form->isSubmitted() && $form->isValid()) {

            // recupération de l'utilisateur connecté
            $user = $this->getUser();

            //liaison de l'id commentaire à l'id user connecté et l'id_picture
            $comment->setUser($user);
            $comment->setPicture($picture);




            // sauvegarde des données
            $em->persist($comment);
            $em->flush();

            // on retourne  les données du commentaire en JSON
            return $this->json([
                'code' => 403,
                'textComment' => $comment->getTextComment('textComment'),
                'messages' => "Good comment Added",
                'comments' => $commentRepository->count(['picture' => $picture])
            ], 200);

        }




        $comments = $picture->getComments();


        return $this->render('pictures/show_owner.html.twig', [
            'comment' => $comments,
            'picture' => $picture,
            'commentForm' => $form->createView(),
        ]);
    }

    /**
     * ########################################################################################################
     * ##############################    CREATE PICTURES    ####################################################
     * ########################################################################################################
     */
    /**
     * @Route("/picture/create", name="app_picture_create", methods="GET|POST")
     */

    public function createPicture(Request $request, EntityManagerInterface $em): Response
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
     * ##############################    EDIT PICTURE    ######################################################
     * ########################################################################################################
     */

    /**
     * @Route("/picture/{id<[0-9]+>}/edit", name="app_picture_edit", methods="GET|PUT")
     */

    public function editPicture(Request $request, EntityManagerInterface $em, Picture $picture): Response
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
     * ##############################    DELETE PICTURE    ####################################################
     * ########################################################################################################
     */

    /**
     * @Route("/picture/{id<[0-9]+>}/", name="app_picture_delete", methods="DELETE")
     *
     */

    public
    function deletePicture(Request $request, Picture $picture, EntityManagerInterface $em): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');
        /* vérification de l'user_id du picture */
        $data_user = $picture->getUser();

        if ($data_user !== $this->getUser()) {

            $this->addFlash('error', 'Not allowed to do that !');

            return $this->redirectToRoute('app_pictures_index');


        } else {
            /* si le token est valid on applique la suppression */

            if ($this->isCsrfTokenValid('picture_deletion_' . $picture->getId(),
                $request->request->get('csrf_token_picture_delete'))
            ) {

                $em->remove($picture);
                $em->flush();

                $this->addFlash('info', 'picture successfully deleted!');


            }
            return $this->redirectToRoute('app_pictures_index');
        }
    }


    /**
     * ########################################################################################################
     * ##############################    EDIT COMMENT    ######################################################
     * ########################################################################################################
     */

    /**
     * @Route("/picture/{id<[0-9]+>}/comment/edit", name="app_comment_edit", methods="GET|PUT")
     */

    public function editComment(Request $request, EntityManagerInterface $em, Comment $comment): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');
        /* vérification de l'user_id du picture */

        $user = $comment->getUser();

        if ($user !== $this->getUser()) {

            $this->addFlash('error', 'Not allowed to do that !');

            return $this->redirectToRoute('app_pictures_index');

        } else {

            $form = $this->createForm(CommentFormType::class, $comment, [

                'method' => 'PUT'

            ]);


            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {


                /*recupere les données dans le form*/
                $em->flush();

                $this->addFlash('success', 'Comment successfully updated!');


                return $this->redirectToRoute('app_pictures_index');
            }

            return $this->render('comment/edit.html.twig', [

                'picture' => $comment,
                'form' => $form->createView()

            ]);
        }
    }

    /**
     * ########################################################################################################
     * ##############################    DELETE COMMENT    ####################################################
     * ########################################################################################################
     */

    /**
     * @Route("/picture/{id<[0-9]+>}/comment/delete/", name="app_comment_delete", methods="DELETE")
     *
     */

    public function delete(Request $request,Comment $comment, EntityManagerInterface $em): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');
        /* vérification de l'user_id du picture */

        $owner_comment = $comment->getUser();
        $owner_picture = $comment->getPicture()->getUser();


        if ($owner_comment === $this->getUser() || $owner_picture === $this->getUser() ) {

            if ($this->isCsrfTokenValid('comment_deletion_' . $comment->getId(),
                $request->request->get('csrf_token_comment_delete'))
            ) {

                $em->remove($comment);
                $em->flush();

                $this->addFlash('info', 'Comment successfully deleted!');
            }

        } else {

            $this->addFlash('error', 'Not allowed to do that !');

            return $this->redirectToRoute('app_pictures_index');
            /* si le token est valid on applique la suppression */


        }

        return $this->redirectToRoute('app_pictures_index');
    }




    /**
     * ########################################################################################################
     * ##############################    LIKE PICTURE    ####################################################
     * ########################################################################################################
     */

    /**
     *
     * @Route("/picture/{id<[0-9]+>}/like", name="app_picture_like", methods="GET")
     * @
     */

    public function like(Picture $picture, EntityManagerInterface $em, PictureLikeRepository $likeRepo): Response
    {

        //récuperer l'utilisateur courant
        $user = $this->getUser();

        if (!$user) {

            return $this->json([
                'code' => 403,
                'message' => "Unauhorized"
            ], 403);
        }

// verification si un "utilisateur connecté" à déja liké une picture
        if ($picture->isLikedByUser($user)) {

            //retrouve le like de l'utilisateur connecté

            $like = $likeRepo->findOneBy([
                'picture' => $picture,
                'user' => $user
            ]);

            //supprime ce like
            $em->remove($like);
            $em->flush();

            // on retourne les informations en Json
            return $this->json([
                'code' => 200,
                'picture' => $picture->getId(),
                'message' => 'Like removed',
                'likes' => $likeRepo->count(['picture' => $picture])
            ], 200);

        }

        // sinon on crée un nouveau like
        $like = new PictureLike();
        // on définit la Picture que l'utilisateur a aimé
        $like->setPicture($picture);
        // on définit l'utilisateur qui a aimé la Picture
        $like->setUser($user);

        $em->persist($like);
        $em->flush($like);

        return $this->json([
            'code' => 403,
            'picture' => $picture->getId(),
            'message' => "Good Like Added",
            'likes' => $likeRepo->count(['picture' => $picture])
        ], 200);
    }




}