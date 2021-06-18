<?php

namespace App\Entity;

use App\Repository\FlightOrderRepository;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=FlightOrderRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"flight", "client"}, errorPath="flight")
 */
class FlightOrder
{
    const IS_BOOKED = 1 , IS_BOOK_CANCELED = 2, IS_PAID = 3, IS_RETURNED = 5, IS_ARCHIVED = 6;

    const FLIGHT_ORDER_LIST = [
        "flight:order:read",
        "flight:order:flight", "flight:read",
        "flight:from:address", "flight:to:address", "address:address",
        "flight:order:payment",
        "flight:order:client", "client:read"
    ];

    const FLIGHT_ORDER_PAYMENT_LIST = [
        "flight:order:read",
        "flight:order:flight", "flight:read",
        "flight:from:address", "flight:to:address", "address:address",
        "flight:order:payment", "payment:read",
        "flight:order:client", "client:read"
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"flight:order:read", "flight:order:edit", "flight:order:id"})
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Choice(choices={1, 2, 3, 4, 5},
     *     groups={"flight-order-add"},
     *     message="Plese select one of this values IS_BOOKED = 1 , IS_BOOK_CANCELED = 2, IS_PAID = 3, IS_RETURNED = 5")
     * @Groups({"flight:order:read"})
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Flight::class, inversedBy="flightOrders", cascade={"persist"})
     * @ORM\JoinColumn(name="flight_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"flight-order-add"}, message="the flight cannot be null")
     * @Assert\NotBlank(groups={"flight-order-add"}, message="the flight cannot be blank")
     * @Groups({"flight:order:flight"})
     */
    private $flight;

    /**
     * @var int
     * @SerializedName(serializedName="flight")
     * @SWG\Property(type="integer", description="flight Id")
     * @Groups({"flight:order:add"})
     */
    public $flightVirtual;

    /**
     * @ORM\OneToOne(targetEntity=Payment::class, cascade={"persist"})
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\Valid()
     * @Assert\NotNull(groups={"flight-order-paid"})
     * @Groups({"flight:order:payment"})
     */
    private $payment;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *     min="1",
     *     max="150",
     *     groups={"flight-order-add"}
     * )
     * @Assert\NotNull(groups={"flight-order-add"}, message="the flighe cannot be null")
     * @Assert\NotBlank(groups={"flight-order-add"}, message="the flighe cannot be blank")
     * @Groups({"flight:order:read","flight:order:edit", "flight:order:add"})
     */
    private $seatNumber;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="flightOrders", cascade={"persist"})
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\Valid()
     * @Assert\NotNull(groups={"flight-order-add"})
     * @Groups({"flight:order:client"})
     */
    private $client;

    public function __construct()
    {
        $this->status = self::IS_BOOKED;
    }

    public function __toString()
    {
        return $this->id ? $this->getShowInfo() : 'New Flight order';
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFlight(): ?Flight
    {
        return $this->flight;
    }

    public function setFlight(?Flight $flight): self
    {
        $this->flight = $flight;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    public function getSeatNumber(): ?int
    {
        return $this->seatNumber;
    }

    public function setSeatNumber(int $seatNumber): self
    {
        $this->seatNumber = $seatNumber;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getShowInfo(): string
    {

        return sprintf('%s %s', (string)$this->getFlight(), (string)$this->getClient());

    }

    /**
     * @param ExecutionContextInterface $context
     * @param $payload
     * @Assert\Callback(groups={"flight-order-add"})
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {

        $this->getFlight()->setAvailableSeats();

        if($this->getFlight()->getAvailableSeats() <= 0 && is_null($this->getId())) {

            $context->buildViolation(sprintf('The flight %s seats is full. Please choose another flight', (string)$this->getFlight()))
                ->atPath('flight')
                ->addViolation();
        };
    }

}
