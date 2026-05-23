<?php

/**
 * Category controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    public const TEST_ROUTE = '/category';

    private KernelBrowser $httpClient;

    private ?EntityManagerInterface $entityManager;

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
     * @return void
     * guest user can't access category list and should be redirected to login page
     */
    public function testIndexGuest(): void
    {
        $this->client->request('GET', self::TEST_ROUTE);

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }


}