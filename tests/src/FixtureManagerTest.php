<?php declare(strict_types=1);

namespace Tests\Src;

use DoctrineFixtures\FixtureManager;
use DoctrineFixtures\Loaders\XmlLoader;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Doctrine\Entities\Accounts;
use Tests\Doctrine\Entities\Users;
use Tests\TestCase;
use Exception;

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
        $fixture = new FixtureManager($this->getEntityManager(), new XmlLoader(ROOT_PATH . '/fixtures'));
        $fixture->loadAll();

        $user = $this->getEntityManager()->getRepository(Users::class)->find(1);
        $this->assertMatchesJsonSnapshot(json_encode($user));
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function testLoadFile()
    {
        $fixture = new FixtureManager($this->getEntityManager(), new XmlLoader(ROOT_PATH . '/fixtures'));
        $fixture->loadFile(ROOT_PATH . '/fixtures/accounts.xml');

        $user = $this->getEntityManager()->getRepository(Users::class)->find(1);
        $this->assertNull($user);

        $account = $this->getEntityManager()->getRepository(Accounts::class)->find(1);
        $this->assertMatchesJsonSnapshot(json_encode($account));
    }
}
