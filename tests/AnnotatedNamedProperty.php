<?php

namespace DI\Test\Migration;

use DI\Annotation\Inject;

class AnnotatedNamedProperty
{
    /** @Inject("Foo") */
    public $foo;
}
