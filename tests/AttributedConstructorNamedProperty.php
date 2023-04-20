<?php

namespace DI\Test\Migration;

use DI\Attribute\Inject;

class AttributedConstructorNamedProperty
{
    public function __construct(
        #[Inject('Foo')]
        public $foo,
    ) {
    }
}
