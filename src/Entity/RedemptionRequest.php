<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RedemptionRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private $user;

    #[ORM\ManyToOne(targetEntity: Reward::class)]
    private $reward;

    #[ORM\Column(type: 'datetime')]
    private $requestDate;

    #[ORM\Column(type: 'string', length: 20)]
    private $status; // 'pending', 'approved', 'rejected'
}