<?php declare(strict_types=1);

namespace DoctrineFixtures\Tests\Src;

use DoctrineFixtures\FixtureManager;
use DoctrineFixtures\Loaders\XmlLoader;
use Spatie\Snapshots\MatchesSnapshots;
use DoctrineFixtures\Tests\Doctrine\Entities\Accounts;
use DoctrineFixtures\Tests\Doctrine\Entities\Users;
use DoctrineFixtures\Tests\TestCase;
use Exception;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\Tools\ToolsException;

/**
 * Class XmlLoaderTest
 * @package Tests\Src\Loaders
 */
class FixtureManagerTest extends TestCase
{
    use MatchesSnapshots;

    /**
     * @throws Exception
     */
    public function testLoadAll()
    {
        $fixture = new FixtureManager($this->getEntityManager(), new XmlLoader());
        $fixture->loadAll(ROOT_PATH . '/fixtures');

        $user = $this->getEntityManager()->getRepository(Users::class)->find(1);
        $this->assertMatchesJsonSnapshot(json_encode($user));
    }

    /**
     * @throws DBALException
     * @throws ToolsException
     */
    public function testLoadFile()
    {
        $fixture = new FixtureManager($this->getEntityManager(), new XmlLoader());
        $fixture->loadFile(ROOT_PATH . '/fixtures/accounts.xml');

        $account = $this->getEntityManager()->getRepository(Accounts::class)->find(1);
        $this->assertMatchesJsonSnapshot(json_encode($account));

        $fixture->loadAll(ROOT_PATH . '/fixtures');
        $user = $this->getEntityManager()->getRepository(Users::class)->find(1);
        $this->assertMatchesJsonSnapshot(json_encode($user));

        $fixture->loadFile(ROOT_PATH . '/fixtures/single/accounts2.xml');
        $accounts = $this->getEntityManager()->getRepository(Accounts::class)->findAll();
        $this->assertMatchesJsonSnapshot(json_encode($accounts));
    }

    /**
     * Both test entities declare an index named `name_idx`. SQLite index names are unique
     * per database, so createSchema() must prefix them with the table name.
     *
     * @throws DBALException
     * @throws ToolsException
     */
    public function testCreateSchemaMakesSqliteIndexNamesUnique()
    {
        $fixture = new FixtureManager($this->getEntityManager(), new XmlLoader());
        $fixture->createSchema();

        $schemaManager = $this->getEntityManager()->getConnection()->createSchemaManager();

        $this->assertArrayHasKey('accounts_name_idx', $schemaManager->listTableIndexes('accounts'));
        $this->assertArrayHasKey('users_name_idx', $schemaManager->listTableIndexes('users'));
    }
}
