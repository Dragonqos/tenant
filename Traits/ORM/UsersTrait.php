<?php declare(strict_types=1);

namespace App\TenantBundle\Traits\ORM;

use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Interfaces\TenantUserInterface;
use App\TenantBundle\Interfaces\TenantInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Trait SettingsTrait
 * @package App\TenantBundle\Traits\ORM
 */
trait UsersTrait
{
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="TenantUser", mappedBy="tenant", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    protected ArrayCollection $users;

    /**
     * @param TenantUserInterface $user
     * @return $this|Tenant|TenantInterface
     */
    public function addUser(TenantUserInterface $user): self
    {
        if(!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    /**
     * @param TenantUserInterface $user
     * @return $this
     */
    public function removeUser(TenantUserInterface $user): self
    {
        if($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }
}