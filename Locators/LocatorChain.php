<?php declare(strict_types=1);

namespace App\TenantBundle\Locators;

use App\TenantBundle\Interfaces\TenantLocatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LocatorChain
 * @package App\TenantBundle\Locators
 */
class LocatorChain
{
    const SECURITY_LAST_TENANT = '_security.last_tenant';

    /**
     * @var array
     */
    private iterable $locators = [];
    private string $propertyKey;

    /**
     * LocatorChain constructor.
     *
     * @param string $propertyKey
     */
    public function __construct(string $propertyKey)
    {
        $this->propertyKey = $propertyKey;
    }

    /**
     * @param $locator
     */
    public function addLocator($locator): void
    {
        if (is_subclass_of($locator, TenantLocatorInterface::class, true)) {
            $locatorName = $this->filterLocatorName($locator);
            $this->locators[$locatorName] = $locator;
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Locator %s must be subclass of %s',
                is_object($locator) ? get_class($locator) : $locator,
                TenantLocatorInterface::class
            ));
        }
    }

    /**
     * @param $locator
     */
    public function removeLocator($locator): void
    {
        if($this->hasLocator($locator, true)) {
            $locatorName = $this->filterLocatorName($locator);
            unset($this->locators[$locatorName]);
        }
    }

    /**
     * @param $locator
     * @param bool $returnIndex
     * @return bool
     */
    public function hasLocator($locator, bool $returnIndex = false): bool
    {
        if(empty($this->locators)) {
            return false;
        }

        return array_key_exists($this->filterLocatorName($locator), $this->locators);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function locate(Request $request)
    {
        $processed = null;

        foreach ($this->locators as $locator) {
            try {
                $processed = $locator->getTenantFromRequest($request, $this->propertyKey);
            } catch (\RuntimeException $e) {
                $processed = null;
            }

            if (null !== $processed) {
                break;
            }
        }

        if (null === $processed) {
            throw new \RuntimeException('Could not locate a tenant from the request');
        }

        return rawurldecode((string) $processed);
    }

    /**
     * @param $locator
     *
     * @return string
     */
    private function filterLocatorName($locator)
    {
        $locator = is_object($locator) ? get_class($locator) : $locator;
        return trim($locator, '\\');
    }
}