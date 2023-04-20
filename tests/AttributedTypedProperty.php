<?php

namespace DI\Test\Migration;

use DI\Attribute\Inject;

class AttributedTypedProperty
{
    #[Inject]
    public Service $service;
}
