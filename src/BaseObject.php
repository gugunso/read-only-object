<?php


namespace Gugunso\ReadOnlyObject;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use LogicException;
use Traversable;


class BaseObject implements IteratorAggregate
{
    /** @var array|null */
    private $readOnlyObjectTmpArray;

    /**
     * ReadOnlyObject constructor.
     * サブクラスでは、必ずサブクラスでの 初期化完了後にparent::__construct() を呼び出すこと
     */
    public function __construct()
    {
        //public property の個数を検査、public propertyはwritableなためインスタンス作成自体を許可しない。
        if (count($this->publicPropertyNames()) > 0) {
            throw new LogicException('this class has public property.');
        }
        //コンストラクタprivate property
        $this->readOnlyObjectTmpArray = $this->retrievePropertyValuesAsArray();
    }

    /**
     * @return array
     */
    private function publicPropertyNames(): array
    {
        return Closure::bind(
            function () {
                return array_keys(get_class_vars(static::class));
            },
            $this,
            null
        )->__invoke();
    }

    /**
     * @return array
     */
    protected function retrievePropertyValuesAsArray(): array
    {
        if (!is_null($this->readOnlyObjectTmpArray)) {
            return $this->readOnlyObjectTmpArray;
        }
        $asArray = [];
        foreach ($this->getVars() as $name => $value) {
            $asArray[$name] = $this->castValue($value);
        }
        $this->readOnlyObjectTmpArray = $asArray;
        return $asArray;
    }

    /**
     * @return Traversable
     */
    private function getVars(): Traversable
    {
        $objectVars = get_object_vars($this);
        foreach ($this->getAllowedKeys() as $name) {
            yield $name => $objectVars[$name];
        }
    }

    /**
     * @return array
     */
    private function getAllowedKeys()
    {
        $allowedKeys = array_keys(get_class_vars(static::class));
        if ($index = array_search('readOnlyObjectTmpArray', $allowedKeys)) {
            unset($allowedKeys[$index]);
        }
        return $allowedKeys;
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function castValue($value)
    {
        if (is_object($value)) {
            return (string)$value;
        }
        return $value;
    }

    /**
     * @param string|int $name
     * @return mixed
     */
    final public function __get($name)
    {
        $array = $this->retrievePropertyValuesAsArray();
        return $array[$name];
    }

    /**
     * @param mixed $name
     * @param mixed $value
     */
    final public function __set($name, $value)
    {
        throw new LogicException(
            'You have tried to set a value for ' . $name . '. ' . static::class . ' is read only.'
        );
    }

    /**
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->retrievePropertyValuesAsArray());
    }
}