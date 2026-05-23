<?php

/**
 * Category controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    public const string TEST_ROUTE = '/category';

    private KernelBrowser $httpClient;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private CategoryRepository $categoryRepository;

    /**
     * Set up test environment before each test.
     */
    protected function setUp(): void
    {
        $this->httpClient = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->categoryRepository = static::getContainer()->get(CategoryRepository::class);
    }

    /**
     * @return void
     * guest user can't access category list and should be redirected to login page
     */
    public function testIndexGuest(): void
    {
        $this->httpClient->request('GET', self::TEST_ROUTE);

        $this->assertEquals(302, $this->httpClient->getResponse()->getStatusCode());
    }


    public function testIndexUser(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $this->httpClient->loginUser($user);
        $this->httpClient->request('GET', self::TEST_ROUTE);

        $this->assertEquals(200, $this->httpClient->getResponse()->getStatusCode());
    }

    /**
     * Create user helper.
     */
    private function createUser(array $roles): User
    {
        $passwordHasher = static::getContainer()
            ->get('security.password_hasher');

        $user = new User();
        $user->setEmail('user'.uniqid().'@example.com');
        $user->setRoles($roles);
        //$user->setBlocked(false);

        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'password'
            )
        );

        $userRepository = static::getContainer()
            ->get(UserRepository::class);

        $userRepository->save($user);

        return $user;
    }

}