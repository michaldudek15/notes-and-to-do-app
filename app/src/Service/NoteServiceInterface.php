<?php
/**
 * Note service interface.
 */

namespace App\Service;

use App\Dto\NoteListInputFiltersDto;
use App\Entity\Note;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface NoteServiceInterface.
 */
interface NoteServiceInterface
{


    /**
     * Get paginated list.
     *
     * @param integer                 $page    Page number
     * @param User                    $author  Author
     * @param NoteListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author, NoteListInputFiltersDto $filters): PaginationInterface;


    /**
     * Save entity.
     *
     * @param Note $note Note entity
     */
    public function save(Note $note): void;

    /**
     * @param Note $note
     *
     * @return void
     */
    public function delete(Note $note): void;
}//end interface
