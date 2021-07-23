<?php declare(strict_types=1);

namespace App\TenantBundle\Entity;

use App\TenantBundle\Interfaces\TenantUserInterface;
use App\TenantBundle\Interfaces\TenantInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

/**
 * We store every user of the system and the information about which tenant he belongs to.
 * User data in this global db are always synchronized to the tenant’s one.
 *
 * If a user gets created, modified or deleted on a tenant, he’s synchronized to the global one.
 * If he gets modified on the global, he’s synchronized to the tenant.
 *
 * Exept of tenant's users all emails and usernames in this tables are not unique.
 * We keep unique tenant_id and email
 *
 * @ORM\Entity()
 * @ORM\Table(name="tenant_users")
 * @ORM\HasLifecycleCallbacks()
 */
class TenantUser implements TenantUserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="uid")
     * @Groups({"tenant_get"})
     */
    protected $uid;

    /**
     * @var TenantInterface
     *
     * @ORM\ManyToOne(targetEntity="App\TenantBundle\Entity\Tenant", inversedBy="users", cascade={"persist"})
     * @Groups({"get"})
     * @ORM\JoinColumn(name="tenant", referencedColumnName="id", nullable=true)
     */
    protected $tenant;

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * @param int $uid
     */
    public function setUid(int $uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return TenantInterface
     */
    public function getTenant(): TenantInterface
    {
        return $this->tenant;
    }

    /**
     * @param TenantInterface $tenant
     * @return $this
     */
    public function setTenant(TenantInterface $tenant): self
    {
        $tenant->addUser($this);
        $this->tenant = $tenant;

        return $this;
    }
}