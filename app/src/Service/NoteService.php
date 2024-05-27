<?php
/**
 * Note service.
 */

namespace App\Service;

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
     * @param NoteRepository     $noteRepository Note repository
     * @param PaginatorInterface $paginator      Paginator
     */
    public function __construct(private readonly NoteRepository $noteRepository, private readonly PaginatorInterface $paginator)
    {

    }//end __construct()


    /**
     * Get paginated list.
     *
     * @param  integer $page   Page number
     * @param  User    $author Author
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->noteRepository->queryByAuthor($author),
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


}//end class
