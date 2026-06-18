<?php

/**
 * Email change form type tests.
 */

namespace App\Tests\Form\Type;

use App\Entity\User;
use App\Form\Type\EmailChangeType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Email change form type tests.
 */
class EmailChangeTypeTest extends KernelTestCase
{
    private FormFactoryInterface $formFactory;

    /**
     * Boots kernel and fetches form factory.
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->formFactory = static::getContainer()->get(FormFactoryInterface::class);
    }

    /**
     * It exposes email field.
     */
    public function testFormContainsEmailField(): void
    {
        $form = $this->formFactory->create(EmailChangeType::class, new User());

        self::assertTrue($form->has('email'));
    }

    /**
     * It exposes expected block prefix.
     */
    public function testGetBlockPrefixReturnsUser(): void
    {
        $formType = static::getContainer()->get(EmailChangeType::class);

        self::assertSame('user', $formType->getBlockPrefix());
    }

    /**
     * It accepts valid email values.
     */
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
            'email' => 'new' . uniqid() . '@example.com',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
        self::assertStringContainsString('new', $user->getEmail());
    }

    /**
     * It rejects invalid email values.
     */
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
