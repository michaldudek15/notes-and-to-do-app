<?php

/**
 * Note controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Note;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Enum\UserRole;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Note controller integration tests.
 */
class NoteControllerTest extends AbstractWebTestCase
{
    private const string TEST_ROUTE = '/note';

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
     * Creates and persists tag via entity manager.
     */
    private function createManagedTag(string $title = 'Test tag'): Tag
    {
        $entityManager = $this->getEntityManager();

        $tag = new Tag();
        $tag->setTitle($title);

        $entityManager->persist($tag);
        $entityManager->flush();

        return $tag;
    }

    /**
     * Note list is only for logged-in users.
     */
    public function testIndexGuest(): void
    {
        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE);

        $this->assertResponseRedirects('/login');
    }

    /**
     * Logged-in user should be able to see the list of notes.
     */
    public function testIndexUser(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($user);

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE);

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test pagination and parameter page.
     */
    public function testIndexPagination(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $category = $this->createManagedCategory($user, 'Notes category');
        $this->login($user);

        for ($i = 1; $i <= 11; ++$i) {
            $this->createManagedNote($user, $category, sprintf('Note %02d', $i));
        }

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'?page=2');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Index accepts categoryId filter query parameter.
     */
    public function testIndexWithCategoryIdFilter(): void
    {
        $user = $this->createUser();
        $category = $this->createManagedCategory($user, 'Filtered category');
        $this->createManagedNote($user, $category);
        $this->login($user);

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'?categoryId='.$category->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Index accepts tagId filter query parameter.
     */
    public function testIndexWithTagIdFilter(): void
    {
        $user = $this->createUser();
        $category = $this->createManagedCategory($user, 'Tagged notes');
        $tag = $this->createManagedTag('filtertag');
        $note = $this->createManagedNote($user, $category);
        $note->addTag($tag);
        $entityManager = $this->getEntityManager();
        $entityManager->persist($note);
        $entityManager->flush();
        $this->login($user);

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'?tagId='.$tag->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Guest users should be redirected to the login page when attempting to access a private note.
     */
    public function testShowGuestRedirectsToLogin(): void
    {
        $owner = $this->createUser();
        $category = $this->createManagedCategory($owner);
        $note = $this->createManagedNote($owner, $category, 'Private note');

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$note->getId());

        $this->assertResponseRedirects('/login');
    }

    /**
     * Note owner should be able to access the specific note details successfully.
     */
    public function testShowOwnerReturnsSuccess(): void
    {
        $owner = $this->createUser();
        $category = $this->createManagedCategory($owner);
        $note = $this->createManagedNote($owner, $category, 'My note');
        $this->loginAsNoteOwner($note);

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$note->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Ensures that when a logged-in user tries to access a note owned by another user,
     * they are redirected to the note index page.
     */
    public function testShowOtherUserRedirectsToIndex(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $category = $this->createManagedCategory($owner);
        $note = $this->createManagedNote($owner, $category, 'Not yours');

        $this->login($other);
        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$note->getId());

        $this->assertResponseRedirects('/note');
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
     * Ensures that the create note route displays the creation form successfully.
     */
    public function testCreateGetShowsForm(): void
    {
        $user = $this->createUser();
        $this->createManagedCategory($user, 'Form category');
        $this->login($user);

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/create');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
        $this->assertSelectorExists('input[name="note[title]"]');
    }

    /**
     * Authenticated user should be able to create a new note and
     * should be redirected to the note list upon successful creation.
     */
    public function testCreatePostValidRedirectsToIndex(): void
    {
        $user = $this->createUser();
        $category = $this->createManagedCategory($user, 'Create note category');
        $this->login($user);

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/create');
        $form = $crawler->filter('form')->form([
            'note[title]' => 'New note title',
            'note[content]' => 'Valid note content.',
            'note[category]' => $category->getId(),
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/note');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $saved = $this->noteRepository->findOneBy(['title' => 'New note title']);
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
        $category = $this->createManagedCategory($user, 'Invalid create category');
        $this->login($user);
        $beforeCount = count($this->noteRepository->findBy(['author' => $user]));

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/create');
        $form = $crawler->filter('form')->form([
            'note[title]' => 'Valid title',
            'note[content]' => 'Valid note content.',
            'note[category]' => $category->getId(),
            'note[tags]' => 'invalid tag!!!',
        ]);
        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        self::assertCount($beforeCount, $this->noteRepository->findBy(['author' => $user]));
    }

    /**
     * Tests that accessing the edit page as a guest redirects the user to the login page.
     */
    public function testEditGuestRedirectsToLogin(): void
    {
        $owner = $this->createUser();
        $category = $this->createManagedCategory($owner);
        $note = $this->createManagedNote($owner, $category, 'To edit');

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$note->getId().'/edit');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Tests that attempting to edit a note owned by another user redirects
     * the current user to the note index page.
     */
    public function testEditOtherUserRedirectsToIndex(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $category = $this->createManagedCategory($owner);
        $note = $this->createManagedNote($owner, $category, 'Protected');

        $this->login($other);
        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$note->getId().'/edit');

        $this->assertResponseRedirects('/note');
    }

    /**
     * Tests that accessing the edit note endpoint as the owner
     * successfully displays the edit form.
     */
    public function testEditOwnerGetShowsForm(): void
    {
        $owner = $this->createUser();
        $category = $this->createManagedCategory($owner);
        $note = $this->createManagedNote($owner, $category, 'Editable');
        $this->loginAsNoteOwner($note);

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$note->getId().'/edit');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
    }

    /**
     * Tests that submitting a form with valid data in the edit note process
     * updates the database correctly and redirects to the expected route.
     */
    public function testEditOwnerPutValidUpdatesAndRedirects(): void
    {
        $owner = $this->createUser();
        $category = $this->createManagedCategory($owner);
        $note = $this->createManagedNote($owner, $category, 'Old title');
        $this->loginAsNoteOwner($note);

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$note->getId().'/edit');
        $form = $crawler->filter('form')->form([
            'note[title]' => 'Updated title',
            'note[content]' => 'Updated content.',
            'note[category]' => $category->getId(),
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/note');

        $updated = $this->noteRepository->find($note->getId());
        self::assertNotNull($updated);
        self::assertSame('Updated title', $updated->getTitle());
    }

    /**
     * Tests that submitting invalid data during the edit note process
     * does not modify the existing note and correctly re-renders the form.
     */
    public function testEditOwnerPutInvalidShowsForm(): void
    {
        $owner = $this->createUser();
        $category = $this->createManagedCategory($owner);
        $note = $this->createManagedNote($owner, $category, 'Valid title');
        $this->loginAsNoteOwner($note);

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$note->getId().'/edit');
        $form = $crawler->filter('form')->form([
            'note[title]' => 'Valid title',
            'note[content]' => 'Still valid content.',
            'note[category]' => $category->getId(),
            'note[tags]' => 'bad#tag',
        ]);
        $this->client->submit($form);

        $this->assertResponseIsSuccessful();

        $unchanged = $this->noteRepository->find($note->getId());
        self::assertNotNull($unchanged);
        self::assertSame('Valid title', $unchanged->getTitle());
    }

    /**
     * Ensures that attempting to delete a note as a guest user
     * redirects to the login page, preventing unauthorized access.
     */
    public function testDeleteGuestRedirectsToLogin(): void
    {
        $owner = $this->createUser();
        $category = $this->createManagedCategory($owner);
        $note = $this->createManagedNote($owner, $category, 'To delete');

        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$note->getId().'/delete');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Tests that attempting to delete a note owned by another user
     * redirects the user to the note index page without modifying the database.
     */
    public function testDeleteOtherUserRedirectsToIndex(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $category = $this->createManagedCategory($owner);
        $note = $this->createManagedNote($owner, $category, 'Keep');

        $this->login($other);
        $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$note->getId().'/delete');

        $this->assertResponseRedirects('/note');
    }

    /**
     * Tests that accessing the delete confirmation page via a GET request
     * renders the confirmation form correctly and does not perform any deletion.
     */
    public function testDeleteGetShowsConfirmation(): void
    {
        $owner = $this->createUser();
        $category = $this->createManagedCategory($owner);
        $note = $this->createManagedNote($owner, $category, 'Delete me');
        $this->loginAsNoteOwner($note);

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$note->getId().'/delete');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
    }

    /**
     * Tests that deleting a note successfully removes it from the database
     * and redirects to the note index page.
     */
    public function testDeletePostSuccessRemovesNote(): void
    {
        $owner = $this->createUser();
        $category = $this->createManagedCategory($owner);
        $note = $this->createManagedNote($owner, $category, 'Gone soon');
        $noteId = $note->getId();
        $this->loginAsNoteOwner($note);

        $crawler = $this->client->request(Request::METHOD_GET, self::TEST_ROUTE.'/'.$noteId.'/delete');
        $form = $crawler->filter('form')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/note');
        self::assertNull($this->noteRepository->find($noteId));
    }
}
