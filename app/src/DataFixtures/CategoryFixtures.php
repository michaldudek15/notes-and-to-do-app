<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

class CategoryFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    public function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        $this->createMany(20, 'category', function (int $i) {
            $category = new Category();
            $category->setTitle($this->faker->unique()->word);
            $category->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $category->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );

            $author = $this->getRandomReference('user', User::class);
            $category->setAuthor($author);

            return $category;
        });
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}