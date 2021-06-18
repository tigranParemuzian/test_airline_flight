<?php
/**
 * Created by PhpStorm.
 * User: tigran
 * Date: 8/17/17
 * Time: 5:48 PM
 */

namespace App\Traits;

use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait GedmoSlugable
{

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=190)
     * @Groups({"object-name"})
     * @Assert\NotNull(message="object Name cannot be null", groups={"Create"})
     */
    private $name;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", type="string", length=190, unique=true)
     * @Groups({"object-slug", "industry:list"})
     */
    private $slug;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

}
