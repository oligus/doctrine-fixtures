<?php declare(strict_types=1);

namespace Tests\Src\Loaders;

use DoctrineFixtures\Drivers\SQLLite;
use DoctrineFixtures\Loaders\XmlLoader;
use Spatie\Snapshots\MatchesSnapshots;
use DoctrineFixtures\Tests\Doctrine\Entities\Accounts;
use DoctrineFixtures\Tests\TestCase;
use Exception;

/**
 * Class XmlLoaderTest
 * @package Tests\Src\Loaders
 */
class XmlLoaderTest extends TestCase
{
    use MatchesSnapshots;

    /**
     * @throws Exception
     */
    public function testLoadAll()
    {
        $loader = new XmlLoader(ROOT_PATH . '/fixtures');
        $loader->setEm($this->getEntityManager());
        $loader->setDriver(new SQLLite());
        $loader->loadAll();

        $account = $this->getEntityManager()->getRepository(Accounts::class)->find(1);
        $this->assertMatchesJsonSnapshot(json_encode($account));
    }
}
