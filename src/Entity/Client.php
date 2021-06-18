<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"email"}, errorPath="email")
 */
class Client
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"client:read", "client:edit"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull(groups={"client-add"})
     * @Groups({"client:read", "client:add"})
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull(groups={"client-add"})
     * @Groups({"client:read", "client:add"})
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull(groups={"client-add"})
     * @Assert\Email(groups={"client-add"})
     * @Groups({"client:read", "client:add"})
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity=FlightOrder::class, mappedBy="client")
     */
    private $flightOrders;

    public function __toString()
    {
        return $this->id ? $this->getShowName() : 'New Client
        @Assert\NotNull(groups={"client-add"})';
    }

    public function __construct()
    {
        $this->flightOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection|FlightOrder[]
     */
    public function getFlightOrders(): Collection
    {
        return $this->flightOrders;
    }

    public function addFlightOrder(FlightOrder $flightOrder): self
    {
        if (!$this->flightOrders->contains($flightOrder)) {
            $this->flightOrders[] = $flightOrder;
            $flightOrder->setClient($this);
        }

        return $this;
    }

    public function removeFlightOrder(FlightOrder $flightOrder): self
    {
        if ($this->flightOrders->removeElement($flightOrder)) {
            // set the owning side to null (unless already changed)
            if ($flightOrder->getClient() === $this) {
                $flightOrder->setClient(null);
            }
        }

        return $this;
    }

    public function getShowName(): string
    {

        return sprintf('%s %s', $this->getFirstName(), $this->getLastName());

    }
}
