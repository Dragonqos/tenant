<?php declare(strict_types=1);

namespace App\TenantBundle\SwitchHandlers;

use App\TenantBundle\TenantInterface;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\Persistence\ManagerRegistry;
use App\TenantBundle\Doctrine\ConnectionWrapper;
use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Interfaces\TenantSwitchHandlerInterface;

/**
 * Class MysqlConnectionHandler
 * @package App\TenantBundle\SwitchHandlers
 */
class MysqlConnectionHandler implements TenantSwitchHandlerInterface
{
    private ManagerRegistry $managerRegistry;
    private iterable $defaultConfiguration = [];

    /**
     * MysqlConnectionHandler constructor.
     *
     * @param ManagerRegistry $managerRegistry
     * @param string          $host
     * @param string          $user
     * @param null|string     $password
     * @param int|null        $port
     */
    public function __construct(ManagerRegistry $managerRegistry, string $host, string $user, ?string $password, ?int $port)
    {
        $this->managerRegistry = $managerRegistry;
        $this->defaultConfiguration = [
            'host' => $host,
            'port' => $port,
            'dbname' => 'guest',
            'user' => $user,
            'password' => $password
        ];
    }

    /**
     * @param TenantInterface $tenant
     */
    public function handle(TenantInterface $tenant): void
    {
        /** @var ConnectionWrapper $conn */
        foreach($this->managerRegistry->getConnections() as $connectionName => $conn) {

            if($conn instanceof ConnectionWrapper && $this->isHandling($tenant)) {
                $em = $this->managerRegistry->getManager($connectionName);

                $em->flush();
                $em->clear();

                $settings = $tenant->getSettings();
                $database = sprintf('t%s', $tenant->getId() ?? $this->defaultConfiguration['dbname']);
                $host = $settings->getDbHost() ?: $this->defaultConfiguration['host'];
                $username = $settings->getDbUsername() ?: $this->defaultConfiguration['user'];
                $password = $settings->getDbPassword() ?: $this->defaultConfiguration['password'];

                try {
                    $conn->forceSwitch(
                        $host,
                        $database,
                        $username,
                        $password // ToDO: should be encoded
                    );
                } catch (ConnectionException $e) {
                    if (stripos($e->getMessage(), 'Unknown database')) {
                        $conn->forceSwitch(
                            $host,
                            $database,
                            $username,
                            $password, // ToDO: should be encoded
                            false
                        );
                    }
                }
            }
        }
    }

    /**
     * @param TenantInterface $tenant
     * @return bool
     */
    public function isHandling(TenantInterface $tenant): bool
    {
        return true;
    }
}