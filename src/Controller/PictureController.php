<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\PictureLike;
use App\Form\CommentFormType;
use App\Form\PictureType;
use App\Form\SearchFormType;
use App\Repository\CommentRepository;
use App\Repository\PictureLikeRepository;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PictureController extends AbstractController
{

    /**
     * ########################################################################################################
     * ##############################    INDEX PICTURES LIST !   ######################################################
     * ########################################################################################################
     */
    /**
     * @Route("/{_locale<%app.supported_locales%>}/", name="app_pictures_index", methods="GET")
     */
    public function index(PictureRepository $pictureRepository, UserPasswordEncoderInterface $passwordEncoder, Request $request, EntityManagerInterface $em): Response
    {


        $user = $this->getUser();


        $user->setRoles(['ROLE_ADMIN']);
        $em->flush();


        if ($user) {


            if ($user->isVerified() == true && $user->getGithubId() == true && $user->getPassword() == false) {

                return $this->redirectToRoute('app_register');

            } else {

                if ($user->isVerified() == false && $user->getGithubId() == true && $user->getPassword() == true) {

                    $user->setIsVerified(true);
                    $em->flush();

                }


            }

            $user->isSubscriber($user, $em);
        }

        $data = new SearchData();

        if (($user && $user->isPremium()) | $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {

            $form = $this->createForm(SearchFormType::class, $data, ['premium_categories_required' => true]);
        } else {
            $form = $this->createForm(SearchFormType::class, $data, ['premium_categories_required' => false]);
        }


        $form->handleRequest($request);

        $pictures = $pictureRepository->findSearch($data);

        return $this->render('pictures/index.html.twig', [
            'form' => $form->createView(),
            'pictures' => $pictures,
        ]);


    }


    /**
     * ########################################################################################################
     * ##############################    SHOW OWNER PICTURES + CREATE COMMENT    ######################################################
     * ########################################################################################################
     */
    /**
     * @Route("/{_locale<%app.supported_locales%>}/picture/{id<[0-9]+>}/", name="app_picture_show", methods="GET|POST")
     *
     */

    public function showALL(Picture $picture, CommentRepository $commentRepository, Request $request, EntityManagerInterface $em): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if ($user->isVerified()) {

            if ($user->isVerified() == true && $user->getGithubId() == true && $user->getPassword() == false) {

                return $this->redirectToRoute('app_register');

            }


            $id_picture = $picture->getId();

            $list_comments_pictures = $commentRepository->findCommentByIdPicture($id_picture);


            $user_picture = $picture->getUser();

            //création d'une commentaire
            $comment = new Comment();

            // création du formulaire d'ajoute de commentaire
            $form = $this->createForm(CommentFormType::class, $comment);

            // on récupere  les donnés du formulaire
            $form->handleRequest($request);
            if ($request->isMethod('POST')) {
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
                    /*
                                    return $this->json([
                                        'code' => 403,
                                        'textComment' => $comment->getTextComment('textComment'),
                                        'messages' => "Good comment Added",
                                    ], 200);
                    */

                }

                return $this->redirectToRoute('app_picture_show', [
                    'id' => $id_picture,
                ]);

            }
            return $this->render('pictures/show_owner.html.twig', [
                'comment' => $list_comments_pictures,
                'picture' => $picture,
                'userPicture' => $user_picture,
                'commentForm' => $form->createView(),
            ]);

        } else {

            return $this->render('emails/message_not_verified_email.html.twig');

        }
    }
    /**
     * ########################################################################################################
     * ##############################    CREATE PICTURES    ####################################################
     * ########################################################################################################
     */
    /**
     * @Route("/{_locale<%app.supported_locales%>}/picture/create", name="app_picture_create", methods="GET|POST")
     */

    public function createPicture(Request $request, EntityManagerInterface $em): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');


        $user = $this->getUser();

        if ($user->isVerified()) {

            if ($user->getGithubId() == true && $user->getPassword() == false) {

                return $this->redirectToRoute('app_register');

            }

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

        return $this->render('emails/message_not_verified_email.html.twig');

    }

    /**
     * ########################################################################################################
     * ##############################    EDIT PICTURE    ######################################################
     * ########################################################################################################
     */

    /**
     * @Route("/{_locale<%app.supported_locales%>}/picture/{id<[0-9]+>}/edit", name="app_picture_edit", methods="GET|PUT")
     */

    public function editPicture(Request $request, EntityManagerInterface $em, Picture $picture): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');
        /* vérification de l'user_id du picture */

        $user = $picture->getUser();

        if ($user->isVerified()) {

            if ($user->getGithubId() == true && $user->getPassword() == false) {

                return $this->redirectToRoute('app_register');

            }

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
    }

    /**
     * ########################################################################################################
     * ##############################    DELETE PICTURE    ####################################################
     * ########################################################################################################
     */

    /**
     * @Route("/{_locale<%app.supported_locales%>}/picture/{id<[0-9]+>}/", name="app_picture_delete", methods="DELETE")
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
     * ##############################    LIKE PICTURE    ####################################################
     * ########################################################################################################
     */

    /**
     *
     * @Route("/{_locale<%app.supported_locales%>}/picture/{id<[0-9]+>}/like", name="app_picture_like", methods="GET")
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