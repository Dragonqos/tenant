<?php declare(strict_types=1);

namespace App\TenantBundle\Traits\ORM;

use App\TenantBundle\Interfaces\EnabledInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait EnabledTrait
 * @package App\TenantBundle\Traits\ORM
 */
trait EnabledTrait
{
    /**
     * @var bool $enabled
     *
     * @ORM\Column(type="boolean", options={"default"=0})
     * @Groups({"collection", "get", "post", "put"})
     */
    protected bool $enabled = true;

    /**
     * @return bool
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Alias
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->getEnabled();
    }

    /**
     * @param bool $isEnabled
     * @return $this
     */
    public function setEnabled(bool $isEnabled): self
    {
        $this->enabled = $isEnabled;
        return $this;
    }
}