<?php

/**
 * Note entity.
 */

namespace App\Entity;

use App\Repository\NoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Note.
 */
#[ORM\Entity(repositoryClass: NoteRepository::class)]
#[ORM\Table(name: 'notes')]
class Note
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    /**
     * Category.
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
     */
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Type(User::class)]
    private ?User $author;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }// end __construct()

    /**
     * Getter for id
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }// end getId()

    /**
     * Getter for created at
     *
     * @return \DateTimeImmutable|null Created at
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }// end getCreatedAt()

    /**
     * Setter for created at
     *
     * @param \DateTimeImmutable|null $createdAt Created at
     *
     * @return void Void
     */
    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }// end setCreatedAt()

    /**
     * Getter for updated at
     *
     * @return \DateTimeImmutable|null Updated at
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }// end getUpdatedAt()

    /**
     * Setter for updated at
     *
     * @param \DateTimeImmutable|null $updatedAt Updated at
     *
     * @return void Void
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }// end setUpdatedAt()

    /**
     * Getter for title
     *
     * @return string|null Title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }// end getTitle()

    /**
     * Setter for title
     *
     * @param string $title Title
     *
     * @return void Void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }// end setTitle()

    /**
     * Getter for content
     *
     * @return string|null Content
     */
    public function getContent(): ?string
    {
        return $this->content;
    }// end getContent()

    /**
     * Setter for content
     *
     * @param string $content Content
     *
     * @return void Void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }// end setContent()

    /**
     * Getter for category
     *
     * @return Category|null Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }// end getCategory()

    /**
     * Setter for category
     *
     * @param ?Category $category Category
     *
     * @return $this Static
     */
    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }// end setCategory()

    /**
     * Getter for tags
     *
     * @return Collection<int, Tag> Tags
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }// end getTags()

    /**
     * Add tag
     *
     * @param Tag $tag Tag
     *
     * @return void Void
     */
    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }// end addTag()

    /**
     * Remove tag
     *
     * @param Tag $tag Tag
     *
     * @return void Void
     */
    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }// end removeTag()

    /**
     * Getter for author
     *
     * @return User|null Author
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }// end getAuthor()

    /**
     * Setter for author
     *
     * @param User $author User
     *
     * @return $this Static
     */
    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }// end setAuthor()
}// end class
