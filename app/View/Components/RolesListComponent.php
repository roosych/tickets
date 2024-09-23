<?php

namespace App\View\Components;

use App\Models\Role;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RolesListComponent extends Component
{
    public Role $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function render(): View|Closure|string
    {
        return view('components.roles.roles-list-component');
    }
}
