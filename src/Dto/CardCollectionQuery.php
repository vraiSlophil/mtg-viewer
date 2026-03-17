<?php

namespace App\Dto;

use Symfony\Component\HttpFoundation\Request;

final class CardCollectionQuery
{
    public const DEFAULT_LIMIT = 10;
    public const MAX_LIMIT = 100;

    public function __construct(
        public readonly ?string $name,
        public readonly ?string $setCode,
        public readonly int $offset = 0,
        public readonly int $limit = self::DEFAULT_LIMIT,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $name = trim($request->query->getString('name', ''));
        $setCode = trim($request->query->getString('setCode', ''));

        $offset = max(0, $request->query->getInt('offset', 0));
        $limit = $request->query->getInt('limit', self::DEFAULT_LIMIT);
        $limit = max(1, min($limit, self::MAX_LIMIT));

        return new self(
            $name !== '' ? $name : null,
            $setCode !== '' ? $setCode : null,
            $offset,
            $limit,
        );
    }
}
