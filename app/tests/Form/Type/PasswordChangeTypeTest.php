<?php

/**
 * Password change form type tests.
 */

namespace App\Tests\Form\Type;

use App\Entity\User;
use App\Form\Type\PasswordChangeType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

class PasswordChangeTypeTest extends KernelTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->formFactory = static::getContainer()->get(\Symfony\Component\Form\FormFactory::class);
    }

    public function testFormContainsCurrentPasswordAndPasswordFields(): void
    {
        $form = $this->formFactory->create(PasswordChangeType::class, new User());

        self::assertTrue($form->has('currentPassword'));
        self::assertTrue($form->has('password'));
    }

    public function testGetBlockPrefixReturnsUser(): void
    {
        $formType = static::getContainer()->get(PasswordChangeType::class);

        self::assertSame('user', $formType->getBlockPrefix());
    }

    public function testSubmitMatchingNewPasswordsIsValidAndExposesNewPassword(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword('oldpassword123');
        $user->setRoles(['ROLE_USER']);

        $form = $this->formFactory->create(PasswordChangeType::class, $user, [
            'csrf_protection' => false,
        ]);
        $form->submit([
            'currentPassword' => 'oldpassword123',
            'password' => [
                'first' => 'newpassword123',
                'second' => 'newpassword123',
            ],
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
        self::assertSame('newpassword123', $form->get('password')->getData());
        self::assertSame('oldpassword123', $user->getPassword());
    }

    public function testSubmitMismatchedNewPasswordsIsInvalid(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword('oldpassword123');
        $user->setRoles(['ROLE_USER']);

        $form = $this->formFactory->create(PasswordChangeType::class, $user, [
            'csrf_protection' => false,
        ]);
        $form->submit([
            'currentPassword' => 'oldpassword123',
            'password' => [
                'first' => 'newpassword123',
                'second' => 'differentpassword',
            ],
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
    }
}
