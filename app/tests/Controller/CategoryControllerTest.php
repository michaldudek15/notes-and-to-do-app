<?php

/**
 * Category controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Enum\UserRole;
use App\Tests\AbstractWebTestCase;

class CategoryControllerTest extends AbstractWebTestCase
{
    private const string TEST_ROUTE = '/category';

    /**
     * Guest cannot access category list and is redirected to login.
     */
    public function testIndexGuest(): void
    {
        $this->client->request('GET', self::TEST_ROUTE);

        $this->assertResponseRedirects('/login');
    }

    public function testIndexUser(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($user);

        $this->client->request('GET', self::TEST_ROUTE);

        $this->assertResponseIsSuccessful();
    }

    public function testIndexPagination(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($user);

        for ($i = 1; $i <= 11; ++$i) {
            $this->createCategory($user, sprintf('Category %02d', $i));
        }

        $this->client->request('GET', self::TEST_ROUTE.'?page=2');

        $this->assertResponseIsSuccessful();
    }

    public function testShowGuestRedirectsToLogin(): void
    {
        $owner = $this->createUser();
        $category = $this->createCategory($owner, 'Private category');

        $this->client->request('GET', self::TEST_ROUTE.'/'.$category->getId());

        $this->assertResponseRedirects('/login');
    }

    public function testShowOwnerReturnsSuccess(): void
    {
        $owner = $this->createUser();
        $category = $this->createCategory($owner, 'My category');
        $this->loginAsCategoryOwner($category);

        $this->client->request('GET', self::TEST_ROUTE.'/'.$category->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testShowOtherUserRedirectsToIndex(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $category = $this->createCategory($owner, 'Not yours');

        $this->login($other);
        $this->client->request('GET', self::TEST_ROUTE.'/'.$category->getId());

        $this->assertResponseRedirects('/category');
    }

    public function testCreateGuestRedirectsToLogin(): void
    {
        $this->client->request('GET', self::TEST_ROUTE.'/create');

        $this->assertResponseRedirects('/login');
    }

    public function testCreateGetShowsForm(): void
    {
        $this->login($this->createUser());
        $crawler = $this->client->request('GET', self::TEST_ROUTE.'/create');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
        $this->assertSelectorExists('input[name="category[title]"]');
    }

    public function testCreatePostValidRedirectsToIndex(): void
    {
        $user = $this->createUser();
        $this->login($user);

        $crawler = $this->client->request('GET', self::TEST_ROUTE.'/create');
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

    public function testCreatePostInvalidRendersFormAgain(): void
    {
        $user = $this->createUser();
        $this->login($user);
        $beforeCount = count($this->categoryRepository->findBy(['author' => $user]));

        $crawler = $this->client->request('GET', self::TEST_ROUTE.'/create');
        $form = $crawler->filter('form')->form([
            'category[title]' => 'ab',
        ]);
        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        self::assertCount($beforeCount, $this->categoryRepository->findBy(['author' => $user]));
    }

    public function testEditGuestRedirectsToLogin(): void
    {
        $category = $this->createCategory($this->createUser(), 'To edit');

        $this->client->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/edit');

        $this->assertResponseRedirects('/login');
    }

    public function testEditOtherUserRedirectsToIndex(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $category = $this->createCategory($owner, 'Protected');

        $this->login($other);
        $this->client->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/edit');

        $this->assertResponseRedirects('/category');
    }

    public function testEditOwnerGetShowsForm(): void
    {
        $category = $this->createCategory($this->createUser(), 'Editable');
        $this->loginAsCategoryOwner($category);

        $crawler = $this->client->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/edit');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
    }

    public function testEditOwnerPutValidUpdatesAndRedirects(): void
    {
        $category = $this->createCategory($this->createUser(), 'Old title');
        $this->loginAsCategoryOwner($category);

        $crawler = $this->client->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/edit');
        $form = $crawler->filter('form')->form([
            'category[title]' => 'Updated title',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/category');

        $updated = $this->categoryRepository->find($category->getId());
        self::assertNotNull($updated);
        self::assertSame('Updated title', $updated->getTitle());
    }

    public function testEditOwnerPutInvalidShowsForm(): void
    {
        $category = $this->createCategory($this->createUser(), 'Valid title');
        $this->loginAsCategoryOwner($category);

        $crawler = $this->client->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/edit');
        $form = $crawler->filter('form')->form([
            'category[title]' => 'no',
        ]);
        $this->client->submit($form);

        $this->assertResponseIsSuccessful();

        $unchanged = $this->categoryRepository->find($category->getId());
        self::assertNotNull($unchanged);
        self::assertSame('Valid title', $unchanged->getTitle());
    }

    public function testDeleteGuestRedirectsToLogin(): void
    {
        $category = $this->createCategory($this->createUser(), 'To delete');

        $this->client->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/delete');

        $this->assertResponseRedirects('/login');
    }

    public function testDeleteOtherUserRedirectsToIndex(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $category = $this->createCategory($owner, 'Keep');

        $this->login($other);
        $this->client->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/delete');

        $this->assertResponseRedirects('/category');
    }

    public function testDeleteGetShowsConfirmation(): void
    {
        $category = $this->createCategory($this->createUser(), 'Delete me');
        $this->loginAsCategoryOwner($category);

        $crawler = $this->client->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/delete');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
    }

    public function testDeletePostSuccessRemovesCategory(): void
    {
        $category = $this->createCategory($this->createUser(), 'Gone soon');
        $categoryId = $category->getId();
        $this->loginAsCategoryOwner($category);

        $crawler = $this->client->request('GET', self::TEST_ROUTE.'/'.$categoryId.'/delete');
        $form = $crawler->filter('form')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/category');
        self::assertNull($this->categoryRepository->find($categoryId));
    }

    public function testDeleteCategoryWithNotesRedirectsAndKeepsCategory(): void
    {
        $owner = $this->createUser();
        $category = $this->createCategory($owner, 'Has notes');
        $this->createNote($owner, $category);
        $categoryId = $category->getId();
        $this->loginAsCategoryOwner($category);

        $this->client->request('GET', self::TEST_ROUTE.'/'.$categoryId.'/delete');

        $this->assertResponseRedirects('/category');
        self::assertNotNull($this->categoryRepository->find($categoryId));
    }
}
