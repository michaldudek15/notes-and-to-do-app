<?php
/**
 * Note fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Enum\NoteStatus;
use App\Entity\Tag;
use App\Entity\Note;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class NoteFixtures.
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
        if (null === $this->manager || null === $this->faker) {
            return;
        }

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
                /*
                    @var Category $category
                */
                $category = $this->getRandomReference('categories');
                $note->setCategory($category);

                for ($i = 0; $i < 3; ++$i) {
                    $tag = $this->getRandomReference('tags');
                    $note->addTag($tag);
                }

                return $note;
            }
        );

        $this->manager->flush();

    }//end loadData()


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

    }//end getDependencies()


}//end class
