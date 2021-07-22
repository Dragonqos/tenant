<?php declare(strict_types=1);

namespace App\TenantBundle\Model;

use App\TenantBundle\Entity\Tenant;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiProperty;
use Helix\UserBundle\Models\Accessor;

/**
 * Class TenantUserModel
 * @package App\TenantBundle\Model
 */
class TenantUserModel implements UserInterface
{
    use Accessor\Id,
        Accessor\Username,
        Accessor\UsernameCanonical,
        Accessor\Email,
        Accessor\Password,
        Accessor\PlainPassword,
        Accessor\Salt,
        Accessor\Roles,
        Accessor\PasswordRequestedAt,
        Accessor\ConfirmationToken;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"tenant_get"})
     */
    protected $id;

    /**
     * @var Tenant
     *
     * @ORM\ManyToOne(targetEntity="App\TenantBundle\Entity\Tenant", inversedBy="users", cascade={"persist"})
     * @Groups({"tenant_get", "super_user_put"})
     * @ORM\JoinColumn(name="tenant", referencedColumnName="id", nullable=true)
     * @ApiProperty(iri="/Tenant")
     */
    protected $tenant;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     * @Groups({"tenant_get"})
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $usernameCanonical;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     * @Groups({"tenant_get"})
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $emailCanonical;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"tenant_get"})
     */
    protected $enabled;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $salt;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"tenant_get"})
     */
    protected $lastLogin;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $confirmationToken;

    /**
     * @var string
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * @return Tenant
     */
    public function getTenant(): Tenant
    {
        return $this->tenant;
    }

    /**
     * @param Tenant $tenant
     */
    public function setTenant(Tenant $tenant)
    {
        $tenant->addUser($this);
        $this->tenant = $tenant;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}