<?php

/**
 * Note service tests.
 */

namespace App\Tests\Service;

use App\Dto\NoteListFiltersDto;
use App\Dto\NoteListInputFiltersDto;
use App\Entity\Category;
use App\Entity\Note;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\NoteRepository;
use App\Service\CategoryService;
use App\Service\CategoryServiceInterface;
use App\Service\NoteService;
use App\Service\TagService;
use App\Service\TagServiceInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;

class NoteServiceTest extends TestCase
{
    public function testSaveDelegatesToRepository(): void
    {
        $note = new Note();

        $noteRepository = $this->createMock(NoteRepository::class);
        $noteRepository->expects(self::once())
            ->method('save')
            ->with(self::identicalTo($note));

        $service = new NoteService(
            $noteRepository,
            $this->createMock(PaginatorInterface::class),
            $this->createMock(CategoryServiceInterface::class),
            $this->createMock(TagServiceInterface::class)
        );

        $service->save($note);
    }

    public function testDeleteDelegatesToRepository(): void
    {
        $note = new Note();

        $noteRepository = $this->createMock(NoteRepository::class);
        $noteRepository->expects(self::once())
            ->method('delete')
            ->with(self::identicalTo($note));

        $service = new NoteService(
            $noteRepository,
            $this->createMock(PaginatorInterface::class),
            $this->createMock(CategoryServiceInterface::class),
            $this->createMock(TagServiceInterface::class)
        );

        $service->delete($note);
    }

    public function testGetPaginatedListWithoutFilters(): void
    {
        $author = new User();
        $filters = new NoteListInputFiltersDto();
        $query = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $noteRepository = $this->createMock(NoteRepository::class);
        $noteRepository->expects(self::once())
            ->method('queryByAuthor')
            ->with(
                self::identicalTo($author),
                self::callback(fn (NoteListFiltersDto $preparedFilters): bool => !$preparedFilters->category instanceof Category && !$preparedFilters->tag instanceof Tag)
            )
            ->willReturn($query);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator->expects(self::once())
            ->method('paginate')
            ->with(
                self::identicalTo($query),
                1,
                10
            )
            ->willReturn($pagination);

        $service = new NoteService(
            $noteRepository,
            $paginator,
            $this->createMock(CategoryServiceInterface::class),
            $this->createMock(TagServiceInterface::class)
        );

        self::assertSame($pagination, $service->getPaginatedList(1, $author, $filters));
    }

    public function testGetPaginatedListWithResolvedFilters(): void
    {
        $author = new User();
        $category = new Category();
        $tag = new Tag();
        $filters = new NoteListInputFiltersDto(11, 22);
        $query = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $categoryService = $this->getMockBuilder(CategoryService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneById'])
            ->getMock();
        $categoryService->expects(self::once())
            ->method('findOneById')
            ->with(11)
            ->willReturn($category);

        $tagService = $this->getMockBuilder(TagService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneById'])
            ->getMock();
        $tagService->expects(self::once())
            ->method('findOneById')
            ->with(22)
            ->willReturn($tag);

        $noteRepository = $this->createMock(NoteRepository::class);
        $noteRepository->expects(self::once())
            ->method('queryByAuthor')
            ->with(
                self::identicalTo($author),
                self::callback(fn (NoteListFiltersDto $preparedFilters): bool => $preparedFilters->category === $category && $preparedFilters->tag === $tag)
            )
            ->willReturn($query);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator->expects(self::once())
            ->method('paginate')
            ->with(
                self::identicalTo($query),
                2,
                10
            )
            ->willReturn($pagination);

        $service = new NoteService(
            $noteRepository,
            $paginator,
            $categoryService,
            $tagService
        );

        self::assertSame($pagination, $service->getPaginatedList(2, $author, $filters));
    }
}
