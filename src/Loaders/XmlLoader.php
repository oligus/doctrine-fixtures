<?php declare(strict_types=1);

namespace DoctrineFixtures\Loaders;

use Doctrine\ORM\EntityManager;
use DoctrineFixtures\Drivers\Driver;
use SimpleXMLElement;
use Exception;

/**
 * Class XmlLoader
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

        foreach (glob($path . "/*.xml") as $file) {
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

        $xml = simplexml_load_file($file);

        if ($xml instanceof SimpleXMLElement) {
            $this->loadTable($xml);
        }
    }

    /**
     * @param SimpleXMLElement $xml
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

            /** @var SimpleXMLElement $element */
            foreach ($row->field as $element) {
                list($field, $value) = $this->getFieldValue($element);
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
        $connection = $this->em->getConnection();

        if (method_exists($connection, 'createSchemaManager')) {
            $tables = $connection->createSchemaManager()->listTableNames();
        } else {
            $tables = $connection->getSchemaManager()->listTableNames();
        }

        foreach ($this->files as $file) {
            $xml = simplexml_load_file((string)$file);
            $tables[] = $this->getAttribute($xml->database->table_data, 'name');
        }

        return array_unique($tables);
    }

    /**
     * @return array{0:string,1:?string}
     */
    private function getFieldValue(SimpleXMLElement $element): array
    {
        $field = $this->getAttribute($element, 'name');
        $value = (string)$element;

        /** @var SimpleXMLElement $attributes */
        $attributes = $element->attributes('xsi', true);

        if (!empty($attributes)) {
            $isNull = (bool)$attributes['nil'];

            if ($isNull) {
                $value = null;
            }
        }

        return array($field, $value);
    }
}
