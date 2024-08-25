<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
   
    #[Route('/{_locale}', name: 'app_home', requirements: ['_locale' => 'en|fa'])]
    public function index(EntityManagerInterface $entityManager, string $_locale = 'fa'): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $request->setLocale($_locale);

        $menus = $entityManager->getRepository(Menu::class)->findAll();
        
        $user = $this->getUser();
        $cartItems = [];
        
        if ($user) {
            $pendingOrder = $entityManager->getRepository(Order::class)->findOneBy([
                'user' => $user,
                'status' => 'pending'
            ]);
            
            if ($pendingOrder) {
                $orderItems = $entityManager->getRepository(OrderItem::class)->findBy(['purchase' => $pendingOrder]);
                foreach ($orderItems as $item) {
                    $cartItems[$item->getMenuItem()->getId()] = [
                        'menuItem' => $item->getMenuItem(),
                        'quantity' => $item->getQuantity()
                    ];
                }
            }
        }
        
        return $this->render('home/home.html.twig', [
            'menus' => $menus,
            'cartItems' => $cartItems,
        ]);
    }
}