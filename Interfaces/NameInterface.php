<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

/**
 * Interface NameInterface
 * @package App\TenantBundle\Interfaces
 */
interface NameInterface {

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