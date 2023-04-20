<?php

namespace DI\Test\Migration;

use DI\Annotation\Inject;

class AnnotatedTypedMethod
{
    public $service;

    /** @Inject */
    public function test(Service $service)
    {
        $this->service = $service;
    }
}
