<?php

namespace DI\Test\Migration;

use DI\Attribute\Inject;

class AttributedNamedConstructor
{
    #[Inject(['bar' => 'Bar', 'foo' => 'Foo'])]
    public function __construct(
        public $foo,
        public $bar,
    ) {
    }
}
