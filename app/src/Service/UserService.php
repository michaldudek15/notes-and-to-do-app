<?php
/**
 * User service.
 */

namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\NoteRepository;
use App\Entity\User;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class NoteService.
 */
class UserService implements UserServiceInterface
{


    /**
     * Constructor.
     *
     * @param UserRepository $userRepository User repository
     */
    public function __construct(private readonly UserRepository $userRepository)
    {

    }//end __construct()


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


}//end class
