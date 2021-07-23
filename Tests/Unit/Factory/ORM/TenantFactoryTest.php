<?php declare(strict_types=1);

namespace App\TenantBundle\Tests\Unit\Factory\ORM;

use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Factory\ORM\TenantFactory;
use App\TenantBundle\Interfaces\TenantProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * Class TenantFactoryTest
 * @package App\TenantBundle\Tests\Unit\Factory\ORM
 */
class TenantFactoryTest extends TestCase {

    private \PHPUnit\Framework\MockObject\MockObject $provider;
    private TenantFactory $factory;

    public function setUp()
    {
        $this->provider = self::getMockBuilder(TenantProviderInterface::class)->getMockForAbstractClass();
        $this->factory = new TenantFactory($this->provider, '127.0.0.1', 'test', 'pass', 3306);
    }

    public function test_create_without_data_fails()
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Tenant normalized data must be set first, before creating new tenant');

        $this->factory->createNew();
    }

    public function test_fails_when_wrong_data_passed()
    {
        self::expectException(MissingOptionsException::class);
        self::expectExceptionMessage('The required option "name" is missing.');

        $this->factory->withData(['test']);
    }

    public function test_fails_when_redundant_data_passed()
    {
        $result = $this->factory->withData(['name' => 'foo', 'configuration' => [], 'email' => 'test'])->createNew();
        self::assertEquals(true, true);
        self::assertEquals('foo', $result->getName());
        self::assertEquals([
            'host' => '127.0.0.1',
            'port' => 3306,
            'dbname' => 'guest',
            'user' => 'test',
            'password' => 'pass'
        ], $result->getSettings());
    }

    public function test_fails_when_wrong_type_passed()
    {
        self::expectException(InvalidOptionsException::class);
        self::expectExceptionMessage('The option "name" with value 1 is expected to be of type "string", but is of type "int".');

        $this->factory->withData(['name' => 1, 'configuration' => []]);
    }

    public function test_fails_when_wrong_second_type_passed()
    {
        self::expectException(InvalidOptionsException::class);
        self::expectExceptionMessage('The nested option "configuration" with value "string" is expected to be of type array, but is of type "string".');

        $this->factory->withData(['name' => 'foo', 'configuration' => 'string']);
    }

    public function test_fails_when_wrong_subtype_type_passed()
    {
        self::expectException(InvalidOptionsException::class);
        self::expectExceptionMessage('The option "configuration[host]" with value 111 is expected to be of type "string" or "null", but is of type "int".');

        $this->factory->withData(['name' => 'foo', 'configuration' => [
            'host' => 111
        ]]);
    }

    public function test_should_apply_default_config_values()
    {
        $result = $this->factory->withData(['name' => 'foo', 'configuration' => [
            'host' => null,
            'dbname' => 'foo'
        ]])->createNew();
        self::assertInstanceOf(Tenant::class, $result);
        self::assertEquals('foo', $result->getName());
        self::assertEquals([
            'host' => '127.0.0.1',
            'port' => 3306,
            'dbname' => 'foo',
            'user' => 'test',
            'password' => 'pass',
        ], $result->getSettings());
    }

    public function test_ok()
    {
        $result = $this->factory->withData(['name' => 'foo', 'configuration' => []])->createNew();
        self::assertInstanceOf(Tenant::class, $result);
        self::assertEquals('foo', $result->getName());
        self::assertEquals([
            'host' => '127.0.0.1',
            'port' => 3306,
            'dbname' => 'guest',
            'user' => 'test',
            'password' => 'pass',
        ], $result->getSettings());
    }
}