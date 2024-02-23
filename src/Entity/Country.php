<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['country', 'locale', 'product'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['country', 'locale', 'product'])]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Locale::class, inversedBy: 'countries')]
    #[Groups(['country', 'vat', 'product'])]
    private ?Locale $locale;

    #[ORM\OneToMany(targetEntity: Vat::class, mappedBy: 'country')]
    #[Groups(['country'])]
    private Collection $vats;

    #[ORM\Column]
    #[Groups(['country'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['country'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->vats = new ArrayCollection();
    }

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

    public function getLocale(): ?Locale
    {
        return $this->locale;
    }

    public function setLocale(?Locale $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getVats(): Collection
    {
        return $this->vats;
    }

    public function setVats(?Collection $vats): self
    {
        $this->vats = $vats;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
