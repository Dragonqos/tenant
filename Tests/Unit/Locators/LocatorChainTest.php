<?php declare(strict_types=1);

namespace App\TenantBundle\Tests\Unit\Locators;

use App\TenantBundle\Locators\GetParameterLocator;
use App\TenantBundle\Locators\HeaderLocator;
use App\TenantBundle\Locators\HostnameLocator;
use App\TenantBundle\Locators\LocatorChain;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LocatorChainTest
 * @package MultiTenant\Locator
 */
class LocatorChainTest extends TestCase
{
    protected $locator;

    public function setUp(): void
    {
        $this->locator = new LocatorChain('tenant');
        $this->locator->addLocator(new HostnameLocator());
        $this->locator->addLocator(new HeaderLocator());
        $this->locator->addLocator(new GetParameterLocator());
    }

    /**
     * covers LocatorChain::locate()
     */
    public function testLocateFromGetParam()
    {
        $request = Request::create('http://mysite.dev?tenant=acme_co');

        self::assertEquals('acme_co', $this->locator->locate($request));
    }

    /**
     * covers LocatorChain::locate()
     */
    public function testLocateFromHeader()
    {
        $request = Request::create('http://mysite.dev?tenant=query_acme_co');
        $request->headers->add(['tenant' => 'header_acme_co']);

        self::assertEquals('header_acme_co', $this->locator->locate($request));
    }

    /**
     * covers LocatorChain::locate()
     */
    public function testThrowExceptionWhenTenantNotFound()
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Could not locate a tenant from the request');
        $request = Request::create('http://mysite.dev?code=query_acme_co');

        self::assertEquals('header_acme_co', $this->locator->locate($request));
    }

    /**
     * covers LocatorChain::addLocator()
     * covers LocatorChain::removeLocator()
     * covers LocatorChain::hasLocator()
     */
    public function testShouldRemoveLocator()
    {
        self::assertEquals(true, $this->locator->hasLocator('App\TenantBundle\Locators\HostnameLocator'));
        $this->locator->removeLocator('\App\TenantBundle\Locators\HostnameLocator');

        self::assertEquals(false, $this->locator->hasLocator('\App\TenantBundle\Locators\HostnameLocator'));
    }
}