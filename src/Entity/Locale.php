<?php

namespace App\Entity;

use App\Repository\LocaleRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LocaleRepository::class)]
#[UniqueEntity(fields: ["iso_code"], message: "This ISO code is already in use.")]
class Locale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['locale', 'country'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['locale', 'country', 'vat', 'product'])]
    private ?string $name = null;

    #[ORM\Column(length: 5, unique: true)]
    #[Assert\NotBlank]
    #[Groups(['locale', 'country', 'vat', 'product'])]
    private ?string $iso_code = null;

    #[ORM\OneToMany(targetEntity: Country::class, mappedBy: 'locale')]
    #[Groups(['locale'])]
    private Collection $countries;

    #[ORM\Column]
    #[Groups(['locale'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['locale'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->countries = new ArrayCollection();
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

    public function getIsoCode(): ?string
    {
        return $this->iso_code;
    }

    public function setIsoCode(string $iso_code): static
    {
        $this->iso_code = $iso_code;

        return $this;
    }

    public function getCountries(): Collection
    {
        return $this->countries;
    }

    public function setCountries(?Collection $countries): self
    {
        $this->countries = $countries;
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
