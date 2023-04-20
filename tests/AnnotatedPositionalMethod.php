<?php

namespace DI\Test\Migration;

use DI\Annotation\Inject;

class AnnotatedPositionalMethod
{
    public $foo;
    public $bar;

    /** @Inject({"Foo", "Bar"}) */
    public function test($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
