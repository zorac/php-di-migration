<?php

namespace DI\Test\Migration;

class UntypedConstructor
{
    /** @param Service $service */
    public function __construct(
        public $service,
    ) {
    }
}
