<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class PolicyPermissionNameAttribute
{
    public function __construct(public array $name) {}
}
