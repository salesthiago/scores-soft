<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 11, unique: true)]
    private $phone;
    
    #[ORM\Column(type: 'string')]
    private $name;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'integer')]
    private $points = 0;
    
    #[ORM\Column(type: 'integer')]
    private $status = 1;

    // Getters e Setters básicos
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    // Método exigido por UserInterface
    public function getUserIdentifier(): string
    {
        return (string) $this->phone;
    }

    // Método exigido por UserInterface
    public function getRoles(): array
    {
        $roles = $this->roles;
        if (empty($roles)) {
            $roles = ['ROLE_USER'];
        }
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Se você armazena dados temporários sensíveis em seu objeto de usuário,
        // limpe-os aqui
        // $this->plainPassword = null;
    }

    public function getPoints(): ?int
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

    public function subtractPoints(int $points): self
    {
        $this->points -= $points;
        return $this;
    }

    public function getStatus(): ?string
    {
        //$status = $this->status > 0 ? 'Ativo' : 'Inativo';
        return $this->status;
    }
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'phone' => $this->phone,
            'status' => $this->status,
            'points' => $this->points,
            'roles' => $this->roles
        ];
    }
}