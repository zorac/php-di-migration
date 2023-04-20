<?php

namespace DI\Test\Migration;

use DI\Attribute\Inject;

class AttributedPositionalConstructor
{
    #[Inject(['Foo', 'Bar'])]
    public function __construct(
        public $foo,
        public $bar,
    ) {
    }
}
