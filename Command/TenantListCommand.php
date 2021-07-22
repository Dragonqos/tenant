<?php declare(strict_types=1);

namespace App\TenantBundle\Command;

use App\TenantBundle\Interfaces\TenantInterface;
use App\TenantBundle\Interfaces\TenantProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TenantListCommand
 * @package App\TenantBundle\Command
 */
class TenantListCommand extends Command
{
    private TenantProviderInterface $provider;

    /**
     * TenantListCommand constructor.
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
     *
     */
    protected function configure(): void
    {
        $this->setName('tenant:list')
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
        $tenantCollection = $this->provider->findAll();

        $collection = array_map(function (TenantInterface $value) {
            $out[] = $value->getId();
            $out[] = $value->getName();
            $out[] = $value->isEnabled() ? 'true' : 'false';

            return $out;
        }, $tenantCollection);
        
        $table = new Table($output);
        $table->setHeaders(['ID', 'Name', 'Is Active'])
            ->setRows($collection);
        
        $table->render();
        return 0;
    }
}