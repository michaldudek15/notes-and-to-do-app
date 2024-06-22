<?php

namespace App\Repository;

use App\Dto\NoteListFiltersDto;
use App\Entity\Category;
use App\Entity\Note;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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
    public function queryAll(NoteListFiltersDto $filters): QueryBuilder
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()->select(
            'partial note.{id, createdAt, updatedAt, title, content}',
            'partial category.{id, title}',
            'partial tags.{id, title}'
        )->join('note.category', 'category')->leftJoin('note.tags', 'tags')->orderBy('note.updatedAt', 'DESC');

        return $this->applyFiltersToList($queryBuilder, $filters);
    }//end queryAll()


    private function applyFiltersToList(QueryBuilder $queryBuilder, NoteListFiltersDto $filters): QueryBuilder
    {
        if ($filters->category instanceof Category) {
            $queryBuilder->andWhere('category = :category')->setParameter('category', $filters->category);
        }

        if ($filters->tag instanceof Tag) {
            $queryBuilder->andWhere('tags IN (:tag)')->setParameter('tag', $filters->tag);
        }

        return $queryBuilder;
    }//end applyFiltersToList()


    public function save(Note $note): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($note);
        $this->_em->flush();
    }//end save()


    /**
     * Delete entity.
     *
     * @param Note $note Note entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Note $note): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($note);
        $this->_em->flush();
    }//end delete()


    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
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


    /**
     * Query notes by author.
     *
     * @param User $user User entity
     *
     * @return QueryBuilder Query builder
     */
    public function queryByAuthor(UserInterface $user, NoteListFiltersDto $filters): QueryBuilder
    {
        $queryBuilder = $this->queryAll($filters);

        $queryBuilder->andWhere('note.author = :author')->setParameter('author', $user);

        return $queryBuilder;
    }//end queryByAuthor()
}//end class
