<?php

namespace DI\Test\Migration;

use DI\Attribute\Inject;

class AttributedMethodNamedProperty
{
    public $foo;

    #[Inject]
    public function test(#[Inject('Foo')] $foo)
    {
        $this->foo = $foo;
    }
}
