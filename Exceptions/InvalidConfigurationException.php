<?php declare(strict_types=1);

namespace App\TenantBundle\Exceptions;

class InvalidConfigurationException extends \RuntimeException
{
    public static function databaseSettingsNotConfigured($message = 'Tenant does not have any settings configuration.')
    {
        return new static($message);
    }

}