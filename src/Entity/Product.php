<?php
namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[UniqueEntity(fields: ["name"], message: "This Product name is already in use.")]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['vat', 'product'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['vat', 'product'])]
    private ?string $name = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    #[Groups(['vat', 'product'])]
    private ?int $price = null;

    #[ORM\ManyToMany(targetEntity: Vat::class, inversedBy: "products", cascade: ['persist'])]
    #[ORM\JoinTable(name: "product_vat")]
    #[Groups(['product'])]
    private Collection $vats;

    #[ORM\Column]
    #[Groups(['vat', 'product'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['vat', 'product'])]
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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getVats(): Collection
    {
        return $this->vats;
    }

    public function addVat(Vat $vat): self
    {
        if (!$this->vats->contains($vat)) {
            $this->vats[] = $vat;
        }

        return $this;
    }

    public function removeVat(Vat $vat): self
    {
        $this->vats->removeElement($vat);
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