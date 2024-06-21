<?php
/**
 * User service.
 */

namespace App\Service;

use App\Repository\UserRepository;
use App\Entity\User;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class NoteService.
 */
class UserService implements UserServiceInterface
{
    private const PAGINATOR_ITEMS_PER_PAGE = 10;


    /**
     * @param UserRepository     $userRepository
     * @param PaginatorInterface $paginator
     */
    public function __construct(private readonly UserRepository $userRepository, private readonly PaginatorInterface $paginator)
    {
    }//end __construct()

    /**
     * @param int $page
     *
     * @return PaginationInterface
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->userRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }//end getPaginatedList()

    /**
     * Save entity.
     *
     * @param User $user User entity
     */
    public function save(User $user): void
    {
        $this->userRepository->save($user);
    }//end save()

    /**
     * Delete entity.
     *
     * @param User $user User entity
     *
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function delete(User $user): void
    {
        $this->userRepository->delete($user);
    }//end delete()

    /**
     * @param int $id
     *
     * @return User|null
     */
    public function findOneById(int $id): ?User
    {
        return $this->userRepository->findOneById($id);
    }//end findOneById()
}//end class
