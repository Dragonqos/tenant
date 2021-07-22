<?php declare(strict_types=1);

namespace App\TenantBundle\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events;

/**
 * Class ConnectionWrapper
 * @package App\TenantBundle\Doctrine
 */
class ConnectionWrapper extends Connection
{
    private bool $isConnected = false;
    protected array $_params = [];

    /**
     * ConnectionWrapper constructor.
     *
     * @param array              $params
     * @param Driver             $driver
     * @param Configuration|null $config
     * @param EventManager|null  $eventManager
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(
        array $params, Driver $driver, Configuration $config = null, EventManager $eventManager = null
    )
    {
        $this->_params = $params;
        parent::__construct($params, $driver, $config, $eventManager);
    }

    /**
     * @param      $host
     * @param      $dbname
     * @param      $username
     * @param      $password
     * @param bool $connect
     */
    public function forceSwitch($host, $dbname, $username, $password, $connect = true)
    {
        if ($this->isConnected()) {
            $this->close();
        }
        $this->_params['host'] = $host;
        $this->_params['dbname'] = $dbname;
        $this->_params['user'] = $username;
        $this->_params['password'] = $password;

        if ($connect) {
            $this->connect();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        if ($this->isConnected()) {
            return true;
        }

        $this->_conn = $this->_driver->connect(
            $this->_params,
            $this->_params['user'],
            $this->_params['password'],
            $this->_params['driverOptions']
        );

        if ($this->_eventManager->hasListeners(Events::postConnect)) {
            $eventArgs = new ConnectionEventArgs($this);
            $this->_eventManager->dispatchEvent(Events::postConnect, $eventArgs);
        }

        $this->isConnected = true;
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isConnected()
    {
        return $this->isConnected;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        if ($this->isConnected()) {
            parent::close();
            $this->isConnected = false;
        }
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Checks mysql connection and reconnect if it is lost
     */
    public function reconnect(): void
    {
        // Checking connection this way because \Doctrine\DBAL\Connection::ping() is deprecated
        try {
            $this->fetchOne('SELECT 1;');
        } catch (\Throwable $exception) {
            $this->close();
            $this->connect();
        }
    }
}
