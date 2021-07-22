<?php declare(strict_types=1);

namespace App\TenantBundle\EventListener;

use App\TenantBundle\Interfaces\TenantProviderInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\TenantBundle\Component\TenantResolver;
use App\TenantBundle\Entity\Repository\TenantRepository;
use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Exceptions\TenantLoadingException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CommandSubscriber
 * @package App\TenantBundle\EventListener
 */
class CommandSubscriber implements EventSubscriberInterface
{
    const GUEST_TENANT_OPTION = 'guest';

    private TenantProviderInterface $provider;
    protected TenantResolver $tenantResolver;
    protected array $commands;

    /**
     * CommandSubscriber constructor.
     * @param TenantProviderInterface $provider
     * @param TenantResolver $tenantResolver
     * @param iterable $commands
     */
    public function __construct(TenantProviderInterface $provider, TenantResolver $tenantResolver, iterable $commands)
    {
        $this->provider = $provider;
        $this->tenantResolver = $tenantResolver;
        $this->commands = $commands;
    }

    /**
     * @param ConsoleCommandEvent $event
     *
     * @throws \Exception
     */
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();

        # Handle only allowed commands such as migrate, queue, user
        if (!$this->isProperCommand($command)) {
            if($command->getName() !== 'workflow:dump') {
                $event->getOutput()->writeln(
                    sprintf('<fg=green>Command "%s" does not require tenant option, continue...</>', $command->getName())
                );
            }
            return;
        }

        $this->addTenantOption($event);
        $input = $event->getInput();

        $tenantId = $input->getOption('tenant');

        if (null === $tenantId) {
            throw TenantLoadingException::tenantIdentifierNotFound(sprintf('Tenant identifier is empty, use "%s --tenant={id}" instead', $command->getName()));
        }

        if ($tenantId === self::GUEST_TENANT_OPTION) {
            $event->getOutput()->writeln(
                sprintf('<fg=yellow>Command "%s" run with "guest" tenant connection, continue...</>', $command->getName())
            );
            return;
        }

        /** @var Tenant $tenant */
        $tenant = $this->provider->findByIdOrName($tenantId);

        if (!$tenant instanceof Tenant) {
            throw TenantLoadingException::tenantNotFoundException(sprintf('Tenant "%s" id or name not found.', $tenantId));
        }

        $this->tenantResolver->useTenant($tenant, false);

        $event->getOutput()->writeln(
            sprintf('<fg=green>Tenant identified as "%s" loaded</>', $tenantId)
        );
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    private function addTenantOption(ConsoleCommandEvent $event): void
    {
        $definition = $event->getCommand()->getDefinition();
        $input = $event->getInput();

        $definition->addOption(
            new InputOption('tenant', null, InputOption::VALUE_OPTIONAL, 'Tenant identifier', null)
        );

        $input->bind($definition);
    }

    /**
     * @param Command $command
     * @return bool
     */
    private function isProperCommand(Command $command): bool
    {
        return in_array($command->getName(), $this->commands ?? [], true);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => ['onConsoleCommand', 10]
        ];
    }
}