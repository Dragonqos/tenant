<?php declare(strict_types=1);

namespace App\TenantBundle\Exceptions;

/**
 * Class TenantLoadingException
 * @package App\TenantBundle\Exceptions
 */
class TenantLoadingException extends \Exception
{
    public static function tenantNotFoundException($message = 'Tenant not found exception')
    {
        return new static($message);
    }

    public static function invalidTenantIdentifier($message = 'Invalid tenant identifier')
    {
        return new static($message);
    }

    public static function tenantIdentifierNotFound($message = 'Tenant identifier not found')
    {
        return new static($message);
    }
}