<?php declare(strict_types=1);

namespace Tests\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\Logging\EchoSQLLogger;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\ToolsException;
use Tests\DumpSQLLogger;

/**
 * Class Bootstrap
 * @package Tests\Doctrine
 */
class Bootstrap
{
    /**
     * Bootstrap constructor.
     * @throws AnnotationException
     * @throws ORMException
     * @throws ToolsException
     */
    public function __construct()
    {
        $paths = [ROOT_PATH . '/Doctrine/Entities'];
        $proxyPaths = ROOT_PATH . '/proxies';

        $isDevMode = true;

        $doctrineConfig = new Configuration();

        $driver = new AnnotationDriver(new AnnotationReader(), $paths);
        AnnotationRegistry::registerLoader('class_exists');

        $doctrineConfig->setMetadataDriverImpl($driver);
        $doctrineConfig->setProxyDir($proxyPaths);
        $doctrineConfig->setProxyNamespace('CX\Proxies');
        $doctrineConfig->setAutoGenerateProxyClasses(true);

        $cache = new ArrayCache;
        $doctrineConfig->setQueryCacheImpl($cache);

        // $doctrineConfig->setSQLLogger(new DumpSQLLogger());
        // $doctrineConfig->getSQLLogger();

        $connectionParams = [
            'url' => getenv('DB_CONNECTION') . ':///' . getenv('DB_DATABASE')
        ];

        $em = EntityManager::create($connectionParams, $doctrineConfig);

        Manager::getInstance()->setEm($em);
    }

    public function run()
    {

    }
}
