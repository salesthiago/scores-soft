<?php
// src/Entity/UserBalance.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserBalance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column]
    private float $remainingValue = 0.0;

    #[ORM\Column]
    private int $points = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getRemainingValue(): float
    {
        return $this->remainingValue;
    }

    public function setRemainingValue(float $value): self
    {
        $this->remainingValue = $value;
        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;
        return $this;
    }

    public function addPoints(int $points): self
    {
        $this->points += $points;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'points' => $this->getPoints(),
            'remaining_value' => $this->getRemainingValue()
        ];
    }
}