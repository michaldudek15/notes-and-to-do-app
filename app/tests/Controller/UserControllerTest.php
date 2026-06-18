<?php

/**
 * User controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * User controller integration tests.
 */
class UserControllerTest extends AbstractWebTestCase
{
    private const string TEST_ROUTE = '/user';

    /**
     * Guest is redirected to login from index.
     */
    public function testIndexGuestRedirectsToLogin(): void
    {
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE);

        $this->assertResponseRedirects('/login');
    }

    /**
     * Non-admin is redirected away from index.
     */
    public function testIndexNonAdminRedirectsToNote(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($user);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE);

        $this->assertResponseRedirects('/note');
    }

    /**
     * Admin can access index page.
     */
    public function testIndexAdminReturnsSuccess(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($admin);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE);

        $this->assertResponseIsSuccessful();
    }

    /**
     * Admin can open user details.
     */
    public function testShowAdminReturnsSuccess(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($admin);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $target->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Non-admin cannot open user details.
     */
    public function testShowNonAdminRedirectsToNote(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($user);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $target->getId());

        $this->assertResponseRedirects('/note');
    }

    /**
     * Guest cannot open user details.
     */
    public function testShowGuestRedirectsToLogin(): void
    {
        $target = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $target->getId());

        $this->assertResponseRedirects('/login');
    }

    /**
     * Admin sees edit form.
     */
    public function testEditAdminGetShowsForm(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $target->getId() . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
        $this->assertSelectorExists('input[name="user[email]"]');
    }

    /**
     * Guest cannot access edit form.
     */
    public function testEditGuestRedirectsToLogin(): void
    {
        $target = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $target->getId() . '/edit');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Admin updates email and password.
     */
    public function testEditAdminPutValidUpdatesUserAndRedirects(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $targetId = $target->getId();
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $targetId . '/edit');
        $form = $crawler->filter('form')->form([
            'user[email]' => 'updated' . uniqid() . '@example.com',
            'user[password][first]' => 'newpassword',
            'user[password][second]' => 'newpassword',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/user');

        $updated = $this->userRepository->find($targetId);
        self::assertInstanceOf(User::class, $updated);
        self::assertStringContainsString('updated', $updated->getEmail());

        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($passwordHasher->isPasswordValid($updated, 'newpassword'));
    }

    /**
     * Admin can update email without changing password.
     */
    public function testEditAdminPutEmailOnlyUpdatesEmailWithoutChangingPassword(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $targetId = $target->getId();
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $targetId . '/edit');
        $form = $crawler->filter('form')->form([
            'user[email]' => 'email-only-' . uniqid() . '@example.com',
            'user[password][first]' => '',
            'user[password][second]' => '',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/user');

        $updated = $this->userRepository->find($targetId);
        self::assertInstanceOf(User::class, $updated);
        self::assertStringContainsString('email-only-', $updated->getEmail());

        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($passwordHasher->isPasswordValid($updated, 'password'));
    }

    /**
     * Invalid edit payload re-renders edit form.
     */
    public function testEditAdminPutInvalidShowsFormAgain(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $targetId = $target->getId();
        $beforeEmail = $target->getEmail();
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $targetId . '/edit');
        $form = $crawler->filter('form')->form([
            'user[email]' => 'invalid-email',
            'user[password][first]' => 'newpassword',
            'user[password][second]' => 'newpassword',
        ]);
        $this->client->submit($form);

        $this->assertResponseIsSuccessful();

        $unchanged = $this->userRepository->find($targetId);
        self::assertInstanceOf(User::class, $unchanged);
        self::assertSame($beforeEmail, $unchanged->getEmail());
    }

    /**
     * Admin sees delete confirmation form.
     */
    public function testDeleteAdminGetShowsConfirmation(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $target->getId() . '/delete');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
    }

    /**
     * Admin can delete user.
     */
    public function testDeleteAdminDeleteRemovesUserAndRedirects(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $targetId = $target->getId();
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $targetId . '/delete');
        $form = $crawler->filter('form')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/user');
        self::assertNull($this->userRepository->find($targetId));
    }

    /**
     * Non-admin cannot delete users.
     */
    public function testDeleteNonAdminRedirectsToNote(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($user);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $target->getId() . '/delete');

        $this->assertResponseRedirects('/note');
    }

    /**
     * Guest cannot delete users.
     */
    public function testDeleteGuestRedirectsToLogin(): void
    {
        $target = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $target->getId() . '/delete');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Admin sees role change form.
     */
    public function testChangeRoleAdminGetShowsForm(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $target->getId() . '/changeRole');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
    }

    /**
     * Admin can change role of another user.
     */
    public function testChangeRoleAdminPutValidUpdatesRolesAndRedirects(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $targetId = $target->getId();
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $targetId . '/changeRole');
        $form = $crawler->filter('form')->form([
            'user[roles]' => [UserRole::ROLE_ADMIN->value],
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/user');

        $updated = $this->userRepository->find($targetId);
        self::assertInstanceOf(User::class, $updated);
        self::assertContains(UserRole::ROLE_ADMIN->value, $updated->getRoles());
    }

    /**
     * Admin cannot change own role.
     */
    public function testChangeRoleForCurrentAdminRedirectsToIndex(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->login($admin);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $admin->getId() . '/changeRole');

        $this->assertResponseRedirects('/user');
    }

    /**
     * Non-admin cannot change roles.
     */
    public function testChangeRoleNonAdminRedirectsToNote(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($user);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $target->getId() . '/changeRole');

        $this->assertResponseRedirects('/note');
    }

    /**
     * Guest cannot access role change route.
     */
    public function testChangeRoleGuestRedirectsToLogin(): void
    {
        $target = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE . '/' . $target->getId() . '/changeRole');

        $this->assertResponseRedirects('/login');
    }
}
