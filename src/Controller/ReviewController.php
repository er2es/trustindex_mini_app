<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/review', name: 'review_')]
class ReviewController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return new Response('TODO: review list');
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(): Response
    {
        return new Response('TODO: new review form');
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        return new Response('TODO: review show ' . $id);
    }
}
