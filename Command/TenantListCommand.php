<?php declare(strict_types=1);

namespace App\TenantBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use App\TenantBundle\Entity\Tenant;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TenantListCommand
 * @package App\TenantBundle\Command
 */
class TenantListCommand extends Command
{

    private ManagerRegistry $managerRegistry;

    /**
     * TenantListCommand constructor.
     * @param ManagerRegistry $managerRegistry
     * @param string|null $name
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        ?string $name = null
    ) {
        $this->managerRegistry = $managerRegistry;
        parent::__construct($name);
    }

    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setName('tenant:list')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Show deleted tenants')
            ->setDescription('Show available tenants');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $showDeleted = $input->getOption('all');
        
        $tenantCollection = $this->getManager(Tenant::class)
            ->getRepository(Tenant::class)
            ->findAll($showDeleted);
        
        $collection = array_map(function (Tenant $value) {
            $out[] = $value->getId();
            $out[] = $value->getName();
            $out[] = $value->getOrganization()->getEmail();
            $out[] = $value->isEnabled() ? 'true' : 'false';
            $out[] = $value->isDeleted() ? 'true' : 'false';
            
            return $out;
        }, $tenantCollection);
        
        $table = new Table($output);
        
        $table->setHeaders(['ID', 'Name', 'Email', 'Is Active', 'Is deleted'])
            ->setRows($collection);
        
        $table->render();
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