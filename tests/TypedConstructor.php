<?php

namespace DI\Test\Migration;

class TypedConstructor
{
    public function __construct(
        public Service $service,
    ) {
    }
}
