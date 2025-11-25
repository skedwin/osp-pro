<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutMigrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'country_id' => 'required|integer',
            'application_date' => 'nullable|date',
            'marital_status' => 'required|string',
            'dependants' => 'required|string|max:10',
            'employment_status' => 'required|string',
            'current_employer' => 'required|integer',
            'workstation_type' => 'required|string',
            'workstation_id' => 'nullable|integer',
            'workstation_name' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'current_position' => 'nullable|string|max:255',
            'experience_years' => 'nullable|string|max:10',
            'duration_current_employer' => 'nullable|string|max:10',
            'planning_return' => 'required|string',
            'form_attached' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'outmigration_reason' => 'required|string',
            'verification_cadres' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'country_id.required' => 'Please select a destination country.',
            'country_id.integer' => 'Invalid country selection.',
            'marital_status.required' => 'Please select your marital status.',
            'dependants.required' => 'Please specify number of dependants.',
            'employment_status.required' => 'Please select your employment status.',
            'current_employer.required' => 'Please select your current employer.',
            'current_employer.integer' => 'Invalid employer selection.',
            'workstation_type.required' => 'Please select workstation type.',
            'planning_return.required' => 'Please indicate if you plan to return.',
            'outmigration_reason.required' => 'Please select a reason for outmigration.',
            'form_attached.file' => 'The attached form must be a valid file.',
            'form_attached.mimes' => 'The attached form must be a PDF, DOC, DOCX, JPG, JPEG, or PNG file.',
            'form_attached.max' => 'The attached form must not exceed 10MB.',
        ];
    }
}

