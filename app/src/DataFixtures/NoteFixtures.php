<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Note;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class NoteFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    public function loadData(): void
    {
        if (!$this->manager instanceof \Doctrine\Persistence\ObjectManager || !$this->faker instanceof \Faker\Generator) {
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

                $category = $this->getRandomReference('category', Category::class);
                $note->setCategory($category);

                for ($j = 0; $j < 3; ++$j) {
                    $tag = $this->getRandomReference('tags', Tag::class);
                    $note->addTag($tag);
                }

                $author = $this->getRandomReference('user', User::class);
                $note->setAuthor($author);

                return $note;
            }
        );

        $this->manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            TagFixtures::class,
            UserFixtures::class,
        ];
    }
}