<?php
namespace App\Controller;

use App\Entity\Menu;
use App\Entity\MenuItem;
use App\Form\MenuType;
use App\Form\MenuItemType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/menu')]
class MenuController extends AbstractController
{
    #[Route('/', name: 'admin_menu_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $menus = $entityManager->getRepository(Menu::class)->findAll();

        return $this->render('admin/menu/index.html.twig', [
            'menus' => $menus,
        ]);
    }

    #[Route('/new', name: 'admin_menu_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $menu = new Menu();
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($menu);
            $entityManager->flush();

            return $this->redirectToRoute('admin_menu_index');
        }

        return $this->render('admin/menu/new.html.twig', [
            'menu' => $menu,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_menu_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Menu $menu, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_menu_index');
        }

        return $this->render('admin/menu/edit.html.twig', [
            'menu' => $menu,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_menu_delete', methods: ['POST'])]
    public function delete(Request $request, Menu $menu, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$menu->getId(), $request->request->get('_token'))) {
            $entityManager->remove($menu);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_menu_index');
    }

    #[Route('/{id}/item/new', name: 'admin_menu_item_new', methods: ['GET', 'POST'])]
    public function newItem(Request $request, Menu $menu, EntityManagerInterface $entityManager): Response
    {
        $menuItem = new MenuItem();
        $menuItem->setMenu($menu);
        $form = $this->createForm(MenuItemType::class, $menuItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($menuItem);
            $entityManager->flush();

            return $this->redirectToRoute('admin_menu_edit', ['id' => $menu->getId()]);
        }

        return $this->render('admin/menu/new_item.html.twig', [
            'menu' => $menu,
            'form' => $form->createView(),
        ]);
    }
}