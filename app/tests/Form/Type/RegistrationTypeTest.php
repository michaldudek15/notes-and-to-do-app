<?php

/**
 * Registration form type tests.
 */

namespace App\Tests\Form\Type;

use App\Entity\User;
use App\Form\Type\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

class RegistrationTypeTest extends KernelTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->formFactory = static::getContainer()->get(FormFactoryInterface::class);
    }

    public function testFormContainsEmailAndPasswordFields(): void
    {
        $form = $this->formFactory->create(RegistrationType::class, new User());

        self::assertTrue($form->has('email'));
        self::assertTrue($form->has('password'));
    }

    public function testGetBlockPrefixReturnsUser(): void
    {
        $formType = static::getContainer()->get(RegistrationType::class);

        self::assertSame('user', $formType->getBlockPrefix());
    }

    public function testSubmitValidDataUpdatesEntityPassword(): void
    {
        $user = new User();
        $user->setEmail('old@example.com');
        $user->setPassword('oldpassword123');
        $user->setRoles(['ROLE_USER']);

        $email = 'new'.uniqid().'@example.com';

        $form = $this->formFactory->create(RegistrationType::class, $user, [
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
        self::assertSame('newpassword', $user->getPassword());
    }

    public function testSubmitMismatchedPasswordsIsInvalid(): void
    {
        $user = new User();
        $user->setEmail('old@example.com');
        $user->setPassword('oldpassword123');
        $user->setRoles(['ROLE_USER']);

        $form = $this->formFactory->create(RegistrationType::class, $user, [
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
}
