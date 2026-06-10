<?php

/**
 * Email change form type tests.
 */

namespace App\Tests\Form\Type;

use App\Entity\User;
use App\Form\Type\EmailChangeType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

class EmailChangeTypeTest extends KernelTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->formFactory = static::getContainer()->get(FormFactoryInterface::class);
    }

    public function testFormContainsEmailField(): void
    {
        $form = $this->formFactory->create(EmailChangeType::class, new User());

        self::assertTrue($form->has('email'));
    }

    public function testGetBlockPrefixReturnsUser(): void
    {
        $formType = static::getContainer()->get(EmailChangeType::class);

        self::assertSame('user', $formType->getBlockPrefix());
    }

    public function testSubmitValidEmailUpdatesEntity(): void
    {
        $user = new User();
        $user->setEmail('old@example.com');
        $user->setPassword('password123');
        $user->setRoles(['ROLE_USER']);

        $form = $this->formFactory->create(EmailChangeType::class, $user, [
            'csrf_protection' => false,
        ]);
        $form->submit([
            'email' => 'new'.uniqid().'@example.com',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
        self::assertStringContainsString('new', $user->getEmail());
    }

    public function testSubmitInvalidEmailIsInvalid(): void
    {
        $user = new User();
        $user->setEmail('old@example.com');
        $user->setPassword('password123');
        $user->setRoles(['ROLE_USER']);

        $form = $this->formFactory->create(EmailChangeType::class, $user, [
            'csrf_protection' => false,
        ]);
        $form->submit([
            'email' => 'not-an-email',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
    }
}
