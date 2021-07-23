<?php declare(strict_types=1);

namespace App\TenantBundle\Traits\ORM;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Trait ResourceTrait
 * @package App\TenantBundle\Traits\ORM
 */
trait ResourceTrait
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    protected $id;

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId(?int $id): self
    {
        if(null === $this->id) {
            $this->id = $id;
        }
        return $this;
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}