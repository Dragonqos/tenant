<?php declare(strict_types=1);

namespace App\TenantBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry as MongoManagerRegitry;
use Doctrine\Persistence\ObjectManager;
use Helix\CommandBundle\Engine\CommandRunner;
use Helix\SuperuserBundle\Entity\Superuser;
use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Entity\TenantUser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TenantCreateCommand
 * @package App\TenantBundle\Command
 */
class TenantClearCommand extends Command
{
    private ManagerRegistry $managerRegistry;
    private MongoManagerRegitry $mongoManagerRegistry;
    private CommandRunner $commandRunner;

    /**
     * TenantClearCommand constructor.
     * @param ManagerRegistry $managerRegistry
     * @param MongoManagerRegitry $mongoManagerRegistry
     * @param CommandRunner $commandRunner
     * @param string|null $name
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        MongoManagerRegitry $mongoManagerRegistry,
        CommandRunner $commandRunner,
        ?string $name = null
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->mongoManagerRegistry = $mongoManagerRegistry;
        $this->commandRunner = $commandRunner;
        parent::__construct($name);
    }

    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setName('tenant:clear')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'remove all tenants marked as deleted')
            ->setDescription('Will permanently remove tenants which are marked as deleted more than one month.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = $input->getOption('force');

        $tenantsToRemove = $this->getManager(Tenant::class)
            ->getRepository(Tenant::class)
            ->findSoftDeleted($force);

        foreach ($tenantsToRemove as $tenant) {
            $this->removeTenant($tenant, $output);
        }
        
        $output->writeln('<info>Completed</info>');
        return 0;
    }

    /**
     * @param Tenant $tenant
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function removeTenant(Tenant $tenant, OutputInterface $output): void
    {
        $databaseExitCode = $this->commandRunner->runCommand(
            'doctrine:database:drop',
            sprintf('--tenant=%s --if-exists --force', $tenant->getId())
        );
        if ($databaseExitCode !== 0) {
            $output->writeln(sprintf('<bg=red;options=bold>Can\'t remove mysql database. The command terminated with an exit code: %d</>', $databaseExitCode));
        }

        try {
            $this->commandRunner->runCommand(
                'doctrine:mongodb:schema:drop',
                sprintf('--tenant=%s', $tenant->getId())
            );
        } catch (\Throwable $e) {}

        $this->getManager(Superuser::class)
            ->getRepository(Superuser::class)
            ->moveUsersFromDeletingTenant($tenant->getId());
        
        $em = $this->getManager(Tenant::class);
        $em->remove($tenant);
    
        $this->getManager(TenantUser::class)
            ->getRepository(TenantUser::class)
            ->removeByTenant($tenant);
        
        $em->flush();
    }
    
    /**
     * @param string $className
     *
     * @return ObjectManager
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \LogicException
     */
    private function getManager(string $className): ObjectManager
    {
        $manager = $this->managerRegistry->getManagerForClass($className);
        
        if (!$manager instanceof ObjectManager) {
            throw new \LogicException('');
        }
        
        return $manager;
    }
}