<?php declare(strict_types=1);

namespace App\TenantBundle\Tests\Unit\Locators;

use App\TenantBundle\Locators\HeaderLocator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class HeaderLocatorTest extends TestCase
{
    public function testCorrectlyRetrieveTenantName()
    {
        $request = Request::create('http://mysite.dev');
        $request->headers->add(['tenant' => 'acme_co']);

        $locator = new HeaderLocator($request, 'tenant');
        self::assertEquals('acme_co', $locator->getTenantFromRequest($request, 'tenant'));
    }

    public function testCorrectlyRetrieveTenantNameFromDifferentlyNamedGetParameter()
    {
        $request = Request::create('http://mysite.dev');
        $request->headers->add(['code' => 'acme_co']);

        $locator = new HeaderLocator($request, 'code');
        self::assertEquals('acme_co', $locator->getTenantFromRequest($request, 'code'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testThrowExceptionWhenTenantCodeIsNotInTheUrl()
    {
        $request = Request::create('http://mysite.dev');

        $locator = new HeaderLocator($request, 'tenant');
        $locator->getTenantFromRequest($request, 'tenant');
    }
}