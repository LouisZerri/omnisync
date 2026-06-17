<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ChannelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChannelRepository::class)]
#[UniqueEntity(fields: ['code'], message: 'Ce code de canal est déjà utilisé par un autre canal')]
#[ORM\UniqueConstraint(name: 'UNIQ_CHANNEL_CODE', fields: ['code'])]
class Channel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom du canal est obligatoire')]
    #[Assert\Length(max: 100, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères')]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le code du canal est obligatoire')]
    #[Assert\Length(max: 50, maxMessage: 'Le code ne peut pas dépasser {{ limit }} caractères')]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'URL de base du canal est obligatoire")]
    #[Assert\Url(message: "L'URL de base doit être une URL valide")]
    #[Assert\Length(max: 255, maxMessage: "L'URL ne peut pas dépasser {{ limit }} caractères")]
    private ?string $baseUrl = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La clé API du canal est obligatoire")]
    #[Assert\Length(max: 255, maxMessage: 'La clé API ne peut pas dépasser {{ limit }} caractères')]
    private ?string $apiKey = null;

    #[ORM\Column]
    private bool $isActive = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): static
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
}
