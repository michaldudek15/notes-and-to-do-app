<?php

namespace App\DataFixtures;

use App\Entity\Note;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;


class NoteFixtures extends AbstractBaseFixtures
{

    public function loadData(): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $note = new Note();
            $note->setTitle($this->faker->sentence);
            $note->setContent($this->faker->realText);
            $note->setCreatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '-1 days'))
            );
            $note->setUpdatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '-1 days'))
            );

            $this->manager->persist($note);
        }

        $this->manager->flush();
    }
}
