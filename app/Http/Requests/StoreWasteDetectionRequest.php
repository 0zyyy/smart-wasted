<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreWasteDetectionRequest extends FormRequest
{
    private const CLASS_MAP = [
        'organik'    => 'Organic',
        'organic'    => 'Organic',
        'anorganik'  => 'Anorganic',
        'anorganic'  => 'Anorganic',
        'inorganic'  => 'Anorganic',
        'b3'         => 'B3',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->input('waste_type') ?? $this->input('class') ?? $this->input('label');

        $normalized = is_string($raw)
            ? (self::CLASS_MAP[strtolower(trim($raw))] ?? null)
            : null;

        $location = $this->input('location');

        $this->merge([
            'waste_type_input' => $raw,
            'waste_type'       => $normalized,
            'location'         => is_string($location) ? strtoupper(trim($location)) : $location,
        ]);
    }

    public function rules(): array
    {
        $validLocations = \App\Models\Location::pluck('name')->toArray();

        return [
            'waste_type_input' => ['required', 'string'],
            'waste_type'       => ['required', Rule::in(['Organic', 'Anorganic', 'B3'])],
            'location'         => ['required', 'string', Rule::in($validLocations)],
            'confidence'       => ['required', 'numeric', 'between:0,1'],
            'device_id'        => ['sometimes', 'nullable', 'string', 'max:50'],
            'device_timestamp' => ['sometimes', 'nullable', 'numeric'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 400)
        );
    }
}
