<?php

namespace App\Console\Commands;

use App\Attributes\PolicyPermissionNameAttribute;
use Illuminate\Console\Command;
use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CreatePermissionsCommand extends Command
{
    protected $signature = 'permissions:create';

    public function handle(): int
    {
        $this->createPermissions();
        $this->info('Permissions created!');
        return CommandAlias::SUCCESS;
    }

    private function createPermissions()
    {
        //получаем политики которые зареганы в Auth Service Provider через Gate и метод policies()
        $policies = Gate::policies();
        foreach ($policies as $model => $policy)
        {
            // $policy - это класс, получаем его методы функцией  get_class_methods()
            $methods = $this->getPolicyMethods($policy);
            $permissionNameAttributes = $this->getPolicyMethodsAttributes($policy, PolicyPermissionNameAttribute::class);

            $mergedArray = $this->getMergedMethodsWithAttributes($methods, $permissionNameAttributes);

            $reflector = new ReflectionClass($policy);
            $policyNameAttributes = $reflector->getAttributes();

            $policyNameAttribute = '';
            foreach ($policyNameAttributes as $item) {
                $policyNameAttribute = $item->getArguments()[0];
            }

            //foreach ($methods as $method)
            foreach ($mergedArray as $item)
            {
                Permission::query()->updateOrCreate(
                    [
                        'action' => $item[0],
                        'model' => $model,
                    ],
                    [
                        'name' => $item[1],
                        'group' => $policyNameAttribute,
                        'action' => $item[0],
                        'model' => $model,
                    ],
                );
            }
        }
    }

    // фильтруем методы и возвращаем массив, чтобы не получать лишние которые приходят с трейтов
    public function getPolicyMethods($policy): array
    {
        $methods = get_class_methods($policy);
        // фильтр
        return array_filter($methods, function($method){
            return !in_array($method, [
                'denyWithStatus',
                'denyAsNotFound',
                '__construct',
            ]);
        });
    }

    public function getPolicyMethodsAttributes($policy, $attributeClassName): array
    {
        $reflector = new \ReflectionClass($policy);
        $attrs = [];
        foreach ($reflector->getMethods() as $method) {
            $attributes = $method->getAttributes($attributeClassName);
            foreach ($attributes as $attribute) {
                $attrs[] = $attribute->newInstance()->name;
            }
        }
        return $attrs;
    }

    public function getMergedMethodsWithAttributes(array $methods, array $attributes): array
    {
        $mergedCollection = collect($methods)
            ->intersectByKeys($attributes)
            ->map(function ($item, $key) use ($attributes) {
                return [$item, $attributes[$key]];
            });

        //dd($mergedCollection);

        return $mergedCollection->all();
    }
}
