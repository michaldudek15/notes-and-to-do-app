<?php

/**
 * Task service.
 */

namespace App\Service;

use App\Dto\TaskListFiltersDto;
use App\Dto\TaskListInputFiltersDto;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class TaskService.
 */
class TaskService implements TaskServiceInterface
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;


    /**
     * Constructor.
     *
     * @param TaskRepository           $taskRepository  Task repository
     * @param PaginatorInterface       $paginator       Paginator
     * @param CategoryServiceInterface $categoryService Category service
     * @param TagServiceInterface      $tagService      Tag service
     */
    public function __construct(private readonly TaskRepository $taskRepository, private readonly PaginatorInterface $paginator, private readonly CategoryServiceInterface $categoryService, private readonly TagServiceInterface $tagService)
    {
    }//end __construct()


    /**
     * Get paginated list.
     *
     * @param integer                 $page    Page number
     * @param User                    $author  Author
     * @param TaskListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author, TaskListInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->taskRepository->queryByAuthor($author, $filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }//end getPaginatedList()


    /**
     * Save entity.
     *
     * @param Task $task Task entity
     */
    public function save(Task $task): void
    {
        $this->taskRepository->save($task);
    }//end save()


    /**
     * Delete entity.
     *
     * @param Task $task Task entity
     *
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function delete(Task $task): void
    {
        $this->taskRepository->delete($task);
    }//end delete()


    /**
     * Prepare filters for the tasks list.
     *
     * @param TaskListInputFiltersDto $filters Raw filters from request
     *
     * @return TaskListFiltersDto Result filters
     */
    private function prepareFilters(TaskListInputFiltersDto $filters): TaskListFiltersDto
    {
        return new TaskListFiltersDto(
            null !== $filters->categoryId ? $this->categoryService->findOneById($filters->categoryId) : null,
            null !== $filters->tagId ? $this->tagService->findOneById($filters->tagId) : null
        );
    }//end prepareFilters()
}//end class
