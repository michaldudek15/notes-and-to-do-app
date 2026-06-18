<?php

/**
 * Tag service tests.
 */

namespace App\Tests\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Service\TagService;
use App\Service\TagServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tag service integration tests.
 */
class TagServiceTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;

    private ?TagServiceInterface $tagService;

    /**
     * Boots kernel and fetches service dependencies.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->tagService = $container->get(TagService::class);
    }

    /**
     * It persists a tag in the database.
     */
    public function testSaveTagToDatabase(): void
    {
        $expectedTag = new Tag();
        $expectedTag->setTitle('save' . uniqid());

        $this->tagService->save($expectedTag);

        $expectedTagId = $expectedTag->getId();

        $resultTag = $this->entityManager->createQueryBuilder()
            ->select('tag')
            ->from(Tag::class, 'tag')
            ->where('tag.id = :id')
            ->setParameter(':id', $expectedTagId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        self::assertEquals($expectedTag, $resultTag);
    }

    /**
     * It removes a saved tag from the database.
     */
    public function testDeleteRemovesTagFromDatabase(): void
    {
        $tag = new Tag();
        $tag->setTitle('delete' . uniqid());

        $this->tagService->save($tag);

        $id = $tag->getId();

        $this->tagService->delete($tag);

        self::assertNull($this->entityManager->find(Tag::class, $id));
    }

    /**
     * It returns paginated tag list.
     */
    public function testGetPaginatedListReturnsPagination(): void
    {
        $tag = new Tag();
        $tag->setTitle('paginate' . uniqid());

        $this->tagService->save($tag);

        $pagination = $this->tagService->getPaginatedList(1);

        self::assertInstanceOf(PaginationInterface::class, $pagination);
        self::assertSame(1, $pagination->getCurrentPageNumber());
        self::assertSame(10, $pagination->getItemNumberPerPage());
        self::assertGreaterThanOrEqual(1, $pagination->getTotalItemCount());
    }

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
