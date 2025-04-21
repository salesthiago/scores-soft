<?php

namespace App\Entity;

use App\Repository\TransactionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionsRepository::class)]
class Transactions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "created_by", referencedColumnName: "id", nullable: true)]
    private ?User $creator = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $order_number = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $created_at = null;
    
    #[ORM\Column()]
    private bool $is_consumed = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getOrderNumber(): ?string
    {
        return $this->order_number;
    }

    public function setOrderNumber(?string $order_number): static
    {
        $this->order_number = $order_number;

        return $this;
    }
    
    public function setCreatedAt(): static
    {
        $this->created_at = date('Y-m-d H:i:s');

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function getIsConsumed(): bool
    {
        return $this->is_consumed;
    }

    public function setIsConsumed(bool $value): void
    {
        $this->is_consumed = $value;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user ? [
                'id' => $this->user->getId(),
                'name' => $this->user->getName(),
                'phone' => $this->user->getPhone()
            ] : null,
            'creator' => $this->creator ? [
                'id' => $this->creator->getId(),
                'name' => $this->creator->getName(),
                'phone' => $this->user->getPhone()
            ] : null,
            'is_consumed' => $this->is_consumed,
            'amount' => $this->amount,
            'order_number' => $this->order_number,
            'created_at' => date_format(date_create($this->created_at), 'd/m/Y H:i:s')
        ];
    }
}