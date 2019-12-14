<?php declare(strict_types=1);

namespace DoctrineFixtures\Loaders;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use DoctrineFixtures\Drivers\Driver;
use Exception;

/**
 * Class CsvLoader
 * @package DoctrineFixtures\Loaders
 */
class CsvLoader implements Loader
{
    /**
     * @var array<int,string>
     */
    private $files = [];

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var string
     */
    private $path;

    /**
     * @param Driver $driver
     */
    public function setDriver(Driver $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * XmlLoader constructor.
     * @param string|null $path
     */
    public function __construct(?string $path = null)
    {
        if (!empty($path)) {
            $this->setPath($path);
        }
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
        $this->files = $this->scanDirectory($path);
    }

    /**
     * @param string $path
     * @return array<int,string>
     */
    private function scanDirectory(string $path): array
    {
        $files = [];

        if (!is_dir($path)) {
            return $files;
        }

        foreach (glob($path . "/*.csv") as $file) {
            if (!is_string($file)) {
                continue;
            }

            $files[] = $file;
        }

        return $files;
    }

    /**
     * @param EntityManager $em
     */
    public function setEm(EntityManager $em): void
    {
        $this->em = $em;
    }

    /**
     * @throws Exception
     */
    public function loadAll(): void
    {
        foreach ($this->files as $file) {
            $this->loadFile($file);
        }
    }

    /**
     * @param string $file
     * @throws Exception
     */
    public function loadFile(string $file): void
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new Exception('Could not read ' . $file);
        }

        $handle = fopen($file, "r");

        if (!$handle) {
            throw new Exception('Could not open ' . $file);
        }

        $this->loadTable($handle, $this->getTable($file));

        fclose($handle);
    }

    /**
     * @param resource $handle
     * @throws DBALException
     */
    private function loadTable($handle, string $tableName)
    {
        $lineCount = 0;
        $headers = [];

        $connection = $this->em->getConnection();
        $connection->executeQuery($this->driver->disableForeignKeyQuery());
        $connection->executeQuery($this->driver->truncateTableQuery($tableName));

        while (($line = fgets($handle)) !== false) {
            $line = preg_replace('/[\r\n\"]/', '', $line);

            if ($lineCount === 0) {
                $headers = explode(',', $line);
                $lineCount++;
                continue;
            }

            $result = explode(',', $line);
            $data = [];

            foreach ($headers as $id => $header) {
                $data[$header] = $result[$id];
            }

            $connection->insert($tableName, $data);
        }

        $connection->executeQuery($this->driver->enableForeignKeyQuery());
    }

    /**
     * @return array<int,string>
     */
    public function getTables(): array
    {
        $tables = $this->em->getConnection()->getSchemaManager()->listTableNames();

        foreach ($this->files as $file) {
            $tables[] = $this->getTable($file);
        }

        return array_unique($tables);
    }

    private function getTable(string $file): string
    {
        return preg_replace('/\.csv/', '', basename($file));
    }
}
