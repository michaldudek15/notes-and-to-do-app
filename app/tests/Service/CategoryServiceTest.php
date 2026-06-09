<?php

/**
 * Category service tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\NoteRepository;
use App\Service\CategoryService;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;

class CategoryServiceTest extends TestCase
{
    public function testGetPaginatedListDelegatesToRepositoryAndPaginator(): void
    {
        $author = new User();
        $query = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::once())
            ->method('queryByAuthor')
            ->with(self::identicalTo($author))
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

        $service = new CategoryService(
            $categoryRepository,
            $this->createMock(NoteRepository::class),
            $paginator
        );

        self::assertSame($pagination, $service->getPaginatedList(2, $author));
    }

    public function testSaveDelegatesToRepository(): void
    {
        $category = new Category();

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::once())
            ->method('save')
            ->with(self::identicalTo($category));

        $service = new CategoryService(
            $categoryRepository,
            $this->createMock(NoteRepository::class),
            $this->createMock(PaginatorInterface::class)
        );

        $service->save($category);
    }

    public function testDeleteDelegatesToRepository(): void
    {
        $category = new Category();

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::once())
            ->method('delete')
            ->with(self::identicalTo($category));

        $service = new CategoryService(
            $categoryRepository,
            $this->createMock(NoteRepository::class),
            $this->createMock(PaginatorInterface::class)
        );

        $service->delete($category);
    }

    public function testCanBeDeletedReturnsTrueWhenNoNotesAssigned(): void
    {
        $category = new Category();

        $noteRepository = $this->createMock(NoteRepository::class);
        $noteRepository->expects(self::once())
            ->method('countByCategory')
            ->with(self::identicalTo($category))
            ->willReturn(0);

        $service = new CategoryService(
            $this->createMock(CategoryRepository::class),
            $noteRepository,
            $this->createMock(PaginatorInterface::class)
        );

        self::assertTrue($service->canBeDeleted($category));
    }

    public function testCanBeDeletedReturnsFalseWhenNotesExist(): void
    {
        $category = new Category();

        $noteRepository = $this->createMock(NoteRepository::class);
        $noteRepository->expects(self::once())
            ->method('countByCategory')
            ->with(self::identicalTo($category))
            ->willReturn(3);

        $service = new CategoryService(
            $this->createMock(CategoryRepository::class),
            $noteRepository,
            $this->createMock(PaginatorInterface::class)
        );

        self::assertFalse($service->canBeDeleted($category));
    }

    public function testCanBeDeletedReturnsFalseOnRepositoryException(): void
    {
        $category = new Category();

        $noteRepository = $this->createMock(NoteRepository::class);
        $noteRepository->expects(self::once())
            ->method('countByCategory')
            ->with(self::identicalTo($category))
            ->willThrowException(new NoResultException());

        $service = new CategoryService(
            $this->createMock(CategoryRepository::class),
            $noteRepository,
            $this->createMock(PaginatorInterface::class)
        );

        self::assertFalse($service->canBeDeleted($category));
    }

    public function testFindOneByIdDelegatesToRepository(): void
    {
        $expectedCategory = new Category();

        $categoryRepository = $this->getMockBuilder(CategoryRepository::class)
            ->disableOriginalConstructor()
            ->addMethods(['findOneById'])
            ->getMock();
        $categoryRepository->expects(self::once())
            ->method('findOneById')
            ->with(12)
            ->willReturn($expectedCategory);

        $service = new CategoryService(
            $categoryRepository,
            $this->createMock(NoteRepository::class),
            $this->createMock(PaginatorInterface::class)
        );

        self::assertSame($expectedCategory, $service->findOneById(12));
    }

    public function testGetCategoriesByUserDelegatesToRepository(): void
    {
        $user = new User();
        $categories = [new Category(), new Category()];

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::once())
            ->method('findBy')
            ->with(['author' => $user])
            ->willReturn($categories);

        $service = new CategoryService(
            $categoryRepository,
            $this->createMock(NoteRepository::class),
            $this->createMock(PaginatorInterface::class)
        );

        self::assertSame($categories, $service->getCategoriesByUser($user));
    }
}
