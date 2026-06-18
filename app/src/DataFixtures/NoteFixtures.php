<?php

/**
 * Note fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Note;
use App\Entity\Tag;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class NoteFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class NoteFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (!$this->manager instanceof \Doctrine\Persistence\ObjectManager || !$this->faker instanceof \Faker\Generator) {
            return;
        }

        /** @var Category[] $categories */
        $categories = $this->manager->getRepository(Category::class)->findAll();
        foreach ($categories as $i => $category) {
            $note = new Note();
            $note->setTitle(sprintf('Default note %d', $i + 1));
            $note->setContent($this->faker->realText);
            $note->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $note->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $note->setCategory($category);
            for ($j = 0; $j < 3; ++$j) {
                $tag = $this->getRandomReference('tags', Tag::class);
                $note->addTag($tag);
            }

            $note->setAuthor($category->getAuthor());
            $this->manager->persist($note);
        }
        $this->manager->flush();

        $this->createMany(
            100,
            'notes',
            function (int $i) {
                $note = new Note();
                $note->setTitle($this->faker->sentence);
                $note->setContent($this->faker->realText);
                $note->setCreatedAt(
                    \DateTimeImmutable::createFromMutable(
                        $this->faker->dateTimeBetween('-100 days', '-1 days')
                    )
                );
                $note->setUpdatedAt(
                    \DateTimeImmutable::createFromMutable(
                        $this->faker->dateTimeBetween('-100 days', '-1 days')
                    )
                );

                $category = $this->getRandomReference('category', Category::class);
                $note->setCategory($category);

                for ($j = 0; $j < 3; ++$j) {
                    $tag = $this->getRandomReference('tags', Tag::class);
                    $note->addTag($tag);
                }

                $note->setAuthor($category->getAuthor());

                return $note;
            }
        );

        $this->manager->flush();
    }// end loadData()

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: CategoryFixtures::class, 1: TagFixtures::class, 2: UserFixtures::class}
     */
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            TagFixtures::class,
            UserFixtures::class,
        ];
    }// end getDependencies()
}// end class
