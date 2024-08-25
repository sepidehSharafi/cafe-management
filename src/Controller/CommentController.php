<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;

class CommentController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/item/{itemId}/comment', name: 'add_comment', methods: ['POST'])]
    public function addComment(Request $request, EntityManagerInterface $entityManager, int $itemId): Response
    {
        $item = $entityManager->getRepository(MenuItem::class)->find($itemId);

        if (!$item) {
            throw $this->createNotFoundException('Item was not found.');
        }

        $content = $request->request->get('comment');

        if (empty($content)) {
            $this->addFlash('error', 'You cannot post an empty comment.');
            return $this->redirectToRoute('item_detail', ['id' => $itemId]);
        }

        $user = $this->security->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to post a comment.');
        }

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setItem($item);
        $comment->setUser($user);

        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Your comment was successfully submitted.');

        return $this->redirectToRoute('item_detail', ['id' => $itemId]);
    }
}