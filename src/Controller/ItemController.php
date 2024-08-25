<?php

namespace App\Controller;

use App\Entity\MenuItem;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ItemController extends AbstractController
{
    #[Route('/item/{id}', name: 'item_detail')]
    public function detail(int $id, EntityManagerInterface $entityManager, CommentRepository $commentRepository): Response
    {
        $item = $entityManager->getRepository(MenuItem::class)->find($id);

        if (!$item) {
            throw $this->createNotFoundException('Item not found');
        }

        $comments = $commentRepository->findByItem($item);

        return $this->render('item/detail.html.twig', [
            'item' => $item,
            'comments' => $comments,
        ]);
    }
}