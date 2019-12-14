<?php declare(strict_types=1);

namespace Tests\Src\Loaders;

use Doctrine\Common\Collections\Collection;
use DoctrineFixtures\Drivers\SQLLite;
use DoctrineFixtures\FixtureManager;
use DoctrineFixtures\Loaders\CsvLoader;
use Spatie\Snapshots\MatchesSnapshots;
use DoctrineFixtures\Tests\Doctrine\Entities\Accounts;
use DoctrineFixtures\Tests\TestCase;
use Exception;

/**
 * Class CsvLoaderTest
 * @package Tests\Src\Loaders
 */
class CsvLoaderTest extends TestCase
{
    use MatchesSnapshots;

    /**
     * @throws Exception
     */
    public function testLoadAll()
    {
        $loader = new CsvLoader(ROOT_PATH . '/fixtures');
        $fixture = new FixtureManager($this->getEntityManager(), $loader);
        $fixture->createSchema();
        $loader->setEm($this->getEntityManager());
        $loader->setDriver(new SQLLite());
        $loader->loadAll();

        $account = $this->getEntityManager()->getRepository(Accounts::class)->find(1);
        $this->assertMatchesJsonSnapshot(json_encode($account));

        /** @var Collection $users */
        $users = $account->getUsers();
        $this->assertMatchesJsonSnapshot(json_encode($users->first()));
    }
}
