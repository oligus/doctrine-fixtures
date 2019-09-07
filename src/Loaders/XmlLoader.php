<?php declare(strict_types=1);

namespace DoctrineFixtures\Loaders;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use SimpleXMLElement;
use Exception;

/**
 * Interface Loader
 * @package DoctrineFixtures\Loaders
 */
class XmlLoader implements Loader
{
    /**
     * @var array
     */
    private $files = [];

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * XmlLoader constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->files = $this->scanDirectory($path);
    }

    /**
     * @param EntityManager $em
     */
    public function setEm(EntityManager $em): void
    {
        $this->em = $em;
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function loadAll(): void
    {
        foreach($this->files as $file) {
            $this->loadFile($file);
        }
    }

    /**
     * @param string $file
     * @throws Exception
     */
    public function loadFile(string $file): void
    {
        if(!is_file($file) || !is_readable($file)) {
            throw new Exception('Could not read ' . $file);
        }

        $xml = simplexml_load_file($file);
        $this->loadTable($xml);
    }

    public function getTables(): array
    {
        $tables = [];

        foreach($this->files as $file) {
            $xml = simplexml_load_file($file);
            $tables[] = $this->getAttribute($xml->database->table_data, 'name');
        }

        return $tables;
    }

    /**
     * @param SimpleXMLElement $xml
     * @throws DBALException
     */
    private function loadTable(SimpleXMLElement $xml)
    {
        $rows = $xml->database->table_data->row;

        $data = [];

        foreach($rows as $row) {
            foreach($row->field as $element) {
                $field = $this->getAttribute($element, 'name');
                $value = (string) $element;

                $data[$field] = $value;
            }
        }

        $tableName = $this->getAttribute($xml->database->table_data, 'name');

        $connection = $this->em->getConnection();

        $connection->executeQuery('PRAGMA foreign_keys = OFF');
        $connection->executeQuery('DELETE FROM ' . $tableName);
        $connection->insert($tableName, $data);
        $connection->executeQuery('PRAGMA foreign_keys = ON');
    }

    /**
     * @param string $path
     * @return array
     */
    private function scanDirectory(string $path): array
    {
        $files = [];

        if (is_dir($path)) {
            foreach (glob($path . "/*.xml") as $file) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * @param $object
     * @param $attribute
     * @return string|null
     */
    private function getAttribute($object, $attribute): ?string
    {
        if(isset($object[$attribute])) {
            return (string) $object[$attribute];
        }

        return null;
    }
}
