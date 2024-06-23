<?php

/**
 * Task entity.
 */

namespace App\Entity;

use App\Repository\TaskRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Task
 */
#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'tasks')]
class Task
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    private ?DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'boolean')]
    private ?string $status = null;

    /**
     * Category.
     *
     * @var Category
     */
    #[ORM\ManyToOne(targetEntity: Category::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class)]
    private Collection $tags;

    /**
     * Author.
     *
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Type(User::class)]
    private ?User $author;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }//end __construct()


    /**
     * @return integer|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }//end getId()


    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }//end getCreatedAt()


    /**
     * @param  DateTimeImmutable|null $createdAt
     *
     * @return void
     */
    public function setCreatedAt(?DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }//end setCreatedAt()


    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }//end getUpdatedAt()


    /**
     * @param  DateTimeImmutable|null $updatedAt
     *
     * @return void
     */
    public function setUpdatedAt(?DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }//end setUpdatedAt()


    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }//end getTitle()


    /**
     * @param  string $title
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }//end setTitle()


    /**
     * @return boolean|null
     */
    public function getStatus(): ?bool
    {
        return $this->status;
    }//end getStatus()


    /**
     * @param  boolean $status
     *
     *
     * @return void
     */
    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }//end setStatus()


    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }//end getCategory()


    /**
     * Setter for category
     *
     * @param Category|null $category
     *
     * @return $this
     */
    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }//end setCategory()


    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }//end getTags()


    /**
     * Add tag
     *
     * @param Tag $tag
     *
     * @return void
     */
    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }//end addTag()


    /**
     * Remove tag
     *
     * @param Tag $tag
     *
     * @return void
     */
    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }//end removeTag()


    /**
     * Getter for author
     *
     * @return User|null
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }//end getAuthor()


    /**
     * Setter for author
     *
     * @param User|null $author
     *
     * @return $this
     */
    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }//end setAuthor()
}//end class
