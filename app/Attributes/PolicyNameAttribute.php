<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class PolicyNameAttribute
{
    public function __construct(public array $name) {}
}
