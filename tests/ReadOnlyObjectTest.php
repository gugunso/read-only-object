<?php

namespace Gugunso\ReadOnlyObject;

use PHPUnit\Framework\TestCase;
use stdClass;
use IteratorAggregate;

/**
 * @coversDefaultClass \Gugunso\ReadOnlyObject\ReadOnlyObject
 * Gugunso\ReadOnlyObject\ReadOnlyObjectTest
 */
class ReadOnlyObjectTest extends TestCase
{
    /** @var $testClassName as test target class name */
    protected $testClassName = ReadOnlyObject::class;

    /**
     * @coversNothing
     */
    public function test___construct()
    {
        $targetClass = $this->createObject('なまえ', 55, 'my-pass');
        $this->assertInstanceOf(BaseObject::class, $targetClass);
        $this->assertInstanceOf(IteratorAggregate::class, $targetClass);
    }

    /**
     * テスト対象のインスタンスを作成する。
     * @param string $name
     * @param int $age
     * @param string $password
     * @return ReadOnlyObject
     */
    public function createObject(string $name, int $age, string $password)
    {
        return new class($name, $age, $password) extends ReadOnlyObject {
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
     * @covers ::toArray
     */
    public function test_toArray()
    {
        $targetClass = $this->createObject('なまえ', 55, 'my-pass');

        $actual = \Closure::bind(
            /** @var mixed $targetClass */
            function () use ($targetClass) {
                //assertions
                return $targetClass->toArray();
            },
            $this,
            $targetClass
        )->__invoke();

        $this->assertSame(['name'=>'なまえ','age'=>55,'object'=>'object-value'], $actual);

    }

    /**
     * @covers ::__set
     */
    public function test___set_RaiseException()
    {
        $this->expectException(\LogicException::class);
        $targetClass = $this->createObject('なまえ', 55, 'my-pass');
        $targetClass->name = 'this operation raise exception.';
    }

}
