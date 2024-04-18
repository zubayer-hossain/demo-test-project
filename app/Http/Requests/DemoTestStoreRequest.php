<?php

namespace App\Http\Requests;

use App\Models\DemoTest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class DemoTestStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.ref' => ['required', 'string', 'distinct', 'regex:/^T-\d+$/'],
            '*.name' => 'required|string|max:255',
            '*.description' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for form request errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            '*.ref.required' => 'The ref is required for each item.',
            '*.ref.distinct' => 'Duplicate ref found for :input. Please remove duplicates.',
            '*.ref.regex' => 'Invalid ref format for ref :input. The ref must follow the format T-[number].',
            '*.name.required' => 'The name is required for each item.',
            '*.name.string' => 'The name must be a string.',
            '*.description.string' => 'The description must be a string.',
        ];
    }

    /**
     * Add custom validation rules.
     *
     * @param Validator $validator
     */
    protected function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $allData = $this->all();

            if (count($allData) === 0) {
                throw new HttpResponseException(response()->json([], Response::HTTP_OK));
            }

            if (count($allData) > 2000) {
                $validator->errors()->add('items', 'A maximum of 2000 items are allowed.');
            }

            if ($this->hasInactiveItems()) {
                $validator->errors()->add('items', 'One or more items are inactive and cannot be processed.');
            }
        });
    }

    /**
     * Check for inactive items in the request.
     *
     * @return bool
     */
    private function hasInactiveItems(): bool
    {
        $refs = array_column($this->input(), 'ref');
        return DemoTest::whereIn('ref', $refs)
            ->where('is_active', false)
            ->exists();
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors()
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
