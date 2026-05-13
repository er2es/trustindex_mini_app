<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\ReviewReaction;
use App\Form\Dto\ReviewFormDto;
use App\Repository\ReviewRepository;
use App\Service\ReviewReactionService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/reviews', name: 'api_review_')]
#[OA\Tag(name: 'Reviews')]
class ReviewApiController extends AbstractController
{
    public function __construct(
        private readonly ReviewRepository $reviewRepository,
        private readonly ReviewReactionService $reactionService,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(summary: 'Vélemények listája')]
    #[OA\Parameter(name: 'q', in: 'query', description: 'Keresési kifejezés', required: false)]
    #[OA\Parameter(name: 'includeText', in: 'query', description: 'Keresés a vélemény szövegében is', required: false)]
    #[OA\Response(response: 200, description: 'Sikeres lekérés')]
    public function list(Request $request): JsonResponse
    {
        $query = trim($request->query->getString('q'));
        $includeText = $request->query->getBoolean('includeText', false);

        $reviews = '' !== $query
            ? $this->reviewRepository->search($query, $includeText)
            : $this->reviewRepository->findAllOrderedByDate();

        return $this->json(array_map(static fn ($r) => [
            'id' => $r->getId(),
            'companyName' => $r->getCompanyName(),
            'rating' => $r->getRating(),
            'reviewText' => $r->getReviewText(),
            'authorEmail' => $r->getAuthorEmail(),
            'likes' => $r->getLikeCount(),
            'dislikes' => $r->getDislikeCount(),
            'createdAt' => $r->getCreatedAt()->format('Y-m-d'),
        ], $reviews));
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[OA\Get(summary: 'Egy vélemény részletei')]
    #[OA\Response(response: 200, description: 'Sikeres lekérés')]
    #[OA\Response(response: 404, description: 'Nem található')]
    public function show(int $id): JsonResponse
    {
        $review = $this->reviewRepository->find($id);
        if (null === $review) {
            return $this->json(['error' => 'Nem található'], 404);
        }

        return $this->json([
            'id' => $review->getId(),
            'companyName' => $review->getCompanyName(),
            'rating' => $review->getRating(),
            'reviewText' => $review->getReviewText(),
            'authorEmail' => $review->getAuthorEmail(),
            'likes' => $review->getLikeCount(),
            'dislikes' => $review->getDislikeCount(),
            'createdAt' => $review->getCreatedAt()->format('Y-m-d'),
        ]);
    }

    #[Route('/{id}/react', name: 'react', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[OA\Post(summary: 'Like / dislike egy véleményre')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'type', type: 'string', enum: ['like', 'dislike']),
    ]))]
    #[OA\Response(response: 200, description: 'Frissített számok')]
    public function react(int $id, Request $request): JsonResponse
    {
        $review = $this->reviewRepository->find($id);
        if (null === $review) {
            return $this->json(['error' => 'Nem található'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $type = $data['type'] ?? null;

        if (!\in_array($type, [ReviewReaction::TYPE_LIKE, ReviewReaction::TYPE_DISLIKE], true)) {
            return $this->json(['error' => 'Érvénytelen típus (like vagy dislike)'], 400);
        }

        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        return $this->json(
            $this->reactionService->toggle($review, $type, $session->getId())
        );
    }

    #[Route('/validate', name: 'validate', methods: ['POST'])]
    #[OA\Post(summary: 'Egyedi mező inline validáció')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'field', type: 'string'),
        new OA\Property(property: 'value', type: 'string'),
    ]))]
    #[OA\Response(response: 200, description: 'Validáció eredménye')]
    public function validate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $field = $data['field'] ?? null;
        $value = $data['value'] ?? '';

        $allowed = ['companyName', 'reviewText', 'authorEmail', 'rating'];
        if (!\in_array($field, $allowed, true)) {
            return $this->json(['valid' => true, 'errors' => []]);
        }

        $dto = new ReviewFormDto();
        $dto->$field = $value;

        $violations = $this->validator->validateProperty($dto, $field);

        if (0 === \count($violations)) {
            return $this->json(['valid' => true, 'errors' => []]);
        }

        return $this->json([
            'valid' => false,
            'errors' => array_map(static fn ($v) => $v->getMessage(), iterator_to_array($violations)),
        ]);
    }
}
