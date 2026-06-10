<?php

/**
 * User form type tests.
 */

namespace App\Tests\Form\Type;

use App\Entity\User;
use App\Form\Type\UserType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

class UserTypeTest extends KernelTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->formFactory = static::getContainer()->get(\Symfony\Component\Form\FormFactory::class);
    }

    public function testFormContainsEmailAndPasswordFields(): void
    {
        $form = $this->formFactory->create(UserType::class, new User());

        self::assertTrue($form->has('email'));
        self::assertTrue($form->has('password'));
    }

    public function testGetBlockPrefixReturnsUser(): void
    {
        $userType = static::getContainer()->get(UserType::class);

        self::assertSame('user', $userType->getBlockPrefix());
    }

    public function testSubmitValidDataUpdatesEmailAndExposesPassword(): void
    {
        $user = new User();
        $user->setEmail('old@example.com');
        $user->setPassword('oldpassword123');
        $user->setRoles(['ROLE_USER']);

        $email = 'new'.uniqid().'@example.com';

        $form = $this->formFactory->create(UserType::class, $user, [
            'csrf_protection' => false,
        ]);
        $form->submit([
            'email' => $email,
            'password' => [
                'first' => 'newpassword',
                'second' => 'newpassword',
            ],
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
        self::assertSame($email, $user->getEmail());
        self::assertSame('newpassword', $form->get('password')->getData());
        self::assertSame('oldpassword123', $user->getPassword());
    }

    public function testSubmitMismatchedPasswordsIsInvalid(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword('oldpassword123');
        $user->setRoles(['ROLE_USER']);

        $form = $this->formFactory->create(UserType::class, $user, [
            'csrf_protection' => false,
        ]);
        $form->submit([
            'email' => 'user@example.com',
            'password' => [
                'first' => 'password123',
                'second' => 'different12',
            ],
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
    }

    public function testSubmitWithoutPasswordDoesNotChangeEntityPassword(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword('oldpassword123');
        $user->setRoles(['ROLE_USER']);

        $form = $this->formFactory->create(UserType::class, $user, [
            'csrf_protection' => false,
        ]);
        $form->submit([
            'email' => 'updated@example.com',
            'password' => [
                'first' => '',
                'second' => '',
            ],
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
        self::assertSame('updated@example.com', $user->getEmail());
        self::assertSame('oldpassword123', $user->getPassword());
        self::assertNull($form->get('password')->getData());
    }

    public function testSubmitInvalidEmailIsInvalid(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword('oldpassword123');
        $user->setRoles(['ROLE_USER']);

        $form = $this->formFactory->create(UserType::class, $user, [
            'csrf_protection' => false,
        ]);
        $form->submit([
            'email' => 'not-an-email',
            'password' => [
                'first' => 'password123',
                'second' => 'password123',
            ],
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
    }
}
