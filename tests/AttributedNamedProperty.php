<?php

namespace DI\Test\Migration;

use DI\Attribute\Inject;

class AttributedNamedProperty
{
    #[Inject('Foo')]
    public $foo;
}
