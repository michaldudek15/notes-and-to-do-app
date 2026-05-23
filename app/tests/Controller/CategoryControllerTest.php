<?php

/**
 * Category controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;
    private CategoryRepository $categoryRepository;

    /**
     * Set up test environment before each test.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->categoryRepository = static::getContainer()->get(CategoryRepository::class);
    }

    /**
     * Test index redirects guest to login page.
     */
    public function testIndexRedirectsGuestToLogin(): void
    {
        // Arrange - brak logowania (gość)

        // Act
        $this->client->request('GET', '/category');

        // Assert
        $this->assertResponseRedirects('/login');
    }

    /**
     * Test index returns 200 for logged in user.
     */
    public function testIndexReturns200ForLoggedInUser(): void
    {
        // Arrange
        $user = $this->userRepository->findOneByEmail('user0@example.com');

        // Act
        $this->client->loginUser($user);
        $this->client->request('GET', '/category');

        // Assert
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test show redirects guest to login.
     */
    public function testShowRedirectsGuestToLogin(): void
    {
        // Arrange
        $category = $this->categoryRepository->findOneBy([]);

        // Act
        $this->client->request('GET', '/category/' . $category->getId());

        // Assert
        $this->assertResponseRedirects('/login');
    }

    /**
     * Test show redirects user without VIEW permission to index.
     */
    public function testShowRedirectsUnauthorizedUserToIndex(): void
    {
        // Arrange
        $user = $this->userRepository->findOneByEmail('user0@example.com');
        $otherUser = $this->userRepository->findOneByEmail('user1@example.com');
        $category = $this->categoryRepository->findOneBy(['author' => $otherUser]);

        // Act
        $this->client->loginUser($user);
        $this->client->request('GET', '/category/' . $category->getId());

        // Assert
        $this->assertResponseRedirects('/category');
    }

    /**
     * Test show returns 200 for owner of category.
     */
    public function testShowReturns200ForOwner(): void
    {
        // Arrange
        $user = $this->userRepository->findOneByEmail('user0@example.com');
        $category = $this->categoryRepository->findOneBy(['author' => $user]);

        // Act
        $this->client->loginUser($user);
        $this->client->request('GET', '/category/' . $category->getId());

        // Assert
        $this->assertResponseIsSuccessful();
    }
}