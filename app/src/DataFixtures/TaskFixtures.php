<?php

/**
 * Task fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Task;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class TaskFixtures.
 */
class TaskFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
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

        $this->createMany(
            100,
            'tasks',
            function (int $i) {
                $task = new Task();
                $task->setTitle($this->faker->sentence);
                $task->setStatus($this->faker->boolean);
                $task->setCreatedAt(
                    \DateTimeImmutable::createFromMutable(
                        $this->faker->dateTimeBetween('-100 days', '-1 days')
                    )
                );
                $task->setUpdatedAt(
                    \DateTimeImmutable::createFromMutable(
                        $this->faker->dateTimeBetween('-100 days', '-1 days')
                    )
                );
                /*
                    @var Category $category
                */
                $category = $this->getRandomReference('categories');
                $task->setCategory($category);

                for ($i = 0; $i < 3; ++$i) {
                    $tag = $this->getRandomReference('tags');
                    $task->addTag($tag);
                }

                $author = $this->getRandomReference('users');
                $task->setAuthor($author);

                return $task;
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
     * @psalm-return array{0: CategoryFixtures::class}
     */
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            TagFixtures::class,
        ];
    }// end getDependencies()
}// end class
