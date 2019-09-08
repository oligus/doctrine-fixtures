<?php declare(strict_types=1);

namespace DoctrineFixtures\Loaders;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use DoctrineFixtures\Drivers\Driver;
use SimpleXMLElement;
use Exception;

/**
 * Interface Loader
 * @package DoctrineFixtures\Loaders
 */
class XmlLoader implements Loader
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
     * @param Driver $driver
     */
    public function setDriver(Driver $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * XmlLoader constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->files = $this->scanDirectory($path);
    }

    /**
     * @param string $path
     * @return array<int,string>
     */
    private function scanDirectory(string $path): array
    {
        $files = [];

        if (is_dir($path)) {
            foreach (glob($path . "/*.xml") as $file) {
                if (is_string($file)) {
                    $files[] = $file;
                }
            }
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

        $xml = simplexml_load_file($file);

        if ($xml) {
            $this->loadTable($xml);
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @throws DBALException
     */
    private function loadTable(SimpleXMLElement $xml)
    {
        $tableName = $this->getAttribute($xml->database->table_data, 'name');
        $rows = $xml->database->table_data->row;
        $connection = $this->em->getConnection();

        $connection->executeQuery($this->driver->disableForeignKeyQuery());
        $connection->executeQuery($this->driver->truncateTableQuery($tableName));

        $data = [];

        foreach ($rows as $row) {
            foreach ($row->field as $element) {
                $field = $this->getAttribute($element, 'name');
                $value = (string)$element;

                $data[$field] = $value;
            }

            $connection->insert($tableName, $data);
        }

        $connection->executeQuery($this->driver->enableForeignKeyQuery());
    }

    /**
     * @param SimpleXMLElement $element
     * @param string $attribute
     * @return string
     */
    private function getAttribute(SimpleXMLElement $element, string $attribute): string
    {
        $result = '';

        if (isset($element[$attribute])) {
            $result = (string)$element[$attribute];
        }

        return $result;
    }

    /**
     * @return array<int,string>
     */
    public function getTables(): array
    {
        $tables = $this->em->getConnection()->getSchemaManager()->listTableNames();

        foreach ($this->files as $file) {
            $xml = simplexml_load_file((string)$file);
            $tables[] = $this->getAttribute($xml->database->table_data, 'name');
        }

        return array_unique($tables);
    }
}
