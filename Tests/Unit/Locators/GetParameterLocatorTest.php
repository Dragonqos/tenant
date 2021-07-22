<?php declare(strict_types=1);

namespace App\TenantBundle\Tests\Unit\Locators;

use App\TenantBundle\Locators\GetParameterLocator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GetParameterLocatorTest
 * @package App\TenantBundle\Tests\Unit\Locators
 */
class GetParameterLocatorTest extends TestCase
{
    public function testCorrectlyRetrieveTenantName()
    {
        $request = Request::create('http://mysite.dev?tenant=acme_co');

        $locator = new GetParameterLocator($request, 'tenant');
        self::assertEquals('acme_co', $locator->getTenantFromRequest($request));
    }

    public function testCorrectlyRetrieveTenantNameFromDifferentlyNamedGetParameter()
    {
        $request = Request::create('http://mysite.dev?code=acme_co');

        $locator = new GetParameterLocator($request, 'code');
        self::assertEquals('acme_co', $locator->getTenantFromRequest($request, 'code'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage GET parameter "tenant" not found.
     */
    public function testThrowExceptionWhenTenantCodeIsNotInTheUrl()
    {
        $request = Request::create('http://mysite.dev?code=acme_co');

        $locator = new GetParameterLocator($request, 'tenant');
        $locator->getTenantFromRequest($request);
    }
}