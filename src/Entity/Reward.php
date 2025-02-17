<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Reward
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;
    
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;
    
    #[ORM\Column(type: 'string')]
    private string $status;

    #[ORM\Column(type: 'integer')]
    private int $pointsCost;

    public function getId()
    {
        return $this->id;
    }
    public function setName(string $value): void
    {
        $this->name = $value;
    }

    public function setImage(string $value): void
    {
        $this->image = $value;
    }

    public function setDescription(string $value): void
    {
        $this->description = $value;
    }

    public function setPointsCost(string $value): void
    {
        $this->pointsCost = $value;
    }

    public function setStatus(string $value): void
    {
        $this->status = $value;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPointsCost(): string
    {
        return $this->pointsCost;
    }
    
    public function getImage(): string
    {
        return $this->image;
    }

    public function isActive(): bool
    {
        if ($this->status === 'enabled') {
            return true;
        }
        return false;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'name' => $this->name,
            'description' => $this->description,
            'pointsCost' => $this->pointsCost,
            'image' => '/uploads/'.$this->image
        ];
    }
}