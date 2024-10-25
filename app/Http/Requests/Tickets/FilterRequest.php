<?php

namespace App\Http\Requests\Tickets;

use App\Enums\FilterGroupingEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'filter.executor_id' => ['nullable', 'exists:users,id'],
            'filter.department_id' => ['nullable', 'exists:departments,id'],
            'date_range' => ['nullable', 'string'],
            'grouping' => ['nullable', Rule::enum(FilterGroupingEnum::class)],
            'filter.priorities_id' => ['nullable', 'array'],
            'filter.priorities_id.*' => ['integer', 'exists:priorities,id'],
        ];
    }

    public function prepareForValidation()
    {
        // Очищаем пустые фильтры
        if ($this->has('filter')) {
            $this->merge([
                'filter' => array_filter($this->filter, function ($value) {
                    return $value !== null && $value !== '';
                })
            ]);
        }
    }
}
