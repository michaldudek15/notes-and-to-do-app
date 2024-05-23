<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Note>
 */
class NoteRepository extends ServiceEntityRepository
{
    public const PAGINATOR_ITEMS_PER_PAGE = 10;


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);

    }//end __construct()


    /**
     * Query all records.
     *
     * @return \Doctrine\ORM\QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()->select(
            'partial note.{id, createdAt, updatedAt, title, content}',
            'partial category.{id, title}'
        )->join('note.category', 'category')->orderBy('note.updatedAt', 'DESC');

    }//end queryAll()


    public function save(Note $note): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($note);
        $this->_em->flush();

    }//end save()


    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder=null): QueryBuilder
    {
        return ($queryBuilder ?? $this->createQueryBuilder('note'));

    }//end getOrCreateQueryBuilder()


    /**
     * Count notes by category.
     *
     * @param Category $category Category
     *
     * @return integer Number of notes in category
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByCategory(Category $category): int
    {
        $qb = $this->getOrCreateQueryBuilder();

        return $qb->select($qb->expr()->countDistinct('note.id'))->where('note.category = :category')->setParameter(':category', $category)->getQuery()->getSingleScalarResult();

    }//end countByCategory()


}//end class
