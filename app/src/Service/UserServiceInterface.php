<?php
/**
 * User service interface.
 */

namespace App\Service;

use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UserServiceInterface.
 */
interface UserServiceInterface
{


    /**
     * Save entity.
     *
     * @param User $user User entity
     */
    public function save(User $user): void;


    public function delete(User $user): void;


}//end interface
