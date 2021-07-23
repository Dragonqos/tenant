<?php declare(strict_types=1);

namespace App\TenantBundle\Manager;

use App\TenantBundle\Factory\ResourceAbstractFactory;
use Doctrine\Persistence\ManagerRegistry;
use App\TenantBundle\Engine\CommandRunner;
use App\TenantBundle\Component\TenantResolver;
use App\TenantBundle\Repository\TenantInstallRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class TenantRegisterManager
 * @package App\TenantBundle\Manager
 */
class TenantInstallManager
{
    const TENANT_CREATED = 10;
    const DATABASE_CREATED = 20;
    const SCHEMA_CREATED = 150;
    const OWNER_CREATED = 200;
    const COMPLETED = 255;

    private TenantInstallRepository $repository;
    private TenantResolver $resolver;
    private array $installationState = [];
    private CommandRunner $commandRunner;
    private ManagerRegistry $managerRegistry;
    private ResourceAbstractFactory $factory;

    /**
     * TenantInstallManager constructor.
     * @param TenantInstallRepository $repository
     * @param TenantResolver $resolver
     * @param ResourceAbstractFactory $factory
     * @param CommandRunner $commandRunner
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(
        TenantInstallRepository $repository,
        TenantResolver $resolver,
        ResourceAbstractFactory $factory,
        CommandRunner $commandRunner,
        ManagerRegistry $managerRegistry
    )
    {
        $this->repository = $repository;
        $this->resolver = $resolver;
        $this->commandRunner = $commandRunner;
        $this->managerRegistry = $managerRegistry;
        $this->factory = $factory;
    }

    /**
     * @param string $uuid
     *
     * @return bool
     * @throws \Doctrine\ODM\MongoDB\DocumentNotFoundException
     */
    public function process(string $uuid): bool
    {
        $this->installationState = $this->repository->retrieve($uuid);
        $actions = ['createTenant', 'createDatabase', 'createSchema', 'createOwner', 'complete'];

        try {
            foreach ($actions as $action) {
                if(method_exists($this, $action)) {
                    $isComplete = $this->{$action}();

                    if(!$isComplete) {
                        break;
                    }
                }
            }
        } catch(\Throwable $exception) {
            $this->repository->putErrorMessage($uuid, $exception->getMessage());
            return false;
        }

        return $this->installationState[TenantInstallRepository::FIELD_STATE] === self::COMPLETED;
    }

    /**
     * @return bool
     * @throws \App\TenantBundle\Exceptions\AccessDeniedException
     * @throws \App\TenantBundle\Exceptions\TenantLoadingException
     */
    private function createTenant(): bool
    {
        $currentState = $this->installationState[TenantInstallRepository::FIELD_STATE];
        $tenantId = $this->installationState[TenantInstallRepository::FIELD_TENANT] ?? null;

        if($tenantId > 0) {
            $this->resolver->useTenant($tenantId);
        }

        if($currentState < self::TENANT_CREATED) {
            $uuid = $this->installationState[TenantInstallRepository::FIELD_UUID];
            $normalizedData = $this->installationState[TenantInstallRepository::FIELD_NORMALIZED_DATA];

            $tenant = $this->factory->createTenantFactory()->withData($normalizedData)->createNew();

            $this->repository->changeTenant($uuid, $tenant->getId());
            $this->repository->changeState($uuid, self::TENANT_CREATED, 'Account created');

            $this->resolver->useTenant($tenant->getId());
            $this->installationState = $this->repository->retrieve($uuid);
        }

        return true;
    }

    /**
     * @throws \Doctrine\ODM\MongoDB\DocumentNotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \App\TenantBundle\Exceptions\TenantLoadingException
     */
    private function createDatabase(): bool
    {
        $uuid = $this->installationState[TenantInstallRepository::FIELD_UUID];
        $currentState = $this->installationState[TenantInstallRepository::FIELD_STATE];
        $tenantId = $this->installationState[TenantInstallRepository::FIELD_TENANT] ?? null;

        if($tenantId > 0 && $currentState < self::DATABASE_CREATED) {
            $exitCode = $this->commandRunner->runCommand('doctrine:database:create', sprintf('--tenant=%s --if-not-exists', $tenantId));
            if ($exitCode !== 0) {
                $this->repository->putErrorMessage($uuid, 'Failed to create database for your company. Ask our support team to help you.');
                return false;
            }

            $this->repository->changeState($uuid, self::DATABASE_CREATED, 'Database created');
            $this->installationState = $this->repository->retrieve($uuid);
        }

        return true;
    }

    /**
     * @throws \Doctrine\ODM\MongoDB\DocumentNotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \App\TenantBundle\Exceptions\TenantLoadingException
     */
    private function createSchema(): bool
    {
        $uuid = $this->installationState[TenantInstallRepository::FIELD_UUID];
        $currentState = $this->installationState[TenantInstallRepository::FIELD_STATE];
        $tenantId = $this->installationState[TenantInstallRepository::FIELD_TENANT] ?? null;

        if($tenantId > 0 && $currentState < self::SCHEMA_CREATED) {

            $exitCode = $this->commandRunner->runCommand('doctrine:migrations:migrate', sprintf('-n --tenant=%s', $tenantId));
            if ($exitCode !== 0) {
                $this->repository->putErrorMessage($uuid, 'Failed to setup database schema for your company. Ask our support team to help you.');
                return false;
            }

            $this->repository->changeState($uuid, self::SCHEMA_CREATED, 'Installed database schema');
            $this->installationState = $this->repository->retrieve($uuid);
        }

        return true;
    }

    /**
     * @throws \Doctrine\ODM\MongoDB\DocumentNotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \App\TenantBundle\Exceptions\TenantLoadingException
     */
    private function createOwner(): bool
    {
        $uuid = $this->installationState[TenantInstallRepository::FIELD_UUID];
        $currentState = $this->installationState[TenantInstallRepository::FIELD_STATE];
        $normalizedData = $this->installationState[TenantInstallRepository::FIELD_NORMALIZED_DATA];

        if($currentState < self::OWNER_CREATED) {
            /** @var RoleRepository $roleRepo */
            $userRepo = $this->managerRegistry->getRepository(User::class);
            if (!$userRepo->hasUsers()) {
                $userRepo->createUser($normalizedData);
            }

            $this->repository->changeState($uuid, self::OWNER_CREATED, 'Default settings applied');
            $this->installationState = $this->repository->retrieve($uuid);
        }

        return true;
    }

    /**
     * @return bool
     * @throws \Doctrine\ODM\MongoDB\DocumentNotFoundException
     */
    private function complete(): bool
    {
        $uuid = $this->installationState[TenantInstallRepository::FIELD_UUID];
        $currentState = $this->installationState[TenantInstallRepository::FIELD_STATE];

        if($currentState < self::COMPLETED) {
            $this->repository->changeState($uuid, self::COMPLETED, 'Completed');
            $this->installationState = $this->repository->retrieve($uuid);
        }

        return true;
    }
}