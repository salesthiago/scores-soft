<?php
// src/Entity/UserBalance.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Exception;

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

    #[ORM\Column]
    private int $reservedPoints = 0;

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

    public function getReservedPoints(): int
    {
        return $this->reservedPoints;
    }

    public function getAvailablePoints(): int
    {
        return $this->points - $this->reservedPoints;
    }

    public function reservePoints(int $points): self
    {
        if ($this->getAvailablePoints() < $points) {
            throw new Exception(
                'Saldo insuficiente para reserva. Disponível: ' . 
                $this->getAvailablePoints() . 
                ', Solicitado: ' . $points
            );
        }
        
        $this->reservedPoints += $points;
        return $this;
    }

    public function unreservePoints(int $points): self
    {
        if ($this->reservedPoints < $points) {
            throw new \InvalidArgumentException(
                'Tentativa de liberar mais pontos do que está reservado'
            );
        }
        
        $this->reservedPoints -= $points;
        return $this;
    }

    public function deductPoints(int $points): self
    {
        if ($this->points < $points) {
            throw new Exception(
                'Saldo insuficiente para dedução. Atual: ' . 
                $this->points . 
                ', Solicitado: ' . $points
            );
        }
        
        if ($this->reservedPoints < $points) {
            throw new \InvalidArgumentException(
                'Tentativa de deduzir pontos não reservados'
            );
        }
        
        $this->points -= $points;
        $this->reservedPoints -= $points;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'points' => $this->getPoints(),
            'reserved_points' => $this->getReservedPoints(),
            'available_points' => $this->getAvailablePoints(),
            'remaining_value' => $this->getRemainingValue(),
        ];
    }
    
}