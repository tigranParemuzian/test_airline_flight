<?php


namespace App\Model;


use App\Entity\Flight;

class NotificationModel
{

    /**
     * @var int
     */
    private $flight_id;

    /**
     * @var string
     */
    private $triggered_at;

    /**
     * @var string
     */
    private $event;

    /**
     * @var string
     */
    private $secret_key;

    public function __construct(Flight $flight)
    {
        $this->flight_id = $flight->getId();
        $this->triggered_at = (new \DateTime('now'))->getTimestamp();

        switch ($flight->getStatus()) {
            case Flight::IS_CLOSED:
                $this->event = 'flight_ticket_sales_completed';
                break;
            case Flight::IS_CANCELED:
                $this->event = 'flight_is_canceled';
                break;
            default:
                break;
        }

        $this->secret_key = md5($flight->getName());
    }

    public function toArray(){
        return get_object_vars($this);
    }

    /**
     * @return int
     */
    public function getFlightId(): int
    {
        return $this->flight_id;
    }

    /**
     * @return string
     */
    public function getTriggeredAt(): ?string
    {
        return $this->triggered_at;
    }

    /**
     * @return string
     */
    public function getEvent(): ?string
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getSecretKey(): ?string
    {
        return $this->secret_key;
    }
}