<?php

/**
 * Task controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Enum\UserRole;
use App\Tests\AbstractWebTestCase;

/**
 * Task controller integration tests.
 */
class TaskControllerTest extends AbstractWebTestCase
{
    private const string TEST_ROUTE = '/task';

    /**
     * Task list is only for logged-in users.
     */
    public function testIndexGuest(): void
    {
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE);

        $this->assertResponseRedirects('/login');
    }

    /**
     * Logged-in user should be able to see the list of tasks.
     */
    public function testIndexUser(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($user);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE);

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test pagination and parameter page.
     */
    public function testIndexPagination(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $category = $this->createCategory($user, 'Tasks category');
        $this->login($user);

        for ($i = 1; $i <= 11; ++$i) {
            $this->createTask($user, $category, sprintf('Task %02d', $i));
        }

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '?page=2');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Index accepts categoryId filter query parameter.
     */
    public function testIndexWithCategoryIdFilter(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user, 'Filtered category');
        $this->createTask($user, $category);
        $this->login($user);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '?categoryId=' . $category->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Index accepts tagId filter query parameter.
     */
    public function testIndexWithTagIdFilter(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user, 'Tagged tasks');
        $tag = $this->createTag('taskfiltertag');
        $task = $this->createTask($user, $category);
        $task->addTag($tag);
        $this->taskRepository->save($task);
        $this->login($user);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '?tagId=' . $tag->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Guest users should be redirected to the login page when attempting to access a private task.
     */
    public function testShowGuestRedirectsToLogin(): void
    {
        $owner = $this->createUser();
        $category = $this->createCategory($owner);
        $task = $this->createTask($owner, $category, 'Private task');

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $task->getId());

        $this->assertResponseRedirects('/login');
    }

    /**
     * Task owner should be able to access the specific task details successfully.
     */
    public function testShowOwnerReturnsSuccess(): void
    {
        $owner = $this->createUser();
        $category = $this->createCategory($owner);
        $task = $this->createTask($owner, $category, 'My task');
        $this->loginAsTaskOwner($task);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $task->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Ensures that when a logged-in user tries to access a task owned by another user,
     * they are redirected to the task index page.
     */
    public function testShowOtherUserRedirectsToIndex(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $category = $this->createCategory($owner);
        $task = $this->createTask($owner, $category, 'Not yours');

        $this->login($other);
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $task->getId());

        $this->assertResponseRedirects('/task');
    }

    /**
     * Guest users should be redirected to the login page when attempting to access the create route.
     */
    public function testCreateGuestRedirectsToLogin(): void
    {
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/create');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Ensures that the create task route displays the creation form successfully.
     */
    public function testCreateGetShowsForm(): void
    {
        $user = $this->createUser();
        $this->createCategory($user, 'Form category');
        $this->login($user);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/create');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
        $this->assertSelectorExists('input[name="task[title]"]');
    }

    /**
     * Authenticated user should be able to create a new task and
     * should be redirected to the task list upon successful creation.
     */
    public function testCreatePostValidRedirectsToIndex(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user, 'Create task category');
        $this->login($user);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/create');
        $form = $crawler->filter('form')->form([
            'task[title]' => 'New task title',
            'task[category]' => $category->getId(),
            'task[status]' => true,
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/task');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $saved = $this->taskRepository->findOneBy(['title' => 'New task title']);
        self::assertNotNull($saved);
        self::assertSame($user->getId(), $saved->getAuthor()?->getId());
        self::assertTrue($saved->getStatus());
    }

    /**
     * Tests that submitting a form with invalid data in the create post process
     * does not modify the database and re-renders the form correctly.
     */
    public function testCreatePostInvalidRendersFormAgain(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user, 'Invalid create category');
        $this->login($user);
        $beforeCount = count($this->taskRepository->findBy(['author' => $user]));

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/create');
        $form = $crawler->filter('form')->form([
            'task[title]' => 'Valid title',
            'task[category]' => $category->getId(),
            'task[tags]' => 'invalid tag!!!',
        ]);
        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        self::assertCount($beforeCount, $this->taskRepository->findBy(['author' => $user]));
    }

    /**
     * Tests that accessing the edit page as a guest redirects the user to the login page.
     */
    public function testEditGuestRedirectsToLogin(): void
    {
        $owner = $this->createUser();
        $category = $this->createCategory($owner);
        $task = $this->createTask($owner, $category, 'To edit');

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $task->getId() . '/edit');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Tests that attempting to edit a task owned by another user redirects
     * the current user to the task index page.
     */
    public function testEditOtherUserRedirectsToIndex(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $category = $this->createCategory($owner);
        $task = $this->createTask($owner, $category, 'Protected');

        $this->login($other);
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $task->getId() . '/edit');

        $this->assertResponseRedirects('/task');
    }

    /**
     * Tests that accessing the edit task endpoint as the owner
     * successfully displays the edit form.
     */
    public function testEditOwnerGetShowsForm(): void
    {
        $owner = $this->createUser();
        $category = $this->createCategory($owner);
        $task = $this->createTask($owner, $category, 'Editable');
        $this->loginAsTaskOwner($task);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $task->getId() . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
    }

    /**
     * Tests that submitting a form with valid data in the edit task process
     * updates the database correctly and redirects to the expected route.
     */
    public function testEditOwnerPutValidUpdatesAndRedirects(): void
    {
        $owner = $this->createUser();
        $category = $this->createCategory($owner);
        $task = $this->createTask($owner, $category, 'Old title', false);
        $this->loginAsTaskOwner($task);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $task->getId() . '/edit');
        $form = $crawler->filter('form')->form([
            'task[title]' => 'Updated title',
            'task[category]' => $category->getId(),
            'task[status]' => true,
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/task');

        $updated = $this->taskRepository->find($task->getId());
        self::assertNotNull($updated);
        self::assertSame('Updated title', $updated->getTitle());
        self::assertTrue($updated->getStatus());
    }

    /**
     * Tests that submitting invalid data during the edit task process
     * does not modify the existing task and correctly re-renders the form.
     */
    public function testEditOwnerPutInvalidShowsForm(): void
    {
        $owner = $this->createUser();
        $category = $this->createCategory($owner);
        $task = $this->createTask($owner, $category, 'Valid title');
        $this->loginAsTaskOwner($task);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $task->getId() . '/edit');
        $form = $crawler->filter('form')->form([
            'task[title]' => 'Valid title',
            'task[category]' => $category->getId(),
            'task[tags]' => 'bad#tag',
        ]);
        $this->client->submit($form);

        $this->assertResponseIsSuccessful();

        $unchanged = $this->taskRepository->find($task->getId());
        self::assertNotNull($unchanged);
        self::assertSame('Valid title', $unchanged->getTitle());
    }

    /**
     * Ensures that attempting to delete a task as a guest user
     * redirects to the login page, preventing unauthorized access.
     */
    public function testDeleteGuestRedirectsToLogin(): void
    {
        $owner = $this->createUser();
        $category = $this->createCategory($owner);
        $task = $this->createTask($owner, $category, 'To delete');

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $task->getId() . '/delete');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Tests that attempting to delete a task owned by another user
     * redirects the user to the task index page without modifying the database.
     */
    public function testDeleteOtherUserRedirectsToIndex(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $category = $this->createCategory($owner);
        $task = $this->createTask($owner, $category, 'Keep');

        $this->login($other);
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $task->getId() . '/delete');

        $this->assertResponseRedirects('/task');
    }

    /**
     * Tests that accessing the delete confirmation page via a GET request
     * renders the confirmation form correctly and does not perform any deletion.
     */
    public function testDeleteGetShowsConfirmation(): void
    {
        $owner = $this->createUser();
        $category = $this->createCategory($owner);
        $task = $this->createTask($owner, $category, 'Delete me');
        $this->loginAsTaskOwner($task);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $task->getId() . '/delete');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
    }

    /**
     * Tests that deleting a task successfully removes it from the database
     * and redirects to the task index page.
     */
    public function testDeletePostSuccessRemovesTask(): void
    {
        $owner = $this->createUser();
        $category = $this->createCategory($owner);
        $task = $this->createTask($owner, $category, 'Gone soon');
        $taskId = $task->getId();
        $this->loginAsTaskOwner($task);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $taskId . '/delete');
        $form = $crawler->filter('form')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/task');
        self::assertNull($this->taskRepository->find($taskId));
    }
}
