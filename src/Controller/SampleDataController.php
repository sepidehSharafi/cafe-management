<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SampleDataController extends AbstractController
{
    #[Route('/add-sample-data', name: 'add_sample_data')]
    public function addSampleData(EntityManagerInterface $entityManager): Response
    {
        $menu1 = new Menu();
        $menu1->setName('Lunch Menu');

        $item1 = new MenuItem();
        $item1->setName('Chicken Sandwich');
        $item1->setDescription('Grilled chicken with lettuce and mayo');
        $item1->setPrice(8.99);
        $item1->setMenu($menu1);

        $item2 = new MenuItem();
        $item2->setName('Caesar Salad');
        $item2->setDescription('Fresh romaine lettuce with Caesar dressing');
        $item2->setPrice(7.99);
        $item2->setMenu($menu1);

        $menu2 = new Menu();
        $menu2->setName('Dinner Menu');

        $item3 = new MenuItem();
        $item3->setName('Steak');
        $item3->setDescription('8oz sirloin steak with mashed potatoes');
        $item3->setPrice(19.99);
        $item3->setMenu($menu2);

        $item4 = new MenuItem();
        $item4->setName('Salmon');
        $item4->setDescription('Grilled salmon with asparagus');
        $item4->setPrice(17.99);
        $item4->setMenu($menu2);

        $entityManager->persist($menu1);
        $entityManager->persist($item1);
        $entityManager->persist($item2);
        $entityManager->persist($menu2);
        $entityManager->persist($item3);
        $entityManager->persist($item4);

        $entityManager->flush();

        return new Response('Sample data added successfully. Two menus with two items each have been created.');
    }
}