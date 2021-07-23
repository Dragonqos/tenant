<?php declare(strict_types=1);

namespace App\TenantBundle\Tests\Unit\Locators;

use App\TenantBundle\Locators\HostnameLocator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HostnameLocatorTest
 * @package MultiTenant\Locator
 */
class HostnameLocatorTest extends TestCase
{
    public function testShouldMatchTenantFromSubDomain()
    {
        $request = Request::create('http://tenant1.example.org/login');

        $locator = new HostnameLocator($request);
        self::assertEquals('tenant1', $locator->getTenantFromRequest($request));
    }

    public function testCorrectlyRetrieveTenantNameWithDotInThePath()
    {
        $request = Request::create('http://tenant1.example.org/sign.up');

        $locator = new HostnameLocator($request);
        self::assertEquals('tenant1', $locator->getTenantFromRequest($request));
    }

    public function testThrowExceptionWhenTenantCodeIsNotInTheUrl()
    {
        self::expectException(\RuntimeException::class);
        $request = Request::create('http://mysite.dev');

        $locator = new HostnameLocator($request);
        $locator->getTenantFromRequest($request);
    }

    /**
     * covers setPattern
     */
    public function testShouldMatchTenantWithCustomSubDomainPattern()
    {
        $request = Request::create('http://tenant1.mysite.dev');

        $locator = new HostnameLocator($request);
        $locator->setPattern('/^(?P<tenant>[a-z0-9][a-z0-9._-]{1,62}?)\.(.*?)\./i');
        self::assertEquals('tenant1', $locator->getTenantFromRequest($request));
    }

    public function testShouldNotMatchTenantWithCustomSubDomainPattern()
    {
        self::expectException(\RuntimeException::class);

        $request = Request::create('http://_tenant1.mysite.dev');

        $locator = new HostnameLocator($request);
        $locator->setPattern('/^(?P<tenant>[a-z0-9][a-z0-9._-]{1,62}?)\.(.*?)\./i');
        $locator->getTenantFromRequest($request);
    }
}