<?php declare(strict_types=1);

namespace App\TenantBundle\Engine;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CommandExecutor
 * @package CronBundle\Engine
 */
class CommandExecutor
{
    protected Application $consoleApp;
    protected ?OutputInterface $output = null;
    protected ?int $lastCommandExitCode = null;
    protected ?string $lastCommandLine = null;

    /**
     * CommandExecutor constructor.
     *
     * @param Application          $consoleApp
     * @param OutputInterface|null $output
     */
    public function __construct(
        Application $consoleApp,
        OutputInterface $output = null
    )
    {
        $this->consoleApp = $consoleApp;
        $this->output = $output;
    }

    /**
     * Launches a command.
     * If '--process-isolation' parameter is specified the command will be launched as a separate process.
     * In this case you can parameter '--process-timeout' to set the process timeout
     * in seconds. Default timeout is 300 seconds.
     * The '--process-timeout' parameter can be used to set the process timeout
     * in seconds. Default timeout is 300 seconds.
     * If '--ignore-errors' parameter is specified any errors are ignored;
     * otherwise, an exception is raises if an error happened.
     *
     * @param string $command
     * @param string $params
     *
     * @return CommandExecutor
     * @throws \RuntimeException if command failed and '--ignore-errors' parameter is not specified
     */
    public function runCommand(string $command, string $params = null)
    {
        $this->lastCommandLine = null;
        $this->lastCommandExitCode = null;
        $ignoreErrors = false;

        $stringInput = new StringInput($command . ' ' . $params);

        if ($stringInput->hasParameterOption('--ignore-errors')) {
            $ignoreErrors = true;
        }

        $this->consoleApp->setAutoExit(false);
        $this->lastCommandExitCode = $this->consoleApp->run($stringInput, $this->output);


        $this->processResult($ignoreErrors);
        return $this;
    }

    /**
     * Gets an exit code of last executed command
     *
     * @return int
     */
    public function getLastCommandExitCode()
    {
        return $this->lastCommandExitCode;
    }

    /**
     * @param bool $ignoreErrors
     *
     * @throws \RuntimeException
     */
    protected function processResult($ignoreErrors)
    {
        if (0 !== $this->lastCommandExitCode) {
            if ($ignoreErrors) {
                $this->output->writeln(
                    sprintf(
                        '<error>The command terminated with an exit code: %u.</error>',
                        $this->lastCommandExitCode
                    )
                );
            } else {
                throw new \RuntimeException(sprintf(
                    'The command %s terminated with an exit code: %u.',
                    $this->lastCommandLine,
                    $this->lastCommandExitCode
                ));
            }
        }
    }
}
