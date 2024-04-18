<?php

namespace App\Http\Requests;

use App\Models\DemoTest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class DeactivateDemoTestRequest extends FormRequest
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
            'ref' => 'required|exists:demo_test,ref',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'ref.required' => 'A reference ID is required.',
            'ref.exists' => 'The test with the given reference ID does not exist.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     */
    protected function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->isAlreadyInactive()) {
                $validator->errors()->add('ref', 'The test with the given reference ID is already inactive.');
            }
        });
    }

    /**
     * Check if the test is already inactive
     *
     * @return bool
     */
    private function isAlreadyInactive(): bool
    {
        $ref = $this->input('ref');
        return DemoTest::where('ref', $ref)
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
