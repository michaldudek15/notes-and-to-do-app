<?php

/**
 * Tag service tests.
 */

namespace App\Tests\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Service\TagService;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tag service unit tests.
 */
class TagServiceTest extends TestCase
{
    /**
     * It delegates save operation to repository.
     */
    public function testSaveDelegatesToRepository(): void
    {
        $tag = new Tag();
        $tag->setTitle('delegate');

        $tagRepository = $this->createMock(TagRepository::class);
        $tagRepository->expects(self::once())
            ->method('save')
            ->with(self::identicalTo($tag));

        $service = new TagService(
            $tagRepository,
            $this->createMock(PaginatorInterface::class),
        );

        $service->save($tag);
    }

    /**
     * It delegates delete operation to repository.
     */
    public function testDeleteDelegatesToRepository(): void
    {
        $tag = new Tag();
        $tag->setTitle('delegate');

        $tagRepository = $this->createMock(TagRepository::class);
        $tagRepository->expects(self::once())
            ->method('delete')
            ->with(self::identicalTo($tag));

        $service = new TagService(
            $tagRepository,
            $this->createMock(PaginatorInterface::class),
        );

        $service->delete($tag);
    }

    /**
     * It delegates list pagination to repository and paginator.
     */
    public function testGetPaginatedListReturnsPaginationFromPaginator(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $tagRepository = $this->createMock(TagRepository::class);
        $tagRepository->expects(self::once())
            ->method('queryAll')
            ->willReturn($queryBuilder);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator->expects(self::once())
            ->method('paginate')
            ->with(
                self::identicalTo($queryBuilder),
                1,
                10
            )
            ->willReturn($pagination);

        $service = new TagService($tagRepository, $paginator);

        self::assertSame($pagination, $service->getPaginatedList(1));
    }

    /**
     * It delegates title lookup to repository.
     */
    public function testFindOneByTitleDelegatesToRepository(): void
    {
        $tagRepository = $this->getMockBuilder(TagRepository::class)
            ->disableOriginalConstructor()
            ->addMethods(['findOneByTitle'])
            ->getMock();
        $tagRepository->expects(self::once())
            ->method('findOneByTitle')
            ->with('work')
            ->willReturn(null);

        $service = new TagService(
            $tagRepository,
            $this->createMock(PaginatorInterface::class),
        );

        self::assertNull($service->findOneByTitle('work'));
    }
}
