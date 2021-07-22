<?php declare(strict_types=1);

namespace App\TenantBundle\Logger;

use App\TenantBundle\Entity\Tenant;

/**
 * Class TenantLoggerProcessor
 * @package App\TenantBundle\Logger
 */
class TenantLoggerProcessor
{
    protected ?Tenant $tenant;
    
    public function setTenant(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }
    
    /**
     * @param array $record
     *
     * @return array
     */
    public function processRecord(array $record): array
    {
        if ($this->tenant) {
            $record['extra']['tenant_id'] = $this->tenant->getId();
            $record['extra']['tenant_name'] = $this->tenant->getName();
        }
        
        return $record;
    }
}