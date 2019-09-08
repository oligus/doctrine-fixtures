<?php declare(strict_types=1);

namespace DoctrineFixtures\Tests;

use Doctrine\DBAL\Logging\SQLLogger;
use const PHP_EOL;
use function dump;

/**
 * A SQL logger that logs to the standard output using echo/var_dump.
 */
class DumpSQLLogger implements SQLLogger
{
    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        dump($sql);

        if ($params) {
            dump($params);
        }

        if (! $types) {
            return;
        }

        var_dump($types);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }
}
