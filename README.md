[![Build Status](https://travis-ci.com/gugunso/read-only-object.svg?branch=main)](https://travis-ci.com/gugunso/read-only-object)

<a href="https://codeclimate.com/github/gugunso/read-only-object/maintainability"><img src="https://api.codeclimate.com/v1/badges/ed33032118663588cd31/maintainability" /></a>

<a href="https://codeclimate.com/github/gugunso/read-only-object/test_coverage"><img src="https://api.codeclimate.com/v1/badges/ed33032118663588cd31/test_coverage" /></a>

# read-only-object

A read-only abstract class used when developing in PHP

## このパッケージが提供する機能

Read Only風に振る舞うオブジェクトの抽象クラスです。以下のルールに従って利用してください。

- 外部からReadのみ可、Write不可としたいプロパティを、protected propertyとして定義する。
- public なプロパティは定義しない（public property を持つ場合、コンストラクタで例外となります）

- property への値のセットは、すべてコンストラクタみで行う。（setterを設けない）
- サブクラスのコンストラクタでの初期化が完了したあとで、ReadOnlyObjectのコンストラクタを呼び出す

以下に、実装サンプルを示します。

``` php
<?php

namespace App\Samples;

use Gugunso\ReadOnlyObject\ReadOnlyObject;

class SampleObject extends ReadOnlyObject
{
    protected $id;
    protected $name;
    protected $class;
    protected $rank;
    protected $email;

    private $password;

    /**
     * SampleObject constructor.
     * @param $id
     * @param $name
     * @param $class
     * @param $rank
     * @param $email
     */
    public function __construct($id, $name, $class, $rank, $email)
    {
        $this->id = $id;
        $this->name = $name;
        $this->class = $class;
        $this->rank = $rank;
        $this->email = $email;
        $this->password = '********';
        parent::__construct();
    }
}
```

上記のように定義したSampleObjectを利用する際には、プロパティアクセスを用います。

```php
$object = new SampleObject(1,'なまえ','クラス',15,'sample@example.com');
echo $object->id; // 1
echo $object->name; // 'なまえ'
echo $object->property_not_exist; // 「Undefined index: property_not_exist」エラーが発生
echo $object->password; // private property に対するアクセスも「Undefined index: password」エラーが発生
$object->id=2; //値の代入を行うと、LogicExceptionが発生
```

※id,nameは定義上protectedですが、マジックメソッド __get() を経由することでReadAccessが可能です。

※値の代入を試みた場合、LogicExceptionが発生します。



また、このオブジェクトはIteratorableです。foreach文中で扱う場合、property名 => 値 の形式で利用することができます。

```php
$object = new SampleObject(1,'なまえ','クラス',15,'sample@example.com');
foreach($object as $propertyName => $propertyValue){
    print $propertyName.':'.$propertyValue; //順に、id:1 name:なまえ class:クラス rank:15 email:sample@example.com が出力されます
}
```







