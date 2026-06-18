<?php

/**
 * Tag form type tests.
 */

namespace App\Tests\Form\Type;

use App\Entity\Tag;
use App\Form\Type\TagType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Tag form type tests.
 */
class TagTypeTest extends KernelTestCase
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
     * It exposes title field.
     */
    public function testFormContainsTitleField(): void
    {
        $form = $this->formFactory->create(TagType::class, new Tag());

        self::assertTrue($form->has('title'));
    }

    /**
     * It exposes expected block prefix.
     */
    public function testGetBlockPrefixReturnsTag(): void
    {
        $formType = static::getContainer()->get(TagType::class);

        self::assertSame('tag', $formType->getBlockPrefix());
    }

    /**
     * It accepts a valid title.
     */
    public function testSubmitValidTitleUpdatesEntity(): void
    {
        $tag = new Tag();
        $tag->setTitle('oldtag');

        $form = $this->formFactory->create(TagType::class, $tag, [
            'csrf_protection' => false,
        ]);
        $form->submit([
            'title' => 'validtag123',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
        self::assertSame('validtag123', $tag->getTitle());
    }

    /**
     * It rejects an invalid title.
     */
    public function testSubmitInvalidTitleIsInvalid(): void
    {
        $tag = new Tag();
        $tag->setTitle('oldtag');

        $form = $this->formFactory->create(TagType::class, $tag, [
            'csrf_protection' => false,
        ]);
        $form->submit([
            'title' => 'bad tag!',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
    }
}
