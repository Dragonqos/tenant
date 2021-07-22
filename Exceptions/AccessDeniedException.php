<?php declare(strict_types=1);

namespace App\TenantBundle\Exceptions;

/**
 * Class AccessDeniedException
 * @package App\TenantBundle\Exceptions
 */
class AccessDeniedException extends \Exception
{
    /**
     * @param string $message
     * @return static
     */
    public static function tenantDisabled($message = 'Tenant has been disabled, or removed.')
    {
        return new static($message);
    }

}