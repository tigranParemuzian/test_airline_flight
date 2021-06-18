<?php


namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait EnableAvailable
{

    public function __construct()
    {
        $this->enabled = true;
    }

    /**
     * @var
     * @ORM\Column(name="enabled", type="boolean", options={"default":true})
     * @Groups({"enabled"})
     *
     */
    private $enabled = true;

    /**
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param $enabled
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }
}
