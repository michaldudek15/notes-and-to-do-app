<?php

/**
 * Category service interface.
 */

namespace App\Service;

use App\Entity\Category;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface CategoryServiceInterface.
 */
interface CategoryServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param integer $page   Page number
     * @param User    $author Author
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author): PaginationInterface;


    /**
     * Save entity.
     *
     * @param Category $category Category entity
     */
    public function save(Category $category): void;


    /**
     * @param Category $category
     *
     * @return void
     */
    public function delete(Category $category): void;


    /**
     * Can Category be deleted?
     *
     * @param Category $category Category entity
     *
     * @return boolean Result
     */
    public function canBeDeleted(Category $category): bool;

    /**
     * @param User $user
     *
     * @return array
     */
    public function getCategoriesByUser(User $user): array;
}//end interface
