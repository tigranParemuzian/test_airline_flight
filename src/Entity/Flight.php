<?php

namespace App\Entity;

use App\Interfaces\EnableInterface;
use App\Repository\FlightRepository;
use App\Traits\EnableAvailable;
use App\Traits\GedmoDateable;
use App\Traits\GedmoSlugable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=FlightRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Flight implements EnableInterface
{
    use GedmoDateable, GedmoSlugable, EnableAvailable;

    const IS_FREE = 1, IN_PROGRESS = 2, IS_RETURNED = 3, IS_WAITING = 4, IS_CANCELED = 5, IS_CLOSED = 6;


    /**
     * Form log create
     */
    const FLIGHT_LIST = [
        "flight:read",
        "flight:from:address", "flight:to:address", "address:read"
    ];


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"flight:read", "flight:edit", "flight:remove", "flight:id"})
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"flight:read", "flight:edit", "flight:parent"})
     *
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Address::class, inversedBy="flightsFrom", cascade={"persist"})
     * @ORM\JoinColumn(name="from_location", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\Valid()
     * @Assert\NotBlank(groups={"add-flight"})
     * @Assert\NotNull(groups={"add-flight"})
     * @Groups({"flight:from:address", "flight:add", "flight:edit"})
     *
     */
    private $fromLocation;

    /**
     * @ORM\ManyToOne(targetEntity=Address::class, inversedBy="flightsTo", cascade={"persist"})
     * @ORM\JoinColumn(name="to_location", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\Valid()
     * @Assert\NotBlank(groups={"add-flight"})
     * @Assert\NotNull(groups={"add-flight"})
     * @Groups({"flight:to:address", "flight:add", "flight:edit"})
     *
     */
    private $toLocation;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull(groups={"add-flight"})
     * @Groups({"flight:read", "flight:add", "flight:edit"})
     */
    private $startedAt;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull(groups={"add-flight"})
     * @Groups({"flight:read", "flight:add", "flight:edit"})
     */
    private $finishedAt;

    /**
     * @ORM\OneToMany(targetEntity=FlightOrder::class, mappedBy="flight")
     * @Groups({"flight:order"})
     */
    private $flightOrders;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *     min="1",
     *     max="150",
     *     groups={"add-flight"}
     * )
     * @Assert\NotNull()
     * @Groups({"flight:read", "flight:add", "flight:edit"})
     */
    private $seatCount;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\NotNull(groups={"add-flight"})
     * @Groups({"flight:read", "flight:add", "flight:edit"})
     */
    private $cost;

    public function __toString()
    {
        return $this->id ? $this->name : 'New Flight';
    }

    public function __construct()
    {
        $this->status = self::IS_FREE;
        $this->enabled = true;
        $this->flightOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getFromLocation(): ?Address
    {
        return $this->fromLocation;
    }

    public function setFromLocation(?Address $fromLocation): self
    {
        $this->fromLocation = $fromLocation;

        return $this;
    }

    public function getToLocation(): ?Address
    {
        return $this->toLocation;
    }

    public function setToLocation(?Address $toLocation): self
    {
        $this->toLocation = $toLocation;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeInterface $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

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
            $flightOrder->setFlight($this);
        }

        return $this;
    }

    public function removeFlightOrder(FlightOrder $flightOrder): self
    {
        if ($this->flightOrders->removeElement($flightOrder)) {
            // set the owning side to null (unless already changed)
            if ($flightOrder->getFlight() === $this) {
                $flightOrder->setFlight(null);
            }
        }

        return $this;
    }

    public function getSeatCount(): ?int
    {
        return $this->seatCount;
    }

    public function setSeatCount(?int $seatCount): self
    {
        $this->seatCount = $seatCount;

        return $this;
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

    /**
     * @param ExecutionContextInterface $context
     * @param $payload
     * @Assert\Callback(groups={"flight:add", "flight:edit"})
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        // somehow you have an array of "fake names"

        if ($this->startedAt->getTimestamp() >= $this->finishedAt->getTimestamp()) {

            $context->buildViolation("The startedAd value cannot be equal or high then finishedAt")
                ->atPath('startedAt')
                ->addViolation();
        }

    }

    private $availableSeats;

    /**
     * @return mixed
     */
    public function getAvailableSeats(): ?int
    {
        return $this->availableSeats;
    }

    /**
     * @param mixed $availableSeats
     */
    public function setAvailableSeats(): self
    {
        $this->availableSeats = $this->getSeatCount();

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->in('status', [FlightOrder::IS_PAID, FlightOrder::IS_BOOKED]));

        $data = $this->flightOrders->matching($criteria);

        if($data->isEmpty() === false) {

            $this->availableSeats = $this->availableSeats - $data->count();
        }

        return $this;
    }
}
