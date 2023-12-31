<?php

namespace App\Controller\Admin;

use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\Event;
use App\Entity\Reservation;
use App\Entity\SecretSantaEvent;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class AdminController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
//        return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
         return $this->render('/admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Optatio');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),

            MenuItem::section('Entities'),
            MenuItem::linkToCrud('Users', 'fa fa-users', User::class),
            MenuItem::linkToCrud('DesireList', 'fa fa-file-text', DesireList::class),
            MenuItem::linkToCrud('Desire', 'fa fa-heart', Desire::class),
            MenuItem::linkToCrud('Reservation', 'fa fa-hand-pointer-o', Reservation::class),
            MenuItem::linkToCrud('Event', 'fa fa-hand-pointer-o', Event::class),
            MenuItem::linkToCrud('SecretSantaEvent', 'fa fa-hand-pointer-o', SecretSantaEvent::class),
        ];
    }
}
