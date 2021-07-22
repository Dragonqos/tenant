<?php declare(strict_types=1);

namespace App\TenantBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use App\TenantBundle\Entity\Tenant;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TenantCreateCommand
 * @package App\TenantBundle\Command
 */
class TenantEnableCommand extends Command
{
    private ManagerRegistry $managerRegistry;

    /**
     * TenantEnableCommand constructor.
     *
     * @param ManagerRegistry $managerRegistry
     * @param null|string $name
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        ?string $name = null
    ) {
        $this->managerRegistry = $managerRegistry;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('tenant:enable')
            ->addArgument(
                'tenant',
                InputArgument::REQUIRED,
                'Tenant id'
            )
            ->setDescription('Enable the tenant');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tenantIdent = $input->getArgument('tenant');
    
        $em = $this->getManager(Tenant::class);
    
        /** @var Tenant $tenant */
        $tenant = $em->getRepository(Tenant::class)->findByIdOrName($tenantIdent);
    
        if (!$tenant) {
            $output->writeln('<bg=red;options=bold>Tenant not found</>');
            return 1;
        }
        
        $tenant->enable();
        $em->flush();
    
        $output->writeln('<info>Completed</info>');
        return 0;
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