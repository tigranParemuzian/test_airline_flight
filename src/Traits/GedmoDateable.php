<?php
/**
 * Created by PhpStorm.
 * User: tigran
 * Date: 8/17/17
 * Time: 5:48 PM
 */

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

trait GedmoDateable
{

    /**
     * @var
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"object-created"})
     */
    private $created;

    /**
     * @var
     * @Gedmo\Timestampable(on="update")
     * @Groups({"object-updated"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @return mixed
     */
    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated(?\DateTimeInterface $created): self
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    /**
     * @param mixed $updated
     */
    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;
        return $this;
    }


}