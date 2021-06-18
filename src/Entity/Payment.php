<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PaymentRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Payment
{
    const IS_NOT_PAID = 1, IS_PAID = 2, IS_RETURNED = 3;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"payment:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\NotNull(groups="payment-add")
     * @Groups({"payment:read", "payment:add"})
     */
    private $cost;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Choice(choices={1, 2, 3}, message="Plaese select valid value, vales is IS_NOT_PAID = 1, IS_PAID = 2, IS_RETURNED = 3")
     * @Assert\NotNull(groups="payment-add")
     * @Groups({"payment:read", "payment:add", "payment:edit"})
     */
    private $status;

    public function __construct()
    {
        $this->status = self::IS_NOT_PAID;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCost(): ?string
    {
        return $this->cost;
    }

    public function setCost(string $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
