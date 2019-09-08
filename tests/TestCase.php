<?php declare(strict_types=1);

namespace DoctrineFixtures\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Doctrine\ORM\EntityManager;
use ReflectionClass;
use ReflectionMethod;
use ReflectionException;
use PHPUnit\Framework\MockObject\MockObject;
use DoctrineFixtures\Tests\Doctrine\Manager;

/**
 * Class TestCase
 * @package Tests
 */
abstract class TestCase extends PHPUnitTestCase
{
    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return Manager::getInstance()->getEm();
    }

    /**
     * @param $className
     * @param $methodName
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    protected static function getMethod($className, $methodName) {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @param $className
     * @param array $options
     * @return MockObject
     */
    protected function getClassMock($className, array $options = array())
    {
        $mock = $this->getMockBuilder($className)
            ->setMethods(array_keys($options))
            ->getMock();

        foreach($options as $method => $value) {
            $mock->expects($this->any())
                ->method($method)
                ->will($this->returnValue($value));
        }

        return $mock;
    }

    /**
     * @param $className
     * @param $property
     * @param $value
     * @throws ReflectionException
     */
    public function setProtectedProperty($className, $property, $value)
    {
        $reflection = new ReflectionClass($className);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($className, $value);
    }
}
