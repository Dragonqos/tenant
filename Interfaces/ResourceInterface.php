<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

/**
 * Interface ResourceInterface
 * @package App\TenantBundle\Interfaces
 */
interface ResourceInterface {

    /**
     * @param int|null $id
     * @return mixed
     */
    public function setId(?int $id): self;

    /**
     * @return int|null
     */
    public function getId(): ?int;
}