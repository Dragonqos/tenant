<?php declare(strict_types=1);

namespace App\TenantBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentNotFoundException;
use MongoDB\Collection;
use Ramsey\Uuid\Uuid;

/**
 * Class TenantInstallRepository
 * @package App\TenantBundle\Repository
 */
class TenantInstallRepository
{
    const STATE_BEGIN = 0;
    const STATE_COMPLETE = 255;

    const FIELD_UUID = 'uuid';
    const FIELD_TENANT = 'tenant';
    const FIELD_STATE = 'state';
    const FIELD_NORMALIZED_DATA = 'normalized_data';
    const FIELD_MESSAGES = 'messages';
    const FIELD_ERROR_MESSAGES = 'error_messages';

    private DocumentManager $documentManager;
    private string $collectionName;
    private string $databaseName;

    /**
     * TenantInstallRepository constructor.
     *
     * @param DocumentManager $documentManager
     * @param string          $databaseName
     * @param string          $collectionName
     */
    public function __construct(
        DocumentManager $documentManager,
        string $databaseName = 'core',
        string $collectionName = 'tenant_install'
    )
    {
        $this->documentManager = $documentManager;
        $this->collectionName = $collectionName;
        $this->databaseName = $databaseName;
    }

    /**
     * @param array $normalizedData
     *
     * @return string
     */
    public function create(array $normalizedData): string
    {
        $uuid = Uuid::uuid4();
        $result = $this->getCollection()->insertOne(
            [
                self::FIELD_UUID => $uuid->toString(),
                self::FIELD_STATE => self::STATE_BEGIN,
                self::FIELD_NORMALIZED_DATA => $normalizedData,
                self::FIELD_MESSAGES => [],
                self::FIELD_ERROR_MESSAGES => []
            ]
        );

        unset($result);
        return $uuid->toString();
    }

    /**
     * @param string $uuid
     * @param string $message
     */
    public function putErrorMessage(string $uuid, string $message): void
    {
        $criteria = [];

        if(null !== $message) {
            $criteria['$push'] = [self::FIELD_ERROR_MESSAGES => $message];
        }

        $result = $this->getCollection()->updateOne([self::FIELD_UUID => $uuid], $criteria);
        unset($result);
    }


    /**
     * @param string      $uuid
     * @param int         $state
     * @param string|null $message
     */
    public function changeState(string $uuid, int $state, string $message = null): void
    {
        if($state < self::STATE_BEGIN || $state > self::STATE_COMPLETE) {
            throw new \InvalidArgumentException('State code must be in range between 0 and 255');
        }

        $criteria = [];
        $criteria['$set'] = [self::FIELD_STATE => $state];

        if(null !== $message) {
            $criteria['$push'] = [self::FIELD_MESSAGES => $message];
        }

        $result = $this->getCollection()->updateOne([self::FIELD_UUID => $uuid], $criteria);
        unset($result);
    }

    /**
     * @param string $uuid
     *
     * @return array
     * @throws DocumentNotFoundException
     */
    public function retrieve(string $uuid): array
    {
        $result = $this->getCollection()->findOne([
            self::FIELD_UUID => $uuid
        ]);

        if (!$result) {
            throw new DocumentNotFoundException(
                'Tenant installation status not found'
            );
        }

        return $result;
    }

    /**
     * @param string $uuid
     * @param int    $tenant
     */
    public function changeTenant(string $uuid, int $tenant): void
    {
        $result = $this->getCollection()->updateOne([self::FIELD_UUID => $uuid], ['$set' => ['tenant' => $tenant]]);
        unset($result);
    }

    /**
     * @return Collection
     */
    private function getCollection(): Collection
    {
        return $this->documentManager->getClient()->selectCollection($this->databaseName, $this->collectionName);
    }
}