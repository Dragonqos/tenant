<?php declare(strict_types=1);

namespace App\TenantBundle\Factory\ORM;

use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Interfaces\FactoryInterface;
use App\TenantBundle\Interfaces\TenantProviderInterface;
use App\TenantBundle\Interfaces\TenantInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TenantFactory
 * @package App\TenantBundle\Factory
 */
class TenantFactory implements FactoryInterface
{
    private TenantProviderInterface $provider;
    protected iterable $normalizedData = [];
    protected iterable $defaultConfiguration = [];

    /**
     * TenantFactory constructor.
     * @param TenantProviderInterface $provider
     * @param string $host
     * @param string $user
     * @param string|null $password
     * @param int|null $port
     */
    public function __construct(
        TenantProviderInterface $provider,
        string $host, string $user, ?string $password, ?int $port
    )
    {
        $this->provider = $provider;
        $this->defaultConfiguration = [
            'host' => $host,
            'port' => $port,
            'dbname' => 'guest',
            'user' => $user,
            'password' => $password
        ];
    }

    /**
     * @return TenantInterface
     */
    public function createNew(): TenantInterface
    {
        if (empty($this->normalizedData)) {
            throw new \LogicException('Tenant normalized data must be set first, before creating new tenant');
        }
        $configuration = array_merge($this->defaultConfiguration, array_filter($this->normalizedData['configuration']));

        $tenant = new Tenant();
        $tenant->setName($this->normalizedData['name']);
        $tenant->setSettings($configuration);

        $this->provider->save($tenant);

        // Avoid creating tenant with the same data
        $this->normalizedData = [];

        return $tenant;
    }

    /**
     * @param array $data
     *
     * @return TenantFactory
     */
    public function withData(array $data): TenantFactory
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(array_keys($data));
        $this->optionsConfigure($resolver);

        $this->normalizedData = $resolver->resolve($data);

        return $this;
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @return OptionsResolver
     */
    protected function optionsConfigure(OptionsResolver $resolver): OptionsResolver
    {
        $resolver->setRequired(['name', 'configuration']);
        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('configuration', 'array');

        $resolver->setDefault('configuration', function(OptionsResolver $nestedResolver) {
            $nestedResolver->setRequired(['host', 'port', 'dbname', 'user', 'password']);
            $nestedResolver->setDefaults($this->defaultConfiguration);
            $nestedResolver->setAllowedTypes('host', ['string', 'null']);
            $nestedResolver->setAllowedTypes('port', ['int', 'null']);
            $nestedResolver->setAllowedTypes('dbname', ['string', 'null']);
            $nestedResolver->setAllowedTypes('user', ['string', 'null']);
            $nestedResolver->setAllowedTypes('password', ['string', 'null']);
        });

        return $resolver;
    }
}