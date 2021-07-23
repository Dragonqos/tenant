<?php declare(strict_types=1);

namespace App\TenantBundle\Command;

use App\TenantBundle\Factory\ResourceAbstractFactory;
use App\TenantBundle\Engine\CommandRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TenantCreateCommand
 * @package App\TenantBundle\Command
 */
class TenantCreateCommand extends Command
{
    private ResourceAbstractFactory $factory;
    private CommandRunner $commandRunner;
    private ?string $name;

    /**
     * TenantCreateCommand constructor.
     * @param ResourceAbstractFactory $factory
     * @param CommandRunner $commandRunner
     * @param string|null $name
     */
    public function __construct(
        ResourceAbstractFactory $factory,
        CommandRunner $commandRunner,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->factory = $factory;
        $this->commandRunner = $commandRunner;
        $this->name = $name;
    }

    protected function configure(): void
    {
        $this->setName('tenant:create')
            ->setDescription('Create new tenant database with schema')
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Email'
            )
            ->addArgument(
                'firstname',
                InputArgument::REQUIRED,
                'First name'
            );
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
        $normalizedData = [
            'email' => $input->getArgument('email'),
            'firstname' => $input->getArgument('firstname')
        ];
    
        $tenant = $this->factory->createTenantFactory()->withData($normalizedData)->createNew();
    
        $databaseExitCode = $this->commandRunner->runCommand('doctrine:database:create', sprintf('--tenant=%s --if-not-exists', $tenant->getId()));
        if ($databaseExitCode !== 0) {
            $output->writeln('<bg=red;options=bold>Can\'t create database. The command has exited with code %d</>', $databaseExitCode);
        }
        
        $schemaExitCode = $this->commandRunner->runCommand('doctrine:migrations:migrate', sprintf('-n --tenant=%s', $tenant->getId()));
        if ($schemaExitCode !== 0) {
            $output->writeln('<bg=red;options=bold>Can\'t apply schema. The command has exited with code %d</>', $schemaExitCode);
        }
    
        $output->writeln('<info>Completed</info>');
        return 0;
    }
}