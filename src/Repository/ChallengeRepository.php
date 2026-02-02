<?php

namespace App\Repository;

use App\Entity\Challenge;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Challenge>
 */
class ChallengeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Challenge::class);
    }

    public function findWithoutResultForUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.results', 'r', 'WITH', 'r.userResult = :user')
            ->andWhere('r.id IS NULL')
            ->setParameter('user', $user)
            ->orderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findGroupedByCategory(): array
    {
        $challenges = $this->createQueryBuilder('c')
            ->leftJoin('c.category', 'cat')
            ->addSelect('cat')
            ->orderBy('cat.title', 'ASC')
            ->addOrderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->groupByCategoryTitle($challenges);
    }

    public function findWithoutResultForUserGrouped(User $user, ?string $search): array
    {
        $challenges = $this->createQueryBuilder('c')
            ->where('LOWER(c.title) LIKE LOWER(:search)')
            ->leftJoin('c.category', 'cat')
            ->addSelect('cat')
            ->leftJoin('c.results', 'r', 'WITH', 'r.userResult = :user')
            ->andWhere('r.id IS NULL')
            ->setParameter('search', "%{$search}%")
            ->setParameter('user', $user)
            ->orderBy('cat.title', 'ASC')
            ->addOrderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->groupByCategoryTitle($challenges);
    }

    public function findWithSearch(?string $search): array
    {
        $challenges = $this->createQueryBuilder('c')
            ->where('LOWER(c.title) LIKE LOWER(:search)')
            ->leftJoin('c.category', 'cat')
            ->addSelect('cat')
            ->setParameter('search', "%{$search}%")
            ->orderBy('cat.title', 'ASC')
            ->addOrderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->groupByCategoryTitle($challenges);
    }

    private function groupByCategoryTitle(array $challenges): array
    {
        $grouped = [];

        foreach ($challenges as $challenge) {
            $title = $challenge->getCategory()->getTitle();
            $grouped[$title][] = $challenge;
        }

        $groupedList = [];
        foreach ($grouped as $title => $items) {
            $groupedList[] = [
                'title' => $title,
                'items' => $items,
            ];
        }

        return $groupedList;
    }


    public function findWithFilter(User $user, string $query): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.userResult = :user')
            ->andWhere('r.challenge.name LIKE :query')
            ->setParameter('user', $user)
            ->setParameter('query', "%{$query}%")
            ->orderBy('r.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
