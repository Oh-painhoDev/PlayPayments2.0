<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = $this->user();
        
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'whatsapp' => ['required', 'string', 'min:10', 'max:20'],
            'cep' => ['required', 'string', 'min:8', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'business_type' => ['nullable', 'string', 'max:100'],
            'business_sector' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before:today'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O campo nome é obrigatório.',
            'name.max' => 'O nome deve ter no máximo 255 caracteres.',
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'whatsapp.required' => 'O campo WhatsApp é obrigatório.',
            'whatsapp.min' => 'Digite um número de WhatsApp válido.',
            'cep.required' => 'O campo CEP é obrigatório.',
            'birth_date.before' => 'A data de nascimento deve ser anterior a hoje.',
            'birth_date.date' => 'Digite uma data válida.',
        ];
    }
}