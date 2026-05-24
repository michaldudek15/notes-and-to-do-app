<?php

/**
 * Base class for HTTP integration tests.
 */

namespace App\Tests;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Note;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected UserRepository $userRepository;

    protected CategoryRepository $categoryRepository;

    protected NoteRepository $noteRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $container = static::getContainer();
        $this->userRepository = $container->get(UserRepository::class);
        $this->categoryRepository = $container->get(CategoryRepository::class);
        $this->noteRepository = $container->get(NoteRepository::class);
    }

    /**
     * @param array<int, string> $roles
     */
    protected function createUser(array $roles = [UserRole::ROLE_USER->value]): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');

        $user = new User();
        $user->setEmail('user'.uniqid().'@example.com');
        $user->setRoles($roles);
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));

        $this->userRepository->save($user);

        return $user;
    }

    protected function createCategory(User $author, string $title = 'Test category'): Category
    {
        $author = $this->userRepository->find($author->getId());
        self::assertInstanceOf(User::class, $author);

        $category = new Category();
        $category->setTitle($title);
        $category->setAuthor($author);

        $this->categoryRepository->save($category);

        return $category;
    }

    protected function createNote(User $author, Category $category, string $title = 'Test note'): Note
    {
        $author = $this->userRepository->find($author->getId());
        $category = $this->categoryRepository->find($category->getId());
        self::assertInstanceOf(User::class, $author);
        self::assertInstanceOf(Category::class, $category);

        $note = new Note();
        $note->setTitle($title);
        $note->setContent('Test content for note.');
        $note->setAuthor($author);
        $note->setCategory($category);

        $this->noteRepository->save($note);

        return $note;
    }

    protected function login(User $user): void
    {
        $user = $this->userRepository->find($user->getId());
        self::assertInstanceOf(User::class, $user);

        $this->client->loginUser($user);
    }

    /**
     * Log in as the category owner so CategoryVoter identity checks pass.
     */
    protected function loginAsCategoryOwner(Category $category): void
    {
        $category = $this->categoryRepository->find($category->getId());
        self::assertInstanceOf(Category::class, $category);
        self::assertInstanceOf(User::class, $category->getAuthor());

        $this->client->loginUser($category->getAuthor());
    }
}
