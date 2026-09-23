<?php declare(strict_types=1);

namespace DoctrineFixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use DoctrineFixtures\Drivers\Driver;
use DoctrineFixtures\Drivers\Generic;
use DoctrineFixtures\Drivers\MySql;
use DoctrineFixtures\Drivers\SQLLite;
use DoctrineFixtures\Loaders\Loader;
use Doctrine\ORM\Tools\ToolsException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Schema\Schema;

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
     */
    private function getDriver(): Driver
    {
        $platform = $this->em->getConnection()->getDatabasePlatform();

        if ($platform instanceof SQLitePlatform) {
            return new SQLLite();
        }

        if ($platform instanceof AbstractMySQLPlatform) {
            return new MySql();
        }

        return new Generic();
    }


    /**
     * @throws ToolsException
     * @throws ORMInvalidArgumentException
     */
    public function createSchema(): void
    {
        $this->dropSchema();
        $this->em->getUnitOfWork()->clear();

        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();

        if (!$this->driver instanceof SQLLite) {
            $schemaTool->createSchema($metadata);
            return;
        }

        $schema = $schemaTool->getSchemaFromMetadata($metadata);
        $this->makeIndexNamesUnique($schema);

        $connection = $this->em->getConnection();
        foreach ($schema->toSql($connection->getDatabasePlatform()) as $sql) {
            $connection->executeStatement($sql);
        }
    }

    /**
     * Prefix index names with their table name, for SQLite only.
     *
     * SQLite index names are unique per database, where MySQL's are unique per table. A
     * mapping that declares the same index name on two entities - common in legacy schemas
     * where names like `id` or `deleted` repeat across tables - is valid against MySQL but
     * aborts schema creation on SQLite with "index <name> already exists". That forces
     * projects to leave indexes out of their mappings entirely just to keep tests running.
     *
     * Renaming happens on the generated Schema rather than on the ClassMetadata, so it
     * stays local to this call: the mappings are shared with the caller's EntityManager
     * and must not be mutated. Fixtures address tables and columns, never index names.
     *
     * @param Schema $schema
     */
    private function makeIndexNamesUnique(Schema $schema): void
    {
        foreach ($schema->getTables() as $table) {
            foreach (array_keys($table->getIndexes()) as $indexName) {
                $index = $table->getIndex($indexName);

                if ($index->isPrimary()) {
                    continue;
                }

                $table->renameIndex($indexName, $table->getName() . '_' . $index->getName());
            }
        }
    }

    /**
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
     * @throws ToolsException
     * @throws ORMInvalidArgumentException
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
     * @throws ToolsException
     * @throws ORMInvalidArgumentException
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
