<?php declare(strict_types=1);

namespace App\TenantBundle;

use App\TenantBundle\Interfaces\EnabledInterface;
use App\TenantBundle\Interfaces\NameInterface;
use App\TenantBundle\Interfaces\ResourceInterface;
use App\TenantBundle\Interfaces\SettingsInterface;

/**
 * Interface TenantInterface
 * @package App\TenantBundle
 */
interface TenantInterface extends ResourceInterface, NameInterface, EnabledInterface, SettingsInterface {


}