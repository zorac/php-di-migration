<?php

namespace DI\Test\Migration;

use DI\Annotation\Inject;

class AnnotatedUntypedProperty
{
    /** @Inject @var Service */
    public $service;
}
