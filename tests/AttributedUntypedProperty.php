<?php

namespace DI\Test\Migration;

use DI\Attribute\Inject;

class AttributedUntypedProperty
{
    /** @var Service */
    #[Inject]
    public $service;
}
