<?php declare(strict_types=1);

namespace App\TenantBundle\Traits\ORM;

use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Interfaces\TenantInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Trait TenantTrait
 * @package App\TenantBundle\Traits\ORM
 */
trait TenantTrait
{
    /**
     * @var \App\TenantBundle\Interfaces\TenantInterface
     *
     * @ORM\ManyToOne(targetEntity="App\TenantBundle\Entity\Tenant", inversedBy="users", cascade={"persist"})
     * @Groups({"tenant_get"})
     * @ORM\JoinColumn(name="tenant", referencedColumnName="id", nullable=true)
     */
    protected $tenant;

    /**
     * @return \App\TenantBundle\Entity\Tenant
     */
    public function getTenant(): TenantInterface
    {
        return $this->tenant;
    }

    /**
     * @param Tenant $tenant
     */
    public function setTenant(TenantInterface $tenant)
    {
        $tenant->addUser($this);
        $this->tenant = $tenant;
    }
}