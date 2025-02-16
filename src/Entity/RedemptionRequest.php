<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity]
class RedemptionRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Reward::class)]
    #[ORM\JoinColumn(name: "reward_id", referencedColumnName: "id", nullable: true)]
    private ?Reward $reward = null;

    #[ORM\Column(type: 'datetime')]
    private DateTime $requestDate;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status; // 'pending', 'approved', 'rejected'

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getRequestDate(): DateTime
    {
        return $this->requestDate;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getReward(): Reward
    {
        return $this->reward;
    }

    public function setStatus(string $string): void
    {
        $this->status = $string;
    }

    public function setRequestDate(DateTime $date): void
    {
        $this->requestDate = $date;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function setReward(Reward $reward): void
    {
        $this->reward = $reward;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'status' => $this->getStatus(),
            'user' => $this->user->toArray(),
            'reward' => $this->reward->toArray(),
            'requestDate' =>  $this->requestDate->format('d/m/Y H:i:s')
        ];
    }
}