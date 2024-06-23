<?php

/**
 * Task service interface.
 */

namespace App\Service;

use App\Dto\TaskListInputFiltersDto;
use App\Entity\Task;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface TaskServiceInterface.
 */
interface TaskServiceInterface
{
    /**
     * @param int                     $page    Page number
     * @param User                    $author  User entity
     * @param TaskListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface Pagination
     */
    public function getPaginatedList(int $page, User $author, TaskListInputFiltersDto $filters): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Task $task Task entity
     */
    public function save(Task $task): void;

    /**
     * @param Task $task Task
     *
     * @return void Void
     */
    public function delete(Task $task): void;
}// end interface
