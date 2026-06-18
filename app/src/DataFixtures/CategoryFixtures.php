<?php

/**
 * Category fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

/**
 * Class CategoryFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class CategoryFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
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
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        /** @var User[] $users */
        $users = $this->manager->getRepository(User::class)->findAll();
        $usersCount = count($users);
        $additionalCategoriesCount = 20;

        $this->createMany($usersCount + $additionalCategoriesCount, 'category', function (int $i) use ($users, $usersCount) {
            $category = new Category();
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

            if ($i < $usersCount) {
                $author = $users[$i];
                $category->setTitle(sprintf('default-%d', $author->getId()));
            } else {
                $author = $this->getRandomReference('user', User::class);
                $category->setTitle($this->faker->unique()->word);
            }
            $category->setAuthor($author);

            return $category;
        });
    }// end loadData()

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: UserFixtures::class}
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }// end getDependencies()
}// end class
