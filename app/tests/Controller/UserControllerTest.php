<?php

/**
 * User controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends AbstractWebTestCase
{
    private const string TEST_ROUTE = '/user';

    public function testIndexGuestRedirectsToLogin(): void
    {
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE);

        $this->assertResponseRedirects('/login');
    }

    public function testIndexNonAdminRedirectsToNote(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($user);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE);

        $this->assertResponseRedirects('/note');
    }

    public function testIndexAdminReturnsSuccess(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($admin);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE);

        $this->assertResponseIsSuccessful();
    }

    public function testShowAdminReturnsSuccess(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($admin);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$target->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testShowNonAdminRedirectsToNote(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($user);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$target->getId());

        $this->assertResponseRedirects('/note');
    }

    public function testShowGuestRedirectsToLogin(): void
    {
        $target = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$target->getId());

        $this->assertResponseRedirects('/login');
    }

    public function testEditAdminGetShowsForm(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$target->getId().'/edit');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
        $this->assertSelectorExists('input[name="user[email]"]');
    }

    public function testEditGuestRedirectsToLogin(): void
    {
        $target = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$target->getId().'/edit');

        $this->assertResponseRedirects('/login');
    }

    public function testEditAdminPutValidUpdatesUserAndRedirects(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $targetId = $target->getId();
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$targetId.'/edit');
        $form = $crawler->filter('form')->form([
            'user[email]' => 'updated'.uniqid().'@example.com',
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

    public function testEditAdminPutEmailOnlyUpdatesEmailWithoutChangingPassword(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $targetId = $target->getId();
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$targetId.'/edit');
        $form = $crawler->filter('form')->form([
            'user[email]' => 'email-only-'.uniqid().'@example.com',
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

    public function testEditAdminPutInvalidShowsFormAgain(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $targetId = $target->getId();
        $beforeEmail = $target->getEmail();
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$targetId.'/edit');
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

    public function testDeleteAdminGetShowsConfirmation(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$target->getId().'/delete');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
    }

    public function testDeleteAdminDeleteRemovesUserAndRedirects(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $targetId = $target->getId();
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$targetId.'/delete');
        $form = $crawler->filter('form')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/user');
        self::assertNull($this->userRepository->find($targetId));
    }

    public function testDeleteNonAdminRedirectsToNote(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($user);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$target->getId().'/delete');

        $this->assertResponseRedirects('/note');
    }

    public function testDeleteGuestRedirectsToLogin(): void
    {
        $target = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$target->getId().'/delete');

        $this->assertResponseRedirects('/login');
    }

    public function testChangeRoleAdminGetShowsForm(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$target->getId().'/changeRole');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form'));
    }

    public function testChangeRoleAdminPutValidUpdatesRolesAndRedirects(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $targetId = $target->getId();
        $this->login($admin);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$targetId.'/changeRole');
        $form = $crawler->filter('form')->form([
            'user[roles]' => [UserRole::ROLE_ADMIN->value],
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/user');

        $updated = $this->userRepository->find($targetId);
        self::assertInstanceOf(User::class, $updated);
        self::assertContains(UserRole::ROLE_ADMIN->value, $updated->getRoles());
    }

    public function testChangeRoleForCurrentAdminRedirectsToIndex(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->login($admin);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$admin->getId().'/changeRole');

        $this->assertResponseRedirects('/user');
    }

    public function testChangeRoleNonAdminRedirectsToNote(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $target = $this->createUser([UserRole::ROLE_USER->value]);
        $this->login($user);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$target->getId().'/changeRole');

        $this->assertResponseRedirects('/note');
    }

    public function testChangeRoleGuestRedirectsToLogin(): void
    {
        $target = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, self::TEST_ROUTE.'/'.$target->getId().'/changeRole');

        $this->assertResponseRedirects('/login');
    }
}
