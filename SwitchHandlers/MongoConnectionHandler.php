<?php declare(strict_types=1);

namespace App\TenantBundle\SwitchHandlers;

use App\TenantBundle\Interfaces\TenantInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use App\TenantBundle\Interfaces\TenantSwitchHandlerInterface;

/**
 * Class MongoConnectionHandler
 * @package App\TenantBundle\SwitchHandlers
 */
class MongoConnectionHandler implements TenantSwitchHandlerInterface
{
    private ManagerRegistry $managerRegistry;
    private iterable $defaultConfiguration = [];

    /**
     * MongoConnectionHandler constructor.
     *
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param TenantInterface $tenant
     * @throws \ReflectionException
     */
    public function handle(TenantInterface $tenant): void
    {
        $defaultManagerName = $this->managerRegistry->getDefaultManagerName();

        if ($this->isHandling($tenant)) {

            /** @var DocumentManager $dm */
            $dm = $this->managerRegistry->getManager($defaultManagerName);
            $dm->flush(); // flush everything from previous connection
            $dm->clear(); // clear UOW schedules

            # we cannot simply change database = we hae to drop all caches of DM and UOW

            $database =sprintf('t%s', $tenant->getId() ?? $this->defaultConfiguration['dbname']);

            $configuration = $dm->getConfiguration();
            $configuration->setDefaultDB($database);

            $dm->getMetadataFactory()->setConfiguration($configuration);



            # reset caches of DocumentManager

            $reflection = new \ReflectionClass($dm);
            $propertyDatabases = $reflection
                ->getProperty('documentDatabases');
            $propertyDatabases->setAccessible(true);
            $propertyDatabases->setValue($dm, []);

            $propertyCollections = $reflection
                ->getProperty('documentCollections');
            $propertyCollections->setAccessible(true);
            $propertyCollections->setValue($dm, []);

            $propertyBuckets = $reflection
                ->getProperty('documentBuckets');
            $propertyBuckets->setAccessible(true);
            $propertyBuckets->setValue($dm, []);



            #reset caches UnitOfWork

            $uow = $dm->getUnitOfWork();
            $reflection = new \ReflectionClass($uow);

            $propertyPersisters = $reflection
                ->getProperty('persisters');
            $propertyPersisters->setAccessible(true);
            $propertyPersisters->setValue($uow, []);

            $propertyCollectionPersister = $reflection
                ->getProperty('collectionPersister');
            $propertyCollectionPersister->setAccessible(true);
            $propertyCollectionPersister->setValue($uow, null);

            # now client should be ready
        }
    }

    /**
     * @param TenantInterface $tenant
     *
     * @return bool
     */
    public function isHandling(TenantInterface $tenant): bool
    {
        return true;
    }
}