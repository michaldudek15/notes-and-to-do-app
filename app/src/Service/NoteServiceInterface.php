<?php
/**
 * Note service interface.
 */

namespace App\Service;

use App\Dto\NoteListInputFiltersDto;
use App\Entity\Note;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface NoteServiceInterface.
 */
interface NoteServiceInterface
{


    /**
     * Get paginated list.
     *
     * @param integer $page   Page number
     * @param User    $author Author
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


    public function delete(Note $note): void;


}//end interface
