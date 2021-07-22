<?php declare(strict_types=1);

namespace App\TenantBundle\Provider;

use App\TenantBundle\Interfaces\TenantProviderInterface;
use App\TenantBundle\Interfaces\TenantInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;

/**
 * Class TenantProvider
 * @package App\TenantBundle\Provider
 */
class TenantProvider implements TenantProviderInterface
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
     * @return iterable
     */
    public function findAll(): iterable
    {
        return $this->getRepository()->findAll();
    }


    /**
     * @param string $tenantIdentifier
     * @return TenantInterface|null
     */
    public function findByIdOrName(string $tenantIdentifier): ?TenantInterface
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

    /**
     * @param TenantInterface $tenant
     * @return bool
     */
    public function remove(TenantInterface $tenant): bool
    {
        $repo = $this->getRepository();
        $repo->remove($tenant);
        $repo->flush();

        return true;
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

}