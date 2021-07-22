<?php declare(strict_types=1);

namespace App\TenantBundle\SwitchHandlers;

use App\TenantBundle\Interfaces\TenantSwitchHandlerInterface;
use App\TenantBundle\TenantInterface;
use Predis\Client;
use Predis\Command\Processor\KeyPrefixProcessor;
use Predis\Profile\ProfileInterface;
use Predis\Profile\RedisProfile;

/**
 * Class LoggerHandler
 * @package App\TenantBundle\SwitchHandlers
 */
class CacheHandler implements TenantSwitchHandlerInterface
{
    private const PATTERN = 't%d.cache:';

    private Client $predis;

    /**
     * CacheHandler constructor.
     *
     * @param Client $redisAdapter
     */
    public function __construct(Client $redisAdapter)
    {
        $this->predis = $redisAdapter;
    }

    /**
     * @param TenantInterface $tenant
     */
    public function handle(TenantInterface $tenant): void
    {
        if($this->isHandling($tenant)) {
            /** @var ProfileInterface|RedisProfile $profile */
            $profile = $this->predis->getProfile();

            if($profile instanceof RedisProfile) {
                $prefixer = new KeyPrefixProcessor(sprintf(self::PATTERN, $tenant->getId()));
                $profile->setProcessor($prefixer);
            }
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