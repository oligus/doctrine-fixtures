<?php declare(strict_types=1);

namespace DoctrineFixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use DoctrineFixtures\Drivers\Driver;
use DoctrineFixtures\Drivers\Generic;
use DoctrineFixtures\Drivers\SQLLite;
use DoctrineFixtures\Loaders\Loader;
use Doctrine\ORM\Tools\ToolsException;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\DBAL\Connection;

/**
 * Class FixtureManager
 * @package DoctrineFixtures
 */
class FixtureManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Loader
     */
    private $loader;

    /**
     * @var Driver
     */
    private $driver;

    /**
     * FixtureManager constructor.
     * @param EntityManager $em
     * @param Loader $loader
     * @throws DBALException
     * @throws ToolsException
     * @throws ORMInvalidArgumentException
     */
    public function __construct(EntityManager $em, Loader $loader)
    {
        $this->loader = $loader;
        $this->em = $em;

        $this->driver = $this->getDriver();
        $this->loader->setEm($this->em);
        $this->loader->setDriver($this->driver);
    }

    /**
     * @return Driver
     * @throws DBALException
     */
    private function getDriver(): Driver
    {
        $platform = $this->em->getConnection()->getDatabasePlatform()->getName();

        switch ($platform) {
            case 'sqlite':
                $driver = new SQLLite();
                break;

            default:
                $driver = new Generic();
        }

        return $driver;
    }


    /**
     * @throws DBALException
     * @throws ToolsException
     * @throws ORMInvalidArgumentException
     */
    public function createSchema(): void
    {
        $this->dropSchema();
        $this->em->getUnitOfWork()->clear();

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    /**
     * @throws DBALException
     * @throws ToolsException
     * @throws ORMInvalidArgumentException
     * @phan-suppress PhanUndeclaredMethod
     */
    public function createTable(string $tableName): void
    {
        $connection = $this->em->getConnection();
        $connection->executeQuery($this->driver->disableForeignKeyQuery());
        $this->dropTable($connection, $tableName);
        $this->em->getUnitOfWork()->clear();

        $schemaTool = new SchemaTool($this->em);
        $metaData = [];

        /** @var ClassMetadata $meta */
        foreach ($this->em->getMetadataFactory()->getAllMetadata() as $meta) {
            $name = method_exists($meta, 'getTableName') ? $meta->getTableName() : null;

            if ($name === $tableName) {
                $metaData[] = $meta;
                continue;
            }
        }

        $schemaTool->createSchema($metaData);
        $connection->executeQuery($this->driver->enableForeignKeyQuery());
    }

    /**
     * @throws DBALException
     */
    public function dropSchema(): void
    {
        $tables = $this->loader->getTables();
        $connection = $this->em->getConnection();
        $connection->executeQuery($this->driver->disableForeignKeyQuery());

        foreach ($tables as $tableName) {
            $this->dropTable($connection, $tableName);
        }

        $connection->executeQuery($this->driver->enableForeignKeyQuery());
    }

    /**
     * @throws DBALException
     */
    public function dropTable(Connection $connection, string $tableName): void
    {
        if ($this->driver->isProtectedTable($tableName)) {
            return;
        }

        $sql = $this->driver->dropTableQuery($tableName);
        $connection->executeQuery($sql);
    }

    /**
     * @param string|null $path
     * @throws DBALException
     * @throws ToolsException
     * @throws ORMInvalidArgumentException
     * @throws ToolsException
     */
    public function loadAll(?string $path = null): void
    {
        $this->createSchema();

        if (!empty($path)) {
            $this->loader->setPath($path);
        }
        $this->loader->loadAll();
    }

    /**
     * @param string $file
     * @throws DBALException
     * @throws ToolsException
     * @throws ORMInvalidArgumentException
     * @throws ToolsException
     */
    public function loadFile(string $file): void
    {
        $this->createTable('accounts');

        $this->loader->loadFile($file);
    }

    /**
     * @return Loader
     */
    public function getLoader(): Loader
    {
        return $this->loader;
    }
}
