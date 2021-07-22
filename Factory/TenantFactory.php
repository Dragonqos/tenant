<?php declare(strict_types=1);

namespace App\TenantBundle\Factory;

use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Interfaces\FactoryInterface;
use App\TenantBundle\Interfaces\TenantProviderInterface;
use App\TenantBundle\TenantInterface;
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

        $tenant = new Tenant();
        $tenant->setName($this->normalizedData['name']);

        // TODO implement config change on a fly
        $tenant->setSettings($this->defaultConfiguration);

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
        $resolver->setRequired(['name', 'email', 'firstname']);

        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('email', 'string');
        $resolver->setAllowedTypes('firstname', 'string');

        return $resolver;
    }
}