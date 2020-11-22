<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Form\PictureType;
use App\Repository\PictureRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PicturesController extends AbstractController
{

    /**
     * ########################################################################################################
     * ##############################    INDEX PICTURES   ######################################################
     * ########################################################################################################
     */
    /**
     * @Route("/", name="app_pictures_index", methods="GET")
     */
    public function index(PictureRepository $pictureRepository): Response
    {
        /* dd($pictureRepository->findAll());  ( recupere tout le tableau) */

        $pictures = $pictureRepository->findBy([], ['createdAt' => 'DESC']);

        /* compact return un tableau pictures */

        return $this->render('pictures/index.html.twig', compact('pictures'));
    }
    /**
     * ########################################################################################################
     * ##############################    SHOW PICTURES    ######################################################
     * ########################################################################################################
     */
    /**
     * @Route("/picture/{id<[0-9]+>}", name="app_picture_show", methods="GET")
     */

    public function show(picture $picture): Response
    {

        return $this->render('pictures/show.html.twig', compact('picture'));
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

        $picture = new Picture();
        $form = $this->createForm(PictureType::class, $picture);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // dd($form->getData());

            /*recupere les données dans le form*/
            $odolinski = $userRepository->findOneBy(['email' => 'adalinski@hotmail.com']);
            $picture->setUser($odolinski);
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
        /* si le token est valid on applique la suppression */

        if ($this->isCsrfTokenValid('picture_deletion_' . $picture->getId(), $request->request->get('csrf_token_picture_delete'))) {

            $em->remove($picture);
            $em->flush();

            $this->addFlash('info', 'Picture successfully deleted!');
        }

        return $this->redirectToRoute('app_pictures_index');
    }
}