<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Note;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, User::class);
        $this->entityManager = $entityManager;
    }// end __construct()

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()->select('partial user.{id, email, roles}')->orderBy('user.id', 'DESC');
    }// end queryAll()

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('user');
    }// end getOrCreateQueryBuilder()

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }// end upgradePassword()

    public function save(User $user): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($user);
        $this->_em->flush();
    }// end save()

    /**
     * Delete entity.
     *
     * @param User $user User entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(User $user): void
    {
        // usuwanie notatek danego użytkownika
        $notes = $this->_em->getRepository(Note::class)->findBy(['author' => $user]);
        foreach ($notes as $note) {
            $this->_em->remove($note);
        }

        // pobranie kategorii danego użytkownika
        $categories = $this->_em->getRepository(Category::class)->findBy(['author' => $user]);

        // usuwanie notatek powiązanych z kategoriami danego użytkownika
        foreach ($categories as $category) {
            $categoryNotes = $this->_em->getRepository(Note::class)->findBy(['category' => $category]);
            foreach ($categoryNotes as $note) {
                $this->_em->remove($note);
            }

            // uswuanie kategorii
            $this->_em->remove($category);
        }

        // usuwanie użytkownika
        $this->_em->remove($user);
        $this->_em->flush();
    }// end delete()

    // **
    // * @return User[] Returns an array of User objects
    // */
    // public function findByExampleField($value): array
    // {
    // return $this->createQueryBuilder('u')
    // ->andWhere('u.exampleField = :val')
    // ->setParameter('val', $value)
    // ->orderBy('u.id', 'ASC')
    // ->setMaxResults(10)
    // ->getQuery()
    // ->getResult()
    // ;
    // }
    // public function findOneBySomeField($value): ?User
    // {
    // return $this->createQueryBuilder('u')
    // ->andWhere('u.exampleField = :val')
    // ->setParameter('val', $value)
    // ->getQuery()
    // ->getOneOrNullResult()
    // ;
    // }
}// end class
