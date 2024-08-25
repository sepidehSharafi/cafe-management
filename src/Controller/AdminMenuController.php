<?php

namespace App\Controller;

use App\Entity\MenuItem;
use App\Entity\Menu;
use App\Entity\Order;
use App\Form\MenuItemType;
use App\Form\MenuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/menu')]
class AdminMenuController extends AbstractController
{
    #[Route('/', name: 'admin_menu', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }
        $menus = $entityManager->getRepository(Menu::class)->findAll();
        $menuItems = $entityManager->getRepository(MenuItem::class)->findAll();

        return $this->render('admin/menu/index.html.twig', [
            'menus' => $menus,
            'menuItems' => $menuItems,
        ]);
    }


    #[Route('/new-menu', name: 'admin_menu_new_menu', methods: ['GET', 'POST'])]
    public function newMenu(Request $request, EntityManagerInterface $entityManager): Response
    {

        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }

        $menu = new Menu();
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $menuName = $menu->getName();
            $existingMenu = $entityManager->getRepository(Menu::class)->findOneBy(['name' => $menuName]);
            if ($existingMenu) {
                $this->addFlash('error', "A menu with the name '$menuName' already exists. Cannot add a duplicate menu.");
            } else {
                $entityManager->persist($menu);
                $entityManager->flush();
                $this->addFlash('success', "New menu '$menuName' created successfully.");
                return $this->redirectToRoute('admin_menu');
            }
        }

        return $this->render('admin/menu/new_menu.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new-item/{menuId}', name: 'admin_menu_new_item', methods: ['GET', 'POST'])]
    public function newMenuItem(Request $request, EntityManagerInterface $entityManager, int $menuId): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }
        $menu = $entityManager->getRepository(Menu::class)->find($menuId);

        if (!$menu) {
            throw $this->createNotFoundException('Menu not found.');
        }

        $menuItem = new MenuItem();
        $menuItem->setMenu($menu);

        $form = $this->createForm(MenuItemType::class, $menuItem);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $existingItem = $entityManager->getRepository(MenuItem::class)->findOneBy([
                    'name' => $menuItem->getName(),
                    'menu' => $menu
                ]);

                if ($existingItem) {
                    $this->addFlash('error', 'An item with this name already exists in this menu. Cannot add a duplicate item.');
                } else {
                    $entityManager->persist($menuItem);
                    $entityManager->flush();
                    $this->addFlash('success', 'Menu item created successfully.');
                    return $this->redirectToRoute('admin_menu');
                }
            } else {
                $this->addFlash('error', 'The form contains invalid data. Please check your input.');
            }
        }

        return $this->render('admin/menu/new_item.html.twig', [
            'form' => $form->createView(),
            'menu' => $menu,
        ]);
    }

    #[Route('/edit-menu/{id}', name: 'admin_menu_edit_menu', methods: ['GET', 'POST'])]
    public function editMenu(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }
        $menu = $entityManager->getRepository(Menu::class)->find($id);

        if (!$menu) {
            throw $this->createNotFoundException('Menu not found.');
        }

        $originalName = $menu->getName();
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($originalName !== $menu->getName()) {
                $existingMenu = $entityManager->getRepository(Menu::class)->findOneBy(['name' => $menu->getName()]);
                if ($existingMenu && $existingMenu->getId() !== $menu->getId()) {
                    $this->addFlash('error', 'A menu with this name already exists.');
                    return $this->render('admin/menu/edit_menu.html.twig', [
                        'form' => $form->createView(),
                        'menu' => $menu,
                    ]);
                }
            }
            $entityManager->flush();
            $this->addFlash('success', 'Menu updated successfully.');
            return $this->redirectToRoute('admin_menu');
        }

        return $this->render('admin/menu/edit_menu.html.twig', [
            'form' => $form->createView(),
            'menu' => $menu,
        ]);
    }

    #[Route('/edit-item/{id}', name: 'admin_menu_edit_item', methods: ['GET', 'POST'])]
    public function editMenuItem(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }
        $menuItem = $entityManager->getRepository(MenuItem::class)->find($id);

        if (!$menuItem) {
            throw $this->createNotFoundException('Menu item not found.');
        }

        $originalName = $menuItem->getName();
        $form = $this->createForm(MenuItemType::class, $menuItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($originalName !== $menuItem->getName()) {
                $existingItem = $entityManager->getRepository(MenuItem::class)->findOneBy([
                    'name' => $menuItem->getName(),
                    'menu' => $menuItem->getMenu()
                ]);
                if ($existingItem && $existingItem->getId() !== $menuItem->getId()) {
                    $this->addFlash('error', 'An item with this name already exists in this menu.');
                    return $this->render('admin/menu/edit_item.html.twig', [
                        'form' => $form->createView(),
                        'menuItem' => $menuItem,
                    ]);
                }
            }
            $entityManager->flush();
            $this->addFlash('success', 'Menu item updated successfully.');
            return $this->redirectToRoute('admin_menu');
        }

        return $this->render('admin/menu/edit_item.html.twig', [
            'form' => $form->createView(),
            'menuItem' => $menuItem,
        ]);
    }


    #[Route('/delete-menu/{id}', name: 'admin_menu_delete_menu', methods: ['POST'])]
    public function deleteMenu(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }
        $menu = $entityManager->getRepository(Menu::class)->find($id);

        if (!$menu) {
            throw $this->createNotFoundException('Menu not found.');
        }

        if ($this->isCsrfTokenValid('delete' . $menu->getId(), $request->request->get('_token'))) {
            $entityManager->remove($menu);
            $entityManager->flush();
            $this->addFlash('success', 'Menu deleted successfully.');
        }

        return $this->redirectToRoute('admin_menu');
    }

    #[Route('/delete-item/{id}', name: 'admin_menu_delete_item', methods: ['POST'])]
    public function deleteMenuItem(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }
        $menuItem = $entityManager->getRepository(MenuItem::class)->find($id);

        if (!$menuItem) {
            throw $this->createNotFoundException('Menu item not found.');
        }

        if ($this->isCsrfTokenValid('delete' . $menuItem->getId(), $request->request->get('_token'))) {
            $entityManager->remove($menuItem);
            $entityManager->flush();
            $this->addFlash('success', 'Menu item deleted successfully.');
        }

        return $this->redirectToRoute('admin_menu');
    }


    /**
     * @Route("/admin/orders", name="admin_orders")
     */
    public function adminOrders(EntityManagerInterface $entityManager): Response
    {
        $orders = $entityManager->getRepository(Order::class)->findAll();

        return $this->render('admin/orders.html.twig', [
            'orders' => $orders,
        ]);
    }


    /**
     * @Route("/admin/order/{id}", name="admin_order_details", methods={"GET"})
     */
    public function orderDetails(Order $order): Response
    {
        return $this->render('admin/order_details.html.twig', [
            'order' => $order,
        ]);
    }
}
