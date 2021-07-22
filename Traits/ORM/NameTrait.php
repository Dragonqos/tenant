<?php declare(strict_types=1);

namespace App\TenantBundle\Traits\ORM;

use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Interfaces\EnabledInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ResourceTrait
 * @package Helix\CoreBundle\Mixins
 */
trait NameTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     * @Assert\Length(min=3, minMessage="Name must be at least 3 characters")
     *
     * @Groups({"collection", "get", "post"})
     */
    private string $name;

    /**
     * Sets name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}