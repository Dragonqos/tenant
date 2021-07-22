<?php declare(strict_types=1);

namespace App\TenantBundle\Entity;

use App\TenantBundle\Interfaces\TenantInterface;
use App\TenantBundle\Traits\ORM\EnabledTrait;
use App\TenantBundle\Traits\ORM\NameTrait;
use App\TenantBundle\Traits\ORM\ResourceTrait;
use App\TenantBundle\Traits\ORM\SettingsTrait;
use App\TenantBundle\Traits\ORM\UsersTrait;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A Tenant.
 *
 * @ORM\Entity()
 * @ORM\Table(name="tenant")
 * @UniqueEntity("name")
 */
class Tenant implements TenantInterface
{
    use ResourceTrait, NameTrait, EnabledTrait, SettingsTrait, UsersTrait;


    public function __construct()
    {
        $this->users = new ArrayCollection();
    }
}