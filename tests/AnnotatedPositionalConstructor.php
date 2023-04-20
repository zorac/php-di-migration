<?php

namespace DI\Test\Migration;

use DI\Annotation\Inject;

class AnnotatedPositionalConstructor
{
    /** @Inject({"Foo", "Bar"}) */
    public function __construct(
        public $foo,
        public $bar,
    ) {
    }
}
