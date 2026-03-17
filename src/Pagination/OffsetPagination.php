<?php

namespace App\Pagination;

/**
 * @template T
 */
final class OffsetPagination implements \JsonSerializable
{
    /**
     * @param list<T> $items
     */
    public function __construct(
        private readonly array $items,
        private readonly int $offset,
        private readonly int $limit,
        private readonly int $total,
    ) {
    }

    /**
     * @return array{
     *     items: list<T>,
     *     offset: int,
     *     limit: int,
     *     total: int,
     *     hasMore: bool
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'items' => $this->items,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'total' => $this->total,
            'hasMore' => $this->offset + count($this->items) < $this->total,
        ];
    }
}
