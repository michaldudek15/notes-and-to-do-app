<?php

/**
 * Base class for HTTP integration tests.
 */

namespace App\Tests;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Note;
use App\Entity\Tag;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\NoteRepository;
use App\Repository\TagRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Shared helpers for web integration tests.
 */
abstract class AbstractWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected UserRepository $userRepository;

    protected CategoryRepository $categoryRepository;

    protected NoteRepository $noteRepository;

    protected TagRepository $tagRepository;

    protected TaskRepository $taskRepository;

    /**
     * Boots the kernel and fetches repositories.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $container = static::getContainer();
        $this->userRepository = $container->get(UserRepository::class);
        $this->categoryRepository = $container->get(CategoryRepository::class);
        $this->noteRepository = $container->get(NoteRepository::class);
        $this->tagRepository = $container->get(TagRepository::class);
        $this->taskRepository = $container->get(TaskRepository::class);
    }

    /**
     * Creates and persists a user.
     *
     * @param array<int, string> $roles
     *
     * @return User Persisted user
     */
    protected function createUser(array $roles = [UserRole::ROLE_USER->value]): User
    {
        $passwordHasher = static::getContainer()->get(\Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface::class);
        $user = new User();
        $user->setEmail('user'.uniqid().'@example.com');
        $user->setRoles($roles);
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));

        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Creates and persists a category.
     *
     * @param User   $author Category author
     * @param string $title  Category title
     *
     * @return Category Persisted category
     */
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

    /**
     * Creates and persists a note.
     *
     * @param User     $author   Note author
     * @param Category $category Note category
     * @param string   $title    Note title
     *
     * @return Note Persisted note
     */
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

    /**
     * Creates and persists a tag.
     *
     * @param string $title Tag title
     *
     * @return Tag Persisted tag
     */
    protected function createTag(string $title = 'Test tag'): Tag
    {
        $tag = new Tag();
        $tag->setTitle($title);

        $this->tagRepository->save($tag);

        return $tag;
    }

    /**
     * Creates and persists a task.
     *
     * @param User     $author   Task author
     * @param Category $category Task category
     * @param string   $title    Task title
     * @param bool     $status   Task status
     *
     * @return Task Persisted task
     */
    protected function createTask(User $author, Category $category, string $title = 'Test task', bool $status = false): Task
    {
        $author = $this->userRepository->find($author->getId());
        $category = $this->categoryRepository->find($category->getId());
        self::assertInstanceOf(User::class, $author);
        self::assertInstanceOf(Category::class, $category);

        $task = new Task();
        $task->setTitle($title);
        $task->setStatus($status);
        $task->setAuthor($author);
        $task->setCategory($category);

        $this->taskRepository->save($task);

        return $task;
    }

    /**
     * Logs in provided user in test client.
     *
     * @param User $user User to authenticate
     */
    protected function login(User $user): void
    {
        $user = $this->userRepository->find($user->getId());
        self::assertInstanceOf(User::class, $user);

        $this->client->loginUser($user);
    }

    /**
     * Log in as the category owner so CategoryVoter identity checks pass.
     *
     * @param Category $category Category to resolve owner from
     */
    protected function loginAsCategoryOwner(Category $category): void
    {
        $category = $this->categoryRepository->find($category->getId());
        self::assertInstanceOf(Category::class, $category);
        self::assertInstanceOf(User::class, $category->getAuthor());

        $this->client->loginUser($category->getAuthor());
    }

    /**
     * Log in as the note owner so NoteVoter identity checks pass.
     *
     * @param Note $note Note to resolve owner from
     */
    protected function loginAsNoteOwner(Note $note): void
    {
        $note = $this->noteRepository->find($note->getId());
        self::assertInstanceOf(Note::class, $note);
        self::assertInstanceOf(User::class, $note->getAuthor());

        $this->client->loginUser($note->getAuthor());
    }

    /**
     * Log in as the task owner so TaskVoter identity checks pass.
     *
     * @param Task $task Task to resolve owner from
     */
    protected function loginAsTaskOwner(Task $task): void
    {
        $task = $this->taskRepository->find($task->getId());
        self::assertInstanceOf(Task::class, $task);
        self::assertInstanceOf(User::class, $task->getAuthor());

        $this->client->loginUser($task->getAuthor());
    }
}
