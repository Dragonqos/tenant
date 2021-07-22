<?php declare(strict_types=1);

namespace App\TenantBundle\Traits\ORM;

use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Interfaces\EnabledInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Trait SettingsTrait
 * @package App\TenantBundle\Traits\ORM
 */
trait SettingsTrait
{
    /**
     * @var array
     *
     * @ORM\OneToOne(type="array")
     * @Groups({"get", "post", "put"})
     */
    private array $settings;

    /**
     * @param array $settings
     * @return $this
     */
    public function setSettings(array $settings): self
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}