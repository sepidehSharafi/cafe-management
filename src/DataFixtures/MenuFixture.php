<?php
namespace App\DataFixtures;

use App\Entity\Menu;
use App\Entity\MenuItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MenuFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $menu1 = new Menu();
        $menu1->setName('Main Menu');

        $item1 = new MenuItem();
        $item1->setName('Burger');
        $item1->setDescription('Delicious beef burger');
        $item1->setPrice(9.99);
        $item1->setMenu($menu1);

        $item2 = new MenuItem();
        $item2->setName('Pizza');
        $item2->setDescription('Margherita pizza');
        $item2->setPrice(12.99);
        $item2->setMenu($menu1);

        $manager->persist($menu1);
        $manager->persist($item1);
        $manager->persist($item2);

        $manager->flush();
    }
}