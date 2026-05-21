<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Task;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

class TaskFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    public function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        $this->createMany(100, 'task', function (int $i) {
            $task = new Task();
            $task->setTitle($this->faker->sentence);
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
            $task->setStatus($this->faker->boolean);

            $category = $this->getRandomReference('category', Category::class);
            $task->setCategory($category);

            $author = $this->getRandomReference('user', User::class);
            $task->setAuthor($author);

            return $task;
        });
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            UserFixtures::class,
        ];
    }
}