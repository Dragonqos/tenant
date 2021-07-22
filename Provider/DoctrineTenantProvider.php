<?php declare(strict_types=1);

namespace App\TenantBundle\Provider;

use App\TenantBundle\Interfaces\TenantProviderInterface;
use App\TenantBundle\TenantInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;

/**
 * Class DoctrineTenantProvider
 * @package App\TenantBundle\Provider
 */
class DoctrineTenantProvider implements TenantProviderInterface
{
    private ManagerRegistry $managerRegistry;
    private string $tableName;

    /**
     * DoctrineTenantProvider constructor.
     * @param ManagerRegistry $managerRegistry
     * @param string $tableName
     */
    public function __construct(ManagerRegistry $managerRegistry, string $tableName = 'tenant')
    {
        $this->managerRegistry = $managerRegistry;
        $this->tableName = $tableName;
    }

    /**
     * @param $tenantIdentifier
     * @return TenantInterface|null
     */
    public function findByIdOrName($tenantIdentifier): ?TenantInterface
    {
        $name = is_numeric($tenantIdentifier)
            ? (int) $tenantIdentifier
            : (string) $tenantIdentifier;

        return $this->getRepository()->createQueryBuilder($this->tableName)
            ->select($this->tableName)
            ->where($this->tableName . '.id = :id')
            ->orWhere($this->tableName . '.name = :name')
            ->setParameter('id', $name)
            ->setParameter('name', $name)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function getActiveTenants(): ?iterable
    {
        return $this->getRepository()->createQueryBuilder($this->tableName)
            ->select($this->tableName)
            ->where($this->tableName . '.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult();
        // TODO: Implement getActiveTenants() method.
    }

    /**
     * @return EntityRepository
     */
    private function getRepository(): EntityRepository
    {
        return $this->managerRegistry
            ->getManagerForClass(TenantInterface::class)
            ->getRepository(TenantInterface::class);
    }

    /**
     * @param TenantInterface $tenant
     * @return bool
     */
    public function save(TenantInterface $tenant): bool
    {
        $repo = $this->getRepository();
        $repo->persist($tenant);
        $repo->flush();

        return true;
    }
}