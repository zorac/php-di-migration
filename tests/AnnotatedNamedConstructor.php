<?php

namespace DI\Test\Migration;

use DI\Annotation\Inject;

class AnnotatedNamedConstructor
{
    /** @Inject({"bar" = "Bar", "foo" = "Foo"}) */
    public function __construct(
        public $foo,
        public $bar,
    ) {
    }
}
