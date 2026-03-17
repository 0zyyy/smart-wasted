<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSensorDataRequest extends FormRequest
{
    private const // Locations are validated dynamically from the database;

    private const BIN_TYPE_MAP = [
        'organik' => 'Organic',
        'anorganik' => 'Anorganic',
        'b3' => 'B3',
        'organic' => 'Organic',
        'anorganic' => 'Anorganic',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $binTypeInput = $this->isMethod('get')
            ? $this->query('lokasi', $this->query('bin_type'))
            : $this->input('lokasi', $this->input('bin_type'));

        $weight = $this->isMethod('get')
            ? $this->query('berat', $this->query('weight'))
            : $this->input('berat', $this->input('weight'));

        $volume = $this->isMethod('get')
            ? $this->query('volume')
            : $this->input('volume');

        $location = $this->isMethod('get')
            ? $this->query('device', $this->query('location', 'BB102'))
            : $this->input('device', $this->input('location', 'BB102'));

        $normalizedBinType = is_string($binTypeInput)
            ? (self::BIN_TYPE_MAP[strtolower(trim($binTypeInput))] ?? null)
            : null;

        $this->merge([
            'bin_type_input' => $binTypeInput,
            'bin_type' => $normalizedBinType,
            'weight' => $weight,
            'volume' => $volume,
            'location' => is_string($location) ? strtoupper(trim($location)) : $location,
        ]);
    }

    public function rules(): array
    {
        $validLocations = \App\Models\Location::pluck('name')->toArray();

        return [
            'bin_type_input' => ['required', 'string'],
            'bin_type' => ['required', Rule::in(['Organic', 'Anorganic', 'B3'])],
            'weight' => ['required', 'numeric', 'min:0'],
            'volume' => ['required', 'numeric', 'between:0,100'],
            'location' => ['required', 'string', Rule::in($validLocations)],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 400)
        );
    }
}

