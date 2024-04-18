<?php

namespace App\Http\Requests;

use App\Models\DemoTest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class ActivateDemoTestRequest extends FormRequest
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
            if ($this->isAlreadyActive()) {
                $validator->errors()->add('ref', 'The test with the given reference ID is already active.');
            }
        });
    }

    /**
     * Check if the test is already active
     *
     * @return bool
     */
    private function isAlreadyActive(): bool
    {
        $ref = $this->input('ref');
        return DemoTest::where('ref', $ref)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->messages();

        // Find the first error message
        $firstErrorKey = array_key_first($errors);
        $firstErrorMessage = $errors[$firstErrorKey][0];
        $formattedError = ["message" => $firstErrorMessage];

        throw new HttpResponseException(response()->json($formattedError, Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
