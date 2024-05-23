<?php
/**
 * Note service interface.
 */

namespace App\Service;

use App\Entity\Note;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface NoteServiceInterface.
 */
interface NoteServiceInterface
{


    /**
     * Get paginated list.
     *
     * @param integer $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;


    /**
     * Save entity.
     *
     * @param Note $note Note entity
     */
    public function save(Note $note): void;


    public function delete(Note $note): void;


}//end interface
