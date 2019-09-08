<?php declare(strict_types=1);

namespace DoctrineFixtures\Loaders;

use Doctrine\ORM\EntityManager;
use DoctrineFixtures\Drivers\Driver;

/**
 * Interface Loader
 * @package DoctrineFixtures\Loaders
 */
interface Loader
{
    /**
     * Loader constructor.
     * @param string $path
     */
    public function __construct(string $path);

    /**
     * @param EntityManager $em
     */
    public function setEm(EntityManager $em): void;

    /**
     * @param Driver $driver
     */
    public function setDriver(Driver $driver): void;

    /**
     *
     */
    public function loadAll(): void;

    /**
     * @param string $file
     */
    public function loadFile(string $file): void;

    /**
     * @return array<int,string>
     */
    public function getTables(): array;
}
