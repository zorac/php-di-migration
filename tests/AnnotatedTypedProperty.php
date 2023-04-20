<?php

namespace DI\Test\Migration;

use DI\Annotation\Inject;

class AnnotatedTypedProperty
{
    /** @Inject */
    public Service $service;
}
