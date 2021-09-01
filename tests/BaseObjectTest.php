<?php

namespace Gugunso\ReadOnlyObject\Tests;

use Gugunso\ReadOnlyObject\BaseObject;
use Gugunso\ReadOnlyObject\ReadOnlyObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use LogicException;

/**
 * @coversDefaultClass \Gugunso\ReadOnlyObject\BaseObject
 * Gugunso\ReadOnlyObject\Tests\BaseObjectTest
 */
class BaseObjectTest extends TestCase
{
    /** @var $testClassName as test target class name */
    protected $testClassName = BaseObject::class;

    /**
     * @covers ::__construct
     * @covers ::retrievePropertyValuesAsArray
     * @covers ::publicPropertyNames
     * @covers ::getVars
     * @covers ::getAllowedKeys
     */
    public function test___construct_正常()
    {
        $this->createObject('なまえ', 55, 'my-pass');
        //例外が発生しないことを検査している　
        $this->assertTrue(true);
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
        return new class($name, $age, $password) extends BaseObject {
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
     * @covers ::__construct
     */
    public function test___construct_RaiseException()
    {
        $this->expectException(\LogicException::class);
        $this->createInvalidSubClass();
    }

    public function createInvalidSubClass()
    {
        //public property を持つが存在しているサブクラスを定義
        return new class() extends ReadOnlyObject {
            public $name;

            public function __construct()
            {
                //コンストラクタ呼び出し
                parent::__construct();
            }
        };
    }

    /**
     * @covers ::__get
     */
    public function test___get()
    {
        $targetClass = $this->createObject('なまえ', 55, 'my-pass');
        $this->assertSame('なまえ', $targetClass->name);
        $this->assertSame(55, $targetClass->age);
        $this->assertSame('object-value', $targetClass->object);
    }

    /**
     * @covers ::__get
     */
    public function test___get_RaiseError()
    {
        $this->expectError();
        $targetClass = $this->createObject('なまえ', 55, 'my-pass');
        $targetClass->pass;
    }

    /**
     * @covers ::getIterator
     * @covers ::retrievePropertyValuesAsArray
     */
    public function test_getIterator()
    {
        $targetClass = $this->createObject('なまえ', 55, 'my-pass');

        $actual1 = $targetClass->getIterator();
        $this->assertInstanceOf(\Traversable::class, $actual1);
        $this->assertSame(['name' => 'なまえ', 'age' => 55, 'object' => 'object-value'], iterator_to_array($actual1));


        \Closure::bind(
            function () use ($targetClass) {
                $targetClass->name = 'Changing the value does not affect the result of getIterator().';
            },
            $this,
            $targetClass
        )->__invoke();


        $actual2 = $targetClass->getIterator();
        $this->assertInstanceOf(\Traversable::class, $actual2);
        $this->assertSame(['name' => 'なまえ', 'age' => 55, 'object' => 'object-value'], iterator_to_array($actual2));
    }

    /**
     * @covers ::castValue
     */
    public function test_castValue()
    {
        $targetClass = $this->createObject('なまえ', 55, 'my-pass');
        $this->assertSame(55, $targetClass->age);
        $this->assertSame('object-value', $targetClass->object);
    }

    /**
     * @covers ::castValue
     */
    public function test_castValue_例外(){
        $this->expectException(LogicException::class);
        $this->createWithNoToStringObject();
    }


    public function createWithNoToStringObject()
    {
        return new class() extends BaseObject {
            protected $objectRaiseExceptionInCastValue;

            public function __construct()
            {
                $this->objectRaiseExceptionInCastValue = new class() extends stdClass {};
                parent::__construct();
            }
        };
    }
}
