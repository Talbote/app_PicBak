<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Invoice;
use App\Entity\Picture;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="app_admin")
     */
    public function index(): Response
    {
        // redirect to some CRUD controller
        $routeBuilder = $this->get(AdminUrlGenerator::class);

        return $this->redirect($routeBuilder->setController(PictureCrudController::class)->generateUrl());

    }


    public function configureMenuItems(): iterable
    {

        yield MenuItem::section('List');
        yield MenuItem::linkToCrud('Categories','fas fa-exchange-alt',Category::class);
        yield MenuItem::linkToCrud('Pictures','fas fa-image',Picture::class);
        yield MenuItem::linkToCrud('Users','fas fa-users',User::class);
        yield MenuItem::linkToCrud('Comments','far fa-comments',Comment::class);
        yield MenuItem::linkToCrud('Invoices','fas fa-file-invoice',Invoice::class);
        yield MenuItem::linkToExitImpersonation('Exit', 'fas fa-sign-out-alt');
    }


}