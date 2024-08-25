<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\MenuItem;
use App\Form\CheckoutType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


 /**
 * @Route("/cart", name="cart")
 */
public function showCart(): Response
{
    $cartItems = $this->getCartItems();
    $total = array_reduce($cartItems, function ($carry, $item) {
        return $carry + ($item->getQuantity() * $item->getPrice());
    }, 0);

    return $this->render('cart/index.html.twig', [
        'cartItems' => $cartItems,
        'total' => $total,
    ]);
}


#[Route('/submit-order', name: 'submit_order', methods: ['POST'])]
public function submitOrder(Request $request): JsonResponse
{
    $form = $this->createForm(CheckoutType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $cartItems = $this->getCartItems();
        $orderData = $form->getData();

        $order = new Order();
        $order->setUser($this->getUser());
        $order->setStatus('processing');
        $order->setAddress($orderData['address']);
        $order->setCardNumber($orderData['cardNumber']);
        $order->setExpirationDate($orderData['expirationDate']);
        $order->setCvv($orderData['cvv']);
        
        $order->setMenu($cartItems[0]->getMenuItem()->getMenu());

        foreach ($cartItems as $item) {
            $orderItem = new OrderItem();
            $orderItem->setMenuItem($item->getMenuItem());
            $orderItem->setQuantity($item->getQuantity());
            $orderItem->setPrice($item->getMenuItem()->getPrice());
            $order->addOrderItem($orderItem);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->clearCart();

        return $this->json([
            'success' => true,
            'message' => 'سفارش با موفقیت ثبت شد',
            'orderId' => $order->getId()
        ]);
    }

    return $this->json([
        'success' => false,
        'message' => 'اطلاعات فرم نامعتبر است'
    ], 400);
}

    private function saveOrder(array $cartItems, array $paymentInfo)
    {
        $order = new Order();
        $order->setUser($this->getUser());
        $order->setCardNumber($paymentInfo['cardNumber'] ?? null);
        $order->setExpirationDate($paymentInfo['expirationDate'] ?? null);
        $order->setCvv($paymentInfo['cvv'] ?? null);
        $order->setAddress($paymentInfo['address'] ?? null);
        $order->setStatus('pending');

        foreach ($cartItems as $item) {
            $menuItem = $this->entityManager->getRepository(MenuItem::class)->find($item['itemId']);
            if ($menuItem) {
                $orderItem = new OrderItem();
                $orderItem->setMenuItem($menuItem); 
                $orderItem->setQuantity($item['quantity']);
                $orderItem->setPrice($menuItem->getPrice());
                $order->addOrderItem($orderItem);
            }
        }

        
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }

    /**
     * @Route("/update-cart", name="update_cart", methods={"POST"})
     */
    public function updateCart(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $itemId = $data['itemId'] ?? null;
        $quantity = $data['quantity'] ?? null;

        if ($itemId && $quantity !== null) {
            try {
                $this->updateCartItem($itemId, $quantity);
                return $this->json(['success' => true, 'message' => 'Cart updated successfully.']);
            } catch (\Exception $e) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }

        return $this->json(['success' => false, 'message' => 'Invalid data provided.'], 400);
    }

    private function updateCartItem($itemId, $quantity)
    {
        $menuItem = $this->entityManager->getRepository(MenuItem::class)->find($itemId);

        if (!$menuItem) {
            throw new \Exception('Menu item not found.');
        }

        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['status' => 'pending', 'user' => $this->getUser()]);

        if (!$order) {
            $order = new Order();
            $order->setUser($this->getUser());
            $order->setStatus('pending');
            $order->setMenu($menuItem->getMenu()); 
            $this->entityManager->persist($order);
        }

        $orderItem = $this->entityManager->getRepository(OrderItem::class)->findOneBy(['menuItem' => $menuItem, 'purchase' => $order]);

        if ($orderItem) {
            $orderItem->setQuantity($quantity);
        } else {
            $orderItem = new OrderItem();
            $orderItem->setMenuItem($menuItem);
            $orderItem->setQuantity($quantity);
            $orderItem->setPrice($menuItem->getPrice());
            $orderItem->setPurchase($order);
            $this->entityManager->persist($orderItem);
        }

        $this->entityManager->flush();
    }

    private function getCartItems()
    {
        $user = $this->getUser();
        if (!$user) {
            throw new \LogicException('User not logged in.');
        }
    
        $pendingOrder = $this->entityManager->getRepository(Order::class)->findOneBy([
            'user' => $user,
            'status' => 'pending'
        ]);
    
        if (!$pendingOrder) {
            return [];
        }
    
        return $this->entityManager->getRepository(OrderItem::class)->findBy(['purchase' => $pendingOrder]);
    }


    #[Route('/checkout', name: 'checkout')] 
    public function checkout(): Response
    {
        $cartItems = $this->getCartItems();
        $total = array_reduce($cartItems, function ($carry, $item) {
            return $carry + ($item->getQuantity() * $item->getMenuItem()->getPrice());
        }, 0);
    
        $form = $this->createForm(CheckoutType::class);
    
        return $this->render('cart/checkout.html.twig', [
            'cartItems' => $cartItems,
            'total' => $total,
            'form' => $form->createView(),
        ]);
    }


 #[Route('/order-confirmation/{id}', name: 'order_confirmation')]
public function orderConfirmation(int $id): Response
{
    $order = $this->entityManager->getRepository(Order::class)->find($id);

    if (!$order) {
        throw $this->createNotFoundException('Order not found.');
    }

    return $this->render('cart/confirmation.html.twig', [
        'order' => $order,
    ]);
}

    private function clearCart()
    {
        $user = $this->getUser();
        $pendingOrder = $this->entityManager->getRepository(Order::class)->findOneBy([
            'user' => $user,
            'status' => 'pending'
        ]);

        if ($pendingOrder) {
            $this->entityManager->remove($pendingOrder);
            $this->entityManager->flush();
        }
    }
}
