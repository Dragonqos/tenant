<?php declare(strict_types=1);

namespace App\TenantBundle\Provider\ORM;

use App\TenantBundle\Interfaces\TenantUserInterface;
use App\TenantBundle\Interfaces\TenantUserProviderInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;

/**
 * Class TenantUserProvider
 * @package App\TenantBundle\Provider\ORM
 */
class TenantUserProvider implements TenantUserProviderInterface
{
    private ManagerRegistry $managerRegistry;
    private string $tableName;

    /**
     * DoctrineTenantProvider constructor.
     * @param ManagerRegistry $managerRegistry
     * @param string $tableName
     */
    public function __construct(ManagerRegistry $managerRegistry, string $tableName = 'tenant_user')
    {
        $this->managerRegistry = $managerRegistry;
        $this->tableName = $tableName;
    }

    /**
     * @param string $identifier
     * @return TenantUserInterface|null
     */
    public function findOneByName(string $identifier): ?TenantUserInterface
    {
        return $this->getRepository()->findOneBy(['username' => $identifier]);
    }

    /**
     * @param string $identifier
     * @return TenantUserInterface|null
     */
    public function findOneByEmail(string $identifier): ?TenantUserInterface
    {
        return $this->findOneBy(['email' => $identifier]);
    }

    /**
     * @param string $identifier
     * @return TenantUserInterface|null
     */
    public function findOneByConfirmationToken(string $identifier): ?TenantUserInterface
    {
        return $this->getRepository()
            ->findOneBy(['confirmationToken' => $identifier]);
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function removeByTenant(string $identifier): bool
    {
        $this->getRepository()
            ->createQueryBuilder($this->tableName)
            ->delete(TenantUserInterface::class, $this->tableName)
            ->andWhere($this->tableName . '.tenant = :tenant')
            ->setParameter('tenant', $identifier)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param TenantUserInterface $tenantUser
     * @return bool
     */
    public function remove(TenantUserInterface $tenantUser): bool
    {
        $repo = $this->getRepository();
        $repo->remove($tenantUser);
        $repo->flush();

        return true;
    }

    /**
     * @param TenantUserInterface $tenantUser
     * @return bool
     */
    public function save(TenantUserInterface $tenantUser): bool
    {
        $repo = $this->getRepository();
        $repo->persist($tenantUser);
        $repo->flush();

        return true;
    }


    /**
     * @return EntityRepository
     */
    private function getRepository(): EntityRepository
    {
        return $this->managerRegistry
            ->getManagerForClass(TenantUserInterface::class)
            ->getRepository(TenantUserInterface::class);
    }
}