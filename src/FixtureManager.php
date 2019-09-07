<?php declare(strict_types=1);

namespace DoctrineFixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use DoctrineFixtures\Loaders\Loader;
use Doctrine\ORM\Tools\ToolsException;
use Doctrine\DBAL\DBALException;

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
     * FixtureManager constructor.
     * @param EntityManager $em
     * @param Loader $loader
     * @throws DBALException
     * @throws ToolsException
     */
    public function __construct(EntityManager $em, Loader $loader)
    {
        $this->loader = $loader;
        $this->em = $em;

        $this->loader->setEm($em);

        $this->createSchema();
    }

    /**
     * @throws DBALException
     * @throws ToolsException
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
        $connection->executeQuery('PRAGMA foreign_keys = OFF');

        foreach($tables as $tableName) {
            $sql = 'DROP TABLE IF EXISTS ' . $tableName;
            $connection->executeQuery($sql);
        }

        $connection->executeQuery('PRAGMA foreign_keys = ON');
    }

    public function loadAll(): void
    {
        $this->loader->loadAll();
    }

    public function loadFile(string $file): void
    {
        $this->loader->loadFile($file);
    }
}
