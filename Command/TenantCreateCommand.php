<?php declare(strict_types=1);

namespace App\TenantBundle\Command;

use Helix\CommandBundle\Engine\CommandRunner;
use App\TenantBundle\Factory\TenantFactory;
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
    private TenantFactory $factory;
    private CommandRunner $commandRunner;

    /**
     * TenantCreateCommand constructor.
     *
     * @param TenantFactory $factory
     * @param CommandRunner $commandRunner
     * @param null|string $name
     */
    public function __construct(
        TenantFactory $factory,
        CommandRunner $commandRunner,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->factory = $factory;
        $this->commandRunner = $commandRunner;
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
                'company',
                InputArgument::REQUIRED,
                'Company name'
            )
            ->addArgument(
                'firstname',
                InputArgument::REQUIRED,
                'First name'
            )
            ->addArgument(
                'lastname',
                InputArgument::REQUIRED,
                'Last name'
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
            'company' => $input->getArgument('company'),
            'firstname' => $input->getArgument('firstname'),
            'lastname' => $input->getArgument('lastname'),
        ];
    
        $tenant = $this->factory->withData($normalizedData)->createNew();
    
        $databaseExitCode = $this->commandRunner->runCommand('doctrine:database:create', sprintf('--tenant=%s --if-not-exists', $tenant->getId()));
        if ($databaseExitCode !== 0) {
            $output->writeln('<bg=red;options=bold>Can\'t create database. The command has exited with code %d</>', $databaseExitCode);
        }
        
        $schemaExitCode = $this->commandRunner->runCommand('helix:migrations:migrate', sprintf('-n --tenant=%s', $tenant->getId()));
        if ($schemaExitCode !== 0) {
            $output->writeln('<bg=red;options=bold>Can\'t apply schema. The command has exited with code %d</>', $schemaExitCode);
        }
    
        $output->writeln('<info>Completed</info>');
        return 0;
    }
}