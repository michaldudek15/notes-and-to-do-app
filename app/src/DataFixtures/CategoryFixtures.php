<?php
/**
 * Category fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

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
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        $this->createMany(
            20,
            'categories',
            function (int $i) {
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
                $author = $this->getRandomReference('users');
                $category->setAuthor($author);

                return $category;
            }
        );

        $this->manager->flush();

    }//end loadData()


    public function getDependencies(): array
    {
        return [UserFixtures::class];

    }//end getDependencies()


}//end class
