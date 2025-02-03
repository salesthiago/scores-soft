<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Rule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private int $amount_min;

    #[ORM\Column(type: 'integer')]
    private int $amount_max;

    #[ORM\Column(type: 'integer')]
    private $score;

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

    public function getAmount_min()
    {
        return $this->amount_min;
    }

    public function getAmount_max()
    {
        return $this->amount_max;
    }

    public function getScore()
    {
        return $this->score;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'amount_min' => $this->amount_min,
            'amount_max' => $this->amount_max,
            'score' => $this->score
        ];
    }
}