<?php

namespace DI\Test\Migration;

use DI\Annotation\Inject;

class AnnotatedNamedMethod
{
    public $foo;
    public $bar;

    /** @Inject({"bar" = "Bar", "foo" = "Foo"}) */
    public function test($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
