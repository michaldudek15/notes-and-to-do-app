<?php

/**
 *  User service tests.
 */

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * User service unit tests.
 */
class UserServiceTest extends TestCase
{
    /**
     * It delegates save operation to repository.
     */
    public function testSaveDelegatesToRepository(): void
    {
        // given
        $user = new User();
        $user->setEmail('delegate@example.com');

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('save')
            ->with(self::identicalTo($user));

        $service = new UserService(
            $userRepository,
            $this->createMock(PaginatorInterface::class),
        );

        $service->save($user);
    }

    /**
     * It delegates delete operation to repository.
     */
    public function testDeleteDelegatesToRepository(): void
    {
        $user = new User();
        $user->setEmail('delete@example.com');

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('delete')
            ->with(self::identicalTo($user));

        $service = new UserService(
            $userRepository,
            $this->createMock(PaginatorInterface::class),
        );

        $service->delete($user);
    }

    /**
     * It delegates paginated listing to repository and paginator.
     */
    public function testGetPaginatedListReturnsPaginationFromPaginator(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
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

        $service = new UserService($userRepository, $paginator);

        self::assertSame($pagination, $service->getPaginatedList(1));
    }
}
