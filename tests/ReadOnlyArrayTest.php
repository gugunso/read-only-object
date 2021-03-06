<?php

namespace Gugunso\ReadOnlyObject\Tests;

use Gugunso\ReadOnlyObject\BaseObject;
use Gugunso\ReadOnlyObject\ReadOnlyArray;
use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;
use ArrayAccess;
use IteratorAggregate;

/**
 * @coversDefaultClass \Gugunso\ReadOnlyObject\ReadOnlyArray
 * Gugunso\ReadOnlyObject\Tests\ReadOnlyArrayTest
 */
class ReadOnlyArrayTest extends TestCase
{
    /** @var $testClassName as test target class name */
    protected $testClassName = ReadOnlyArray::class;

    /**
     * @covers ::offsetExists
     */
    public function test_offsetExists()
    {
        $targetClass = $this->createObject('Yoshiki', 55, 'weAreX');

        //テスト対象メソッドの実行 / assertions
        //public property
        $this->assertTrue($targetClass->offsetExists('name'));
        //protected property
        $this->assertTrue($targetClass->offsetExists('age'));
        //protected property
        $this->assertTrue($targetClass->offsetExists('object'));
        //private property
        $this->assertFalse($targetClass->offsetExists('password'));
        //property doesnt exists
        $this->assertFalse($targetClass->offsetExists('address'));
    }

    public function createObject(string $name, int $age, string $password)
    {
        return new class($name, $age, $password) extends ReadOnlyArray {
            protected $name;
            protected $age;
            protected $object;
            private $password;

            /**
             *  constructor.
             * @param $name
             * @param $age
             * @param $password
             */
            public function __construct(string $name, int $age, string $password)
            {
                $this->name = $name;
                $this->age = $age;
                $this->object = new class() extends stdClass {
                    public function __toString(): string
                    {
                        return 'object-value';
                    }
                };
                $this->password = $password;

                parent::__construct();
            }
        };
    }

    /**
     * @covers ::offsetGet
     */
    public function test_offsetGet()
    {
        $targetClass = $this->createObject('Yoshiki', 55, 'weAreX');

        //テスト対象メソッドの実行 / assertions
        $this->assertSame('Yoshiki', $targetClass->offsetGet('name'));
        $this->assertSame(55, $targetClass->offsetGet('age'));
        $this->assertSame('object-value', $targetClass->offsetGet('object'));

        try {
            $targetClass->offsetGet('password');
            $this->assertTrue(false, 'Exception must be occurred.');
        } catch (\Throwable $e) {
            //例外が発生すれば良い、例外の型、メッセージは問わない。
            $this->assertTrue(true);
        }
    }

    /**
     * @covers ::toArray
     */
    public function test_toArray()
    {
        $targetClass = $this->createObject('Yoshiki', 55, 'weAreX');
        //1回目
        $actual = $targetClass->toArray();
        $this->assertSame(['name' => 'Yoshiki', 'age' => 55, 'object' => 'object-value'], $actual);
        //2回目
        $actual = $targetClass->toArray();
        $this->assertSame(['name' => 'Yoshiki', 'age' => 55, 'object' => 'object-value'], $actual);
        $this->assertSame(3, count($actual));
    }

    /**
     * @covers ::offsetSet
     */
    public function test_offsetSet()
    {
        $targetClass = $this->createObject('Yoshiki', 55, 'weAreX');
        $this->expectException(LogicException::class);
        $targetClass->offsetSet('anyOffset', 'aneValue');
    }

    /**
     * @covers ::offsetUnset
     */
    public function test_offsetUnset()
    {
        $targetClass = $this->createObject('Yoshiki', 55, 'weAreX');
        $this->expectException(LogicException::class);
        $targetClass->offsetUnset('anyOffset');
    }

    /**
     * @coversNothing
     */
    public function test___construct(){
        $targetClass = $this->createObject('Yoshiki', 55, 'weAreX');
        $this->assertInstanceOf(BaseObject::class,$targetClass);
        $this->assertInstanceOf(IteratorAggregate::class,$targetClass);
        $this->assertInstanceOf(ArrayAccess::class,$targetClass);
    }
}
