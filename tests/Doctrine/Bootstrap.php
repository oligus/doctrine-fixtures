<?php declare(strict_types=1);

namespace DoctrineFixtures\Tests\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

/**
 * Class Bootstrap
 * @package Tests\Doctrine
 */
class Bootstrap
{
    public function __construct()
    {
        $paths = [ROOT_PATH . '/Doctrine/Entities'];
        $proxyPaths = ROOT_PATH . '/proxies';

        $isDevMode = true;

        $doctrineConfig = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode, $proxyPaths);
        $doctrineConfig->setProxyNamespace('CX\Proxies');
        $doctrineConfig->setAutoGenerateProxyClasses(true);

        // ORM 3.5+ on PHP 8.4 uses native lazy objects; symfony/var-exporter 8 dropped LazyGhost.
        if (PHP_VERSION_ID >= 80400 && method_exists($doctrineConfig, 'enableNativeLazyObjects')) {
            $doctrineConfig->enableNativeLazyObjects(true);
        }

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_' . getenv('DB_CONNECTION'),
            'path' => getenv('DB_DATABASE'),
            'memory' => getenv('DB_DATABASE') === ':memory:',
        ], $doctrineConfig);

        Manager::getInstance()->setEm(new EntityManager($connection, $doctrineConfig));
    }

    public function run()
    {

    }
}
