<?php

/**
 * Category controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Note;
use App\Entity\User;
use App\Entity\Enum\UserRole;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Category controller integration tests.
 */
class CategoryControllerTest extends AbstractWebTestCase
{
    private const string TEST_ROUTE = '/category';

    /**
     * Returns Doctrine entity manager.
     */
    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Creates and persists category via entity manager.
     */
    private function createManagedCategory(User $author, string $title = 'Test category'): Category
    {
        $entityManager = $this->getEntityManager();

        $category = new Category();
        $category->setTitle($title);
        $category->setAuthor($author);

        $entityManager->persist($category);
        $entityManager->flush();

        return $category;
    }

    /**
     * Creates and persists note via entity manager.
     */
    private function createManagedNote(
        User $author,
        Category $category,
        string $title = 'Test note',
        string $content = 'Test content for note.',
    ): Note {
        $entityManager = $this->getEntityManager();

        $note = new Note();
        $note->setTitle($title);
        $note->setContent($content);
        $note->setAuthor($author);
        $note->setCategory($category);

        $entityManager->persist($note);
        $entityManager->flush();

        return $note;
    }

    /**
     * category list is only for logged-in users.
     */
    public function testIndexGuest(): void
    {
        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE);

        $this->assertResponseRedirects('/login');
    }

    /**
     * logged-in user should be able to see the list of categories.
     */
    public function testIndexUser(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($user);

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE);

        $this->assertResponseIsSuccessful();
    }

    /**
     * test pagination and parameter page.
     */
    public function testIndexPagination(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($user);

        for ($i = 1; $i <= 11; ++$i) {
            $this->createManagedCategory($user, sprintf('Category %02d', $i));
        }

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'?page=2');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Guest users should be redirected to the login page when attempting to access a private category.
     */
    public function testShowGuestRedirectsToLogin(): void
    {
        $owner = $this->createUser();
        $category = $this->createManagedCategory($owner, 'Private category');

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$category->getId());

        $this->assertResponseRedirects('/login');
    }

    /**
     * Category owner should be able to access the specific category details successfully.
     */
    public function testShowOwnerReturnsSuccess(): void
    {
        $owner = $this->createUser();
        $category = $this->createManagedCategory($owner, 'My category');
        $this->loginAsCategoryOwner($category);

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$category->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Ensures that when a logged-in user tries to access a category owned by another user,
     * they are redirected to the category index page.
     */
    public function testShowOtherUserRedirectsToIndex(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $category = $this->createManagedCategory($owner, 'Not yours');

        $this->login($other);
        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$category->getId());

        $this->assertResponseRedirects('/category');
    }

    /**
     * Guest users should be redirected to the login page when attempting to access the create route.
     */
    public function testCreateGuestRedirectsToLogin(): void
    {
        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/create');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Ensures that the create category route displays the creation form successfully.
     */
    public function testCreateGetShowsForm(): void
    {
        $this->login($this->createUser());
        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/create');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
        $this->assertSelectorExists('input[name="category[title]"]');
    }

    /**
     * Authenticated user should be able to create a new category and
     * should be redirected to the category list upon successful creation.
     * The created category should persist in the database with the correct author.
     */
    public function testCreatePostValidRedirectsToIndex(): void
    {
        $user = $this->createUser();
        $this->login($user);

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/create');
        $form = $crawler->filter('form')->form([
            'category[title]' => 'New category title',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/category');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $saved = $this->categoryRepository->findOneBy(['title' => 'New category title']);
        self::assertNotNull($saved);
        self::assertSame($user->getId(), $saved->getAuthor()?->getId());
    }

    /**
     * Tests that submitting a form with invalid data in the create post process
     * does not modify the database and re-renders the form correctly.
     */
    public function testCreatePostInvalidRendersFormAgain(): void
    {
        $user = $this->createUser();
        $this->login($user);
        $beforeCount = count($this->categoryRepository->findBy(['author' => $user]));

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/create');
        $form = $crawler->filter('form')->form([
            'category[title]' => 'ab',
        ]);
        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        self::assertCount($beforeCount, $this->categoryRepository->findBy(['author' => $user]));
    }

    /**
     * Tests that accessing the edit page as a guest redirects the user to the login page.
     */
    public function testEditGuestRedirectsToLogin(): void
    {
        $category = $this->createManagedCategory($this->createUser(), 'To edit');

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$category->getId().'/edit');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Tests that attempting to edit a category owned by another user redirects
     * the current user to the category index page.
     */
    public function testEditOtherUserRedirectsToIndex(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $category = $this->createManagedCategory($owner, 'Protected');

        $this->login($other);
        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$category->getId().'/edit');

        $this->assertResponseRedirects('/category');
    }

    /**
     * Tests that accessing the edit category endpoint as the owner
     * successfully displays the edit form.
     */
    public function testEditOwnerGetShowsForm(): void
    {
        $category = $this->createManagedCategory($this->createUser(), 'Editable');
        $this->loginAsCategoryOwner($category);

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$category->getId().'/edit');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
    }

    /**
     * Tests that submitting a form with valid data in the edit category process
     * updates the database correctly and redirects to the expected route.
     */
    public function testEditOwnerPutValidUpdatesAndRedirects(): void
    {
        $category = $this->createManagedCategory($this->createUser(), 'Old title');
        $this->loginAsCategoryOwner($category);

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$category->getId().'/edit');
        $form = $crawler->filter('form')->form([
            'category[title]' => 'Updated title',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/category');

        $updated = $this->categoryRepository->find($category->getId());
        self::assertNotNull($updated);
        self::assertSame('Updated title', $updated->getTitle());
    }

    /**
     * Tests that submitting invalid data during the edit category process
     * does not modify the existing category and correctly re-renders the form.
     */
    public function testEditOwnerPutInvalidShowsForm(): void
    {
        $category = $this->createManagedCategory($this->createUser(), 'Valid title');
        $this->loginAsCategoryOwner($category);

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$category->getId().'/edit');
        $form = $crawler->filter('form')->form([
            'category[title]' => 'no',
        ]);
        $this->client->submit($form);

        $this->assertResponseIsSuccessful();

        $unchanged = $this->categoryRepository->find($category->getId());
        self::assertNotNull($unchanged);
        self::assertSame('Valid title', $unchanged->getTitle());
    }

    /**
     * Ensures that attempting to delete a category as a guest user
     * redirects to the login page, preventing unauthorized access.
     */
    public function testDeleteGuestRedirectsToLogin(): void
    {
        $category = $this->createManagedCategory($this->createUser(), 'To delete');

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$category->getId().'/delete');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Tests that attempting to delete a category owned by another user
     * redirects the user to the category index page without modifying the database.
     */
    public function testDeleteOtherUserRedirectsToIndex(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $category = $this->createManagedCategory($owner, 'Keep');

        $this->login($other);
        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$category->getId().'/delete');

        $this->assertResponseRedirects('/category');
    }

    /**
     * Tests that accessing the delete confirmation page via a GET request
     * renders the confirmation form correctly and does not perform any deletion.
     */
    public function testDeleteGetShowsConfirmation(): void
    {
        $category = $this->createManagedCategory($this->createUser(), 'Delete me');
        $this->loginAsCategoryOwner($category);

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$category->getId().'/delete');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
    }

    /**
     * Tests that deleting a category successfully removes it from the database
     * and redirects to the category index page.
     */
    public function testDeletePostSuccessRemovesCategory(): void
    {
        $category = $this->createManagedCategory($this->createUser(), 'Gone soon');
        $categoryId = $category->getId();
        $this->loginAsCategoryOwner($category);

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$categoryId.'/delete');
        $form = $crawler->filter('form')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/category');
        self::assertNull($this->categoryRepository->find($categoryId));
    }

    /**
     * Tests that attempting to delete a category with associated notes
     * redirects to the category list page and ensures the category is not deleted.
     */
    public function testDeleteCategoryWithNotesRedirectsAndKeepsCategory(): void
    {
        $owner = $this->createUser();
        $category = $this->createManagedCategory($owner, 'Has notes');
        $this->createManagedNote($owner, $category);
        $categoryId = $category->getId();
        $this->loginAsCategoryOwner($category);

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$categoryId.'/delete');

        $this->assertResponseRedirects('/category');
        self::assertNotNull($this->categoryRepository->find($categoryId));
    }
}
