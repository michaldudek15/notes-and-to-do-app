<?php

/**
 *  User service tests.
 */

namespace App\Tests\Service;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Service\UserService;
use App\Service\UserServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
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
    public function testSave(): void
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
}