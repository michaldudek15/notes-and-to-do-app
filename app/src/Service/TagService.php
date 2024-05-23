<?php
/**
 * Tag service.
 */

namespace App\Service;

use App\Repository\TagRepository;
use App\Repository\NoteRepository;
use App\Entity\Tag;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class NoteService.
 */
class TagService implements TagServiceInterface
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
     * @param TagRepository      $tagRepository  Tag repository
     * @param NoteRepository     $noteRepository Note repository
     * @param PaginatorInterface $paginator      Paginator
     */
    public function __construct(private readonly TagRepository $tagRepository, private readonly NoteRepository $noteRepository, private readonly PaginatorInterface $paginator)
    {

    }//end __construct()


    /**
     * Get paginated list.
     *
     * @param integer $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->tagRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );

    }//end getPaginatedList()


    /**
     * Save entity.
     *
     * @param Tag $tag Tag entity
     */
    public function save(Tag $tag): void
    {
        $this->tagRepository->save($tag);

    }//end save()


    /**
     * Delete entity.
     *
     * @param Tag $tag Tag entity
     *
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function delete(Tag $tag): void
    {
        $this->tagRepository->delete($tag);

    }//end delete()


}//end class
