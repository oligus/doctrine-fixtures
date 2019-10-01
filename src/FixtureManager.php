<?php declare(strict_types=1);

namespace DoctrineFixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use DoctrineFixtures\Drivers\Driver;
use DoctrineFixtures\Drivers\Generic;
use DoctrineFixtures\Drivers\SQLLite;
use DoctrineFixtures\Loaders\Loader;
use Doctrine\ORM\Tools\ToolsException;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMInvalidArgumentException;

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
        $this->createSchema();
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
     */
    public function dropSchema(): void
    {
        $tables = $this->loader->getTables();
        $connection = $this->em->getConnection();
        $connection->executeQuery($this->driver->disableForeignKeyQuery());

        foreach ($tables as $tableName) {
            if($this->driver->isProtectedTable($tableName)) {
                continue;
            }

            $sql = $this->driver->dropTableQuery($tableName);
            $connection->executeQuery($sql);
        }

        $connection->executeQuery($this->driver->enableForeignKeyQuery());
    }

    /**
     * @param string|null $path
     */
    public function loadAll(?string $path = null): void
    {
        if(!empty($path)) {
            $this->loader->setPath($path);
        }
        $this->loader->loadAll();
    }

    /**
     * @param string $file
     */
    public function loadFile(string $file): void
    {
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
