<?php

namespace DI\Test\Migration;

use DI\Annotation\Inject;

class AnnotatedUntypedMethod
{
    public $service;

    /** @Inject @param Service $service */
    public function test($service)
    {
        $this->service = $service;
    }
}
