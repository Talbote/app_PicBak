<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment", name="comment")
     */
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }



    /**
     * ########################################################################################################
     * ##############################    EDIT COMMENT    ######################################################
     * ########################################################################################################
     */

    /**
     * @Route("/{_locale<%app.supported_locales%>}/comment/{id<[0-9]+>}/edit", name="app_comment_edit", methods="GET|PUT")
     */

    public function editComment(Request $request, EntityManagerInterface $em, Comment $comment): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');
        /* vérification de l'user_id du picture */

        $user = $comment->getUser();
        $id_picture = $comment->getPicture()->getId();

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


                return $this->redirectToRoute('app_picture_show', ['id'=>$id_picture]);
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
     * @Route("/{_locale<%app.supported_locales%>}/comment/{id<[0-9]+>}/delete/", name="app_comment_delete", methods="DELETE")
     *
     */

    public function delete( Request $request, Comment $comment, EntityManagerInterface $em): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');
        /* vérification de l'user_id du picture */

        $owner_comment = $comment->getUser();
        $owner_picture = $comment->getPicture()->getUser();
        $id_picture = $comment->getPicture()->getId();


        if ($owner_comment === $this->getUser() || $owner_picture === $this->getUser()) {

            if ($this->isCsrfTokenValid('comment_deletion_' . $comment->getId(),
                $request->request->get('csrf_token_comment_delete'))
            ) {


                $em->remove($comment);
                $em->flush();

                return $this->redirectToRoute('app_picture_show', ['id'=>$id_picture]);
            }

        } else {

            $this->addFlash('error', 'Not allowed to delete this comment');

            return $this->redirectToRoute('app_pictures_index');
            /* si le token est valid on applique la suppression */


        }

    }

}
