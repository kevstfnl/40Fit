<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Challenge;
use App\Entity\Result;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard('/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{

    public function index(): Response
    {
        try {
            $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
            $url = $adminUrlGenerator
                ->setController(UserCrudController::class)
                ->generateUrl();
            return $this->redirect($url);
        } catch (\Exception $ignored) {
            return $this->redirect("app_login");
        }
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('App');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('User', 'fas fa-list', User::class);
        yield MenuItem::linkToCrud('Category', 'fas fa-list', Category::class);
        yield MenuItem::linkToCrud('Challenge', 'fas fa-list', Challenge::class);
        yield MenuItem::linkToCrud('Result', 'fas fa-list', Result::class);
    }
}
