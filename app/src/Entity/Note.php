<?php

namespace App\Entity;

use App\Repository\NoteRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
#[ORM\Table(name: 'notes')]
class Note
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    /**
     * Category.
     *
     * @var Category
     */
    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;


    public function getId(): ?int
    {
        return $this->id;

    }//end getId()


    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }//end getCreatedAt()


    public function setCreatedAt(?DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }//end setCreatedAt()


    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;

    }//end getUpdatedAt()


    public function setUpdatedAt(?DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;

    }//end setUpdatedAt()


    public function getTitle(): ?string
    {
        return $this->title;
    }//end getTitle()


    public function setTitle(string $title): void
    {
        $this->title = $title;

    }//end setTitle()


    public function getContent(): ?string
    {
        return $this->content;

    }//end getContent()


    public function setContent(string $content): void
    {
        $this->content = $content;

    }//end setContent()

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }


}//end class
