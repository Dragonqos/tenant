<?php declare(strict_types=1);

namespace App\TenantBundle\Engine;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class CommandRunner
 * @package Helix\CommandBundle\Engine
 */
class CommandRunner
{
    private KernelInterface $kernel;
    protected ?Application $application = null;
    protected ?CommandExecutor $commandExecutor = null;

    /**
     * CommandRunner constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param string $command
     * @param string|null $arguments
     * @param OutputInterface|null $output
     *
     * @return int
     * @throws \Exception
     */
    public function runCommand(string $command, string $arguments = null, OutputInterface $output = null): int
    {
        $dir = getcwd();

        if ($dir !== $this->kernel->getProjectDir()) {
            chdir($this->kernel->getProjectDir());
        }

        $commandExecutor = $this->getCommandExecutor($output);

        try {
            $commandExecutor->runCommand($command, $arguments);
            $result = $commandExecutor->getLastCommandExitCode();
        } catch (\RuntimeException $ex) {
            $result = $ex;
        }

        chdir($dir);

        return $result;
    }

    /**
     * @param OutputInterface|null $output
     * @return CommandExecutor
     */
    protected function getCommandExecutor(OutputInterface $output = null): CommandExecutor
    {
        if (!$this->commandExecutor) {
            $this->commandExecutor = new CommandExecutor(
                $this->getApplication(),
                $output
            );
        }

        return $this->commandExecutor;
    }

    /**
     * @return Application
     */
    protected function getApplication(): Application
    {
        if (!$this->application) {
            $this->application = new Application($this->kernel);
            $this->application->setAutoExit(false);
        }

        return $this->application;
    }
}