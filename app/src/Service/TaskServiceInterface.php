<?php
/**
 * Task service interface.
 */

namespace App\Service;

use App\Dto\TaskListInputFiltersDto;
use App\Entity\Task;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface TaskServiceInterface.
 */
interface TaskServiceInterface
{


    /**
     * Get paginated list.
     *
     * @param integer $page   Page number
     * @param User    $author Author
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author, TaskListInputFiltersDto $filters): PaginationInterface;


    /**
     * Save entity.
     *
     * @param Task $task Task entity
     */
    public function save(Task $task): void;


    public function delete(Task $task): void;


}//end interface
