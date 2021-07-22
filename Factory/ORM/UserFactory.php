<?php declare(strict_types=1);

namespace App\TenantBundle\Factory\ORM;

use App\TenantBundle\Entity\TenantUser;
use App\TenantBundle\Interfaces\FactoryInterface;
use App\TenantBundle\Interfaces\TenantUserInterface;
use App\TenantBundle\Interfaces\TenantUserProviderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserFactory
 * @package App\TenantBundle\Factory
 */
class UserFactory implements FactoryInterface{

    private TenantUserProviderInterface $provider;

    /**
     * UserFactory constructor.
     * @param TenantUserProviderInterface $provider
     */
    public function __construct(
        TenantUserProviderInterface $provider
    ) {
        $this->provider = $provider;
    }

    /**
     * @return TenantUserInterface
     */
    public function createNew(): TenantUserInterface
    {
        if (empty($this->normalizedData)) {
            throw new \LogicException('Tenant normalized data must be set first, before creating new tenant');
        }

        $user = new TenantUser();
        $this->provider->save($user);

        $this->normalizedData = [];
        return $user;
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
        $resolver->setRequired(['name', 'email']);

        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('email', 'string');

        return $resolver;
    }
}