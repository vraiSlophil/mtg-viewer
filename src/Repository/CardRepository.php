<?php

namespace App\Repository;

use App\Dto\CardCollectionQuery;
use App\Entity\Card;
use App\Pagination\OffsetPagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 *
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    public function getAllUuids(): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('c.uuid')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY)
        ;
        return array_column($result, 'uuid');
    }

    /**
     * @return OffsetPagination<Card>
     */
    public function searchPaginated(CardCollectionQuery $query): OffsetPagination
    {
        $cardsQueryBuilder = $this->createFilteredQueryBuilder($query)
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->setFirstResult($query->offset)
            ->setMaxResults($query->limit);

        $items = $cardsQueryBuilder->getQuery()->getResult();

        $countQueryBuilder = $this->createFilteredQueryBuilder($query)
            ->select('COUNT(c.id)');

        $total = (int) $countQueryBuilder->getQuery()->getSingleScalarResult();

        return new OffsetPagination($items, $query->offset, $query->limit, $total);
    }

    public function getSetCodes(): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('DISTINCT c.setCode')
            ->where('c.setCode IS NOT NULL')
            ->andWhere('c.setCode != :emptySetCode')
            ->setParameter('emptySetCode', '')
            ->orderBy('c.setCode', 'ASC')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY)
        ;
        return array_column($result, 'setCode');
    }

    private function createFilteredQueryBuilder(CardCollectionQuery $query): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('c');

        if ($query->name !== null) {
            $queryBuilder->andWhere('c.name LIKE :name')
                ->setParameter('name', '%' . $query->name . '%');
        }

        if ($query->setCode !== null) {
            $queryBuilder->andWhere('c.setCode = :setCode')
                ->setParameter('setCode', $query->setCode);
        }

        return $queryBuilder;
    }
}
