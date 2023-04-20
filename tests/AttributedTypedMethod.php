<?php

namespace DI\Test\Migration;

use DI\Attribute\Inject;

class AttributedTypedMethod
{
    public $service;

    #[Inject]
    public function test(Service $service)
    {
        $this->service = $service;
    }
}
