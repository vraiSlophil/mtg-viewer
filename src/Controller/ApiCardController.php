<?php

namespace App\Controller;

use App\Dto\CardCollectionQuery;
use App\Repository\CardRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/card', name: 'api_card_')]
#[OA\Tag(name: 'Card', description: 'Routes for all about cards')]
class ApiCardController extends AbstractController
{
    public function __construct(
        private readonly CardRepository $cardRepository,
    ) {
    }

    #[Route('', name: 'List cards', methods: ['GET'])]
    #[OA\Parameter(name: 'name', description: 'Name of the card', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'setCode', description: 'Set code of the card', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'offset', description: 'Number of items to skip', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 0, minimum: 0))]
    #[OA\Parameter(name: 'limit', description: 'Number of items to return', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 100, minimum: 1, maximum: 100))]
    #[OA\Get(description: 'Return a paginated list of cards with optional filters')]
    #[OA\Response(
        response: 200,
        description: 'Paginated list of cards',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'items', type: 'array', items: new OA\Items(type: 'object')),
                new OA\Property(property: 'offset', type: 'integer', example: 0),
                new OA\Property(property: 'limit', type: 'integer', example: 100),
                new OA\Property(property: 'total', type: 'integer', example: 30000),
                new OA\Property(property: 'hasMore', type: 'boolean', example: true),
            ],
            type: 'object'
        )
    )]
    public function cardList(Request $request): Response
    {
        $query = CardCollectionQuery::fromRequest($request);
        $cards = $this->cardRepository->searchPaginated($query);

        return $this->json($cards);
    }

    #[Route('/set-codes', name: 'Show set codes', methods: ['GET'])]
    #[OA\Get(description: 'Get all unique set codes')]
    #[OA\Response(response: 200, description: 'Show set codes')]
    public function cardShowSetCodes(): Response
    {
        $setCodes = $this->cardRepository->getSetCodes();
        return $this->json($setCodes);
    }

    #[Route('/{uuid}', name: 'Show card', methods: ['GET'])]
    #[OA\Parameter(name: 'uuid', description: 'UUID of the card', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Get(description: 'Get a card by UUID')]
    #[OA\Response(response: 200, description: 'Show card')]
    #[OA\Response(response: 404, description: 'Card not found')]
    public function cardShow(string $uuid): Response
    {
        $card = $this->cardRepository->findOneBy(['uuid' => $uuid]);
        if (!$card) {
            return $this->json(['error' => 'Card not found'], 404);
        }
        return $this->json($card);
    }
}
