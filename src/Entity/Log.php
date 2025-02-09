<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Log
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string')]
    private string $action;

    #[ORM\Column(type: 'string')]
    private ?string $value;

    #[ORM\Column(type: 'string')]
    private ?string $description;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'description' => $this->description,
            'value' => $this->value
        ];
    }
}