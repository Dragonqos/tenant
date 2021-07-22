<?php declare(strict_types=1);

namespace App\TenantBundle\Command;

use App\TenantBundle\Interfaces\TenantInterface;
use App\TenantBundle\Interfaces\TenantProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TenantCreateCommand
 * @package App\TenantBundle\Command
 */
class TenantDisableCommand extends Command
{
    private TenantProviderInterface $provider;

    /**
     * TenantDisableCommand constructor.
     * @param TenantProviderInterface $provider
     * @param string|null $name
     */
    public function __construct(
        TenantProviderInterface $provider,
        ?string $name = null
    ) {
        $this->provider = $provider;
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('tenant:disable')
            ->addArgument(
                'tenant',
                InputArgument::REQUIRED,
                'Tenant id'
            )
            ->setDescription('Disable the tenant');
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

        /** @var TenantInterface $tenant */
        $tenant = $this->provider->findByIdOrName((string) $tenantIdent);
    
        if (!$tenant) {
            $output->writeln('<bg=red;options=bold>Tenant not found</>');
            return 1;
        }
        
        $tenant->setEnabled(false);
        $this->provider->save($tenant);
    
        $output->writeln('<info>Completed</info>');
        return 0;
    }
}