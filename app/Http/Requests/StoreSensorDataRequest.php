<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSensorDataRequest extends FormRequest
{
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
        if ($this->isMethod('get')) {
            $binTypeInput    = $this->query('tipe');
            $weight          = $this->query('berat');
            $volume          = $this->query('volume');
            $location        = $this->query('lokasi');
            $deviceId        = $this->query('device');
            $deviceTimestamp = $this->query('ts');
        } else {
            $binTypeInput    = $this->input('bin_type');
            $weight          = $this->input('weight');
            $volume          = $this->input('volume');
            $location        = $this->input('location');
            $deviceId        = $this->input('device_id');
            $deviceTimestamp = $this->input('device_timestamp');
        }

        $normalizedBinType = is_string($binTypeInput)
            ? (self::BIN_TYPE_MAP[strtolower(trim($binTypeInput))] ?? null)
            : null;

        $this->merge([
            'bin_type_input'   => $binTypeInput,
            'bin_type'         => $normalizedBinType,
            'weight'           => $weight,
            'volume'           => $volume,
            'location'         => is_string($location) ? strtoupper(trim($location)) : $location,
            'device_id'        => $deviceId,
            'device_timestamp' => $deviceTimestamp,
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
            'device_id'        => ['sometimes', 'nullable', 'string', 'max:50'],
            'device_timestamp' => ['sometimes', 'nullable', 'numeric'],
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
