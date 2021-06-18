<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AddressRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Address
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"address:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"address:add", "address:read"})
     */
    private $lat;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"address:add", "address:read"})
     */
    private $log;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"address:add", "address:read", "address:address"})
     * @Assert\NotBlank(groups={"address-add"})
     * @Assert\NotNull(groups={"address-add"})
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"address:add", "address:read"})
     * @Assert\NotBlank(groups={"address-add"})
     * @Assert\NotNull(groups={"address-add"})
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"address:add", "address:read"})
     * @Assert\NotBlank(groups={"address-add"})
     * @Assert\NotNull(groups={"address-add"})
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"address:add", "address:read"})
     */
    private $zipCode;

    /**
     * @ORM\OneToMany(targetEntity=Flight::class, mappedBy="fromLocation")
     */
    private $flightsFrom;

    /**
     * @ORM\OneToMany(targetEntity=Flight::class, mappedBy="toLocation")
     */
    private $flightsTo;

    public function __clone()
    {
        $this->id = null;
        $this->city = null;
    }

    public function __toString()
    {
        return $this->id ? $this->address : 'New Address';
    }

    public function __construct()
    {
        $this->flightsFrom = new ArrayCollection();
        $this->flightsTo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function setLat(?string $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLog(): ?string
    {
        return $this->log;
    }

    public function setLog(?string $log): self
    {
        $this->log = $log;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * @return Collection|Flight[]
     */
    public function getFlightsFrom(): Collection
    {
        return $this->flightsFrom;
    }

    public function addFlightsFrom(Flight $flightsFrom): self
    {
        if (!$this->flightsFrom->contains($flightsFrom)) {
            $this->flightsFrom[] = $flightsFrom;
            $flightsFrom->setFromLocation($this);
        }

        return $this;
    }

    public function removeFlightsFrom(Flight $flightsFrom): self
    {
        if ($this->flightsFrom->removeElement($flightsFrom)) {
            // set the owning side to null (unless already changed)
            if ($flightsFrom->getFromLocation() === $this) {
                $flightsFrom->setFromLocation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Flight[]
     */
    public function getFlightsTo(): Collection
    {
        return $this->flightsTo;
    }

    public function addFlightsTo(Flight $flightsTo): self
    {
        if (!$this->flightsTo->contains($flightsTo)) {
            $this->flightsTo[] = $flightsTo;
            $flightsTo->setToLocation($this);
        }

        return $this;
    }

    public function removeFlightsTo(Flight $flightsTo): self
    {
        if ($this->flightsTo->removeElement($flightsTo)) {
            // set the owning side to null (unless already changed)
            if ($flightsTo->getToLocation() === $this) {
                $flightsTo->setToLocation(null);
            }
        }

        return $this;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setAddressWithPersist(){

        $this->setAddress(sprintf('US , %s, %s , %s', $this->getState(), $this->getCity(), $this->getZipCode()));
    }
}
