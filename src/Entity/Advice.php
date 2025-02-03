<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AdviceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AdviceRepository::class)]
class Advice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['advice:read', 'user:read', 'user:write'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::ARRAY)]
    #[Groups(['advice:read', 'advice:write', 'user:read', 'user:write'])]
    private array $month = [];

    #[ORM\Column(length: 255)]
    #[Groups(['advice:read', 'advice:write', 'user:read', 'user:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 5000)]
    #[Groups(['advice:read', 'advice:write', 'user:read', 'user:write'])]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'advices', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['advice:read', 'advice:write'])]
    private ?User $author = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMonth(): array
    {
        return $this->month;
    }

    public function setMonth(array $month): static
    {
        $this->month = $month;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }
}
