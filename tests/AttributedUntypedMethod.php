<?php

namespace DI\Test\Migration;

use DI\Attribute\Inject;

class AttributedUntypedMethod
{
    public $service;

    /** @param Service $service */
    #[Inject]
    public function test($service)
    {
        $this->service = $service;
    }
}
