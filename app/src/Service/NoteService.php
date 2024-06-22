<?php
/**
 * Note service.
 */

namespace App\Service;

use App\Dto\NoteListFiltersDto;
use App\Dto\NoteListInputFiltersDto;
use App\Entity\Note;
use App\Entity\User;
use App\Repository\NoteRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class NoteService.
 */
class NoteService implements NoteServiceInterface
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
     * @param NoteRepository           $noteRepository  Note repository
     * @param PaginatorInterface       $paginator       Paginator
     * @param CategoryServiceInterface $categoryService Category service
     * @param TagServiceInterface      $tagService      Tag service
     */
    public function __construct(private readonly NoteRepository $noteRepository, private readonly PaginatorInterface $paginator, private readonly CategoryServiceInterface $categoryService, private readonly TagServiceInterface $tagService)
    {
    }//end __construct()


    /**
     * Get paginated list.
     *
     * @param integer                 $page    Page number
     * @param User                    $author  Author
     * @param NoteListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author, NoteListInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->noteRepository->queryByAuthor($author, $filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }//end getPaginatedList()


    /**
     * Save entity.
     *
     * @param Note $note Note entity
     */
    public function save(Note $note): void
    {
        $this->noteRepository->save($note);
    }//end save()


    /**
     * Delete entity.
     *
     * @param Note $note Note entity
     *
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function delete(Note $note): void
    {
        $this->noteRepository->delete($note);
    }//end delete()


    /**
     * Prepare filters for the tasks list.
     *
     * @param NoteListInputFiltersDto $filters Raw filters from request
     *
     * @return NoteListFiltersDto Result filters
     */
    private function prepareFilters(NoteListInputFiltersDto $filters): NoteListFiltersDto
    {
        return new NoteListFiltersDto(
            null !== $filters->categoryId ? $this->categoryService->findOneById($filters->categoryId) : null,
            null !== $filters->tagId ? $this->tagService->findOneById($filters->tagId) : null
        );
    }//end prepareFilters()
}//end class
