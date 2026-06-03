<?php

/**
 *  User service tests.
 */

namespace App\Tests\Service;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use App\Service\UserServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserServiceTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?UserServiceInterface $userService;

    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->userService = $container->get(UserService::class);
    }
    public function testSaveUserToDatabase(): void
    {
        // given
        $expectedUser = new User();
        $expectedUser->setEmail('save'.uniqid().'@example.com');
        $expectedUser->setPassword('password');
        $expectedUser->setRoles([UserRole::ROLE_USER->value]);

        // when
        $this->userService->save($expectedUser);

        // then
        $expectedUserId = $expectedUser->getId();

        $resultUser = $this->entityManager->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.id = :id')
            ->setParameter(':id', $expectedUserId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedUser, $resultUser);
    }

    public function testDeleteRemovesUserFromDatabase(): void
    {
        // given
        $user = new User();
        $user->setEmail('delete'.uniqid().'@example.com');
        $user->setPassword('password');
        $user->setRoles([UserRole::ROLE_USER->value]);

        $this->userService->save($user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $id = $user->getId();

        // when
        $this->userService->delete($user);

        // then
        self::assertNull($this->entityManager->find(User::class, $id));

    }

    public function testGetPaginatedListReturnsPagination(): void
    {
        // given
        $user = new User();
        $user->setEmail('paginate'.uniqid().'@example.com');
        $user->setPassword('password');
        $user->setRoles([UserRole::ROLE_USER->value]);

        $this->userService->save($user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // when
        $pagination = $this->userService->getPaginatedList(1);

        // then
        self::assertInstanceOf(PaginationInterface::class, $pagination);
        self::assertSame(1, $pagination->getCurrentPageNumber());
        self::assertSame(10, $pagination->getItemNumberPerPage());
        self::assertGreaterThanOrEqual(1, $pagination->getTotalItemCount());

    }

    public function testSaveDelegatesToRepository(): void
    {
        // given
        $user = new User();
        $user->setEmail('delegate@example.com');
        $user->setPassword('password');
        $user->setRoles([UserRole::ROLE_USER->value]);

        // when
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
}