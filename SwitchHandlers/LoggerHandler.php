<?php declare(strict_types=1);

namespace App\TenantBundle\SwitchHandlers;

use App\TenantBundle\Logger\TenantLoggerProcessor;
use App\TenantBundle\Interfaces\TenantSwitchHandlerInterface;
use App\TenantBundle\Interfaces\TenantInterface;

/**
 * Class LoggerHandler
 * @package App\TenantBundle\SwitchHandlers
 */
class LoggerHandler implements TenantSwitchHandlerInterface
{
    private TenantLoggerProcessor $loggerProcessor;
    
    /**
     * DirectoryHandler constructor.
     *
     * @param TenantLoggerProcessor $loggerProcessor
     */
    public function __construct(TenantLoggerProcessor $loggerProcessor)
    {
        $this->loggerProcessor = $loggerProcessor;
    }

    /**
     * @param TenantInterface $tenant
     */
    public function handle(TenantInterface $tenant): void
    {
        if($this->isHandling($tenant)) {
            $this->loggerProcessor->setTenant($tenant);
        }
    }

    /**
     * @param TenantInterface $tenant
     *
     * @return bool
     */
    public function isHandling(TenantInterface $tenant): bool
    {
        return true;
    }
}