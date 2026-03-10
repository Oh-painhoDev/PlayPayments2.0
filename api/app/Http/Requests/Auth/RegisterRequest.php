<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'account_type' => ['required', 'in:pessoa_fisica,pessoa_juridica'],
            'whatsapp' => ['required', 'string', 'min:10', 'max:20'],
            'cep' => ['required', 'string', 'min:8', 'max:10'],
            'terms_accepted' => ['required', 'accepted'],
        ];

        // Conditional validation based on account type
        if ($this->account_type === 'pessoa_fisica') {
            $rules['document'] = ['required', 'string', 'min:11', 'max:20', 'unique:users'];
            $rules['birth_date'] = ['required', 'date', 'before:today'];
        } else {
            $rules['document'] = ['required', 'string', 'min:14', 'max:20', 'unique:users'];
            $rules['business_type'] = ['required', 'string', 'max:100'];
            $rules['business_sector'] = ['required', 'string', 'max:100'];
        }

        return $rules;
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
            'password.required' => 'O campo senha é obrigatório.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'account_type.required' => 'Selecione o tipo de conta.',
            'whatsapp.required' => 'O campo WhatsApp é obrigatório.',
            'whatsapp.min' => 'Digite um número de WhatsApp válido.',
            'document.required' => 'O campo CPF/CNPJ é obrigatório.',
            'document.unique' => 'Este CPF/CNPJ já está em uso.',
            'cep.required' => 'O campo CEP é obrigatório.',
            'birth_date.required' => 'O campo data de nascimento é obrigatório.',
            'birth_date.before' => 'A data de nascimento deve ser anterior a hoje.',
            'birth_date.date' => 'Digite uma data válida.',
            'business_type.required' => 'O campo tipo de empresa é obrigatório.',
            'business_sector.required' => 'O campo ramo de atuação é obrigatório.',
            'terms_accepted.required' => 'Você deve aceitar os termos de uso.',
            'terms_accepted.accepted' => 'Você deve aceitar os termos de uso.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate CPF
            if ($this->account_type === 'pessoa_fisica' && $this->document) {
                $cpf = preg_replace('/[^0-9]/', '', $this->document);
                if (strlen($cpf) === 11 && !$this->isValidCPF($cpf)) {
                    $validator->errors()->add('document', 'CPF inválido.');
                }
            }

            // Validate CNPJ
            if ($this->account_type === 'pessoa_juridica' && $this->document) {
                $cnpj = preg_replace('/[^0-9]/', '', $this->document);
                if (strlen($cnpj) === 14 && !$this->isValidCNPJ($cnpj)) {
                    $validator->errors()->add('document', 'CNPJ inválido.');
                }
            }
        });
    }

    /**
     * Validate CPF
     */
    private function isValidCPF($cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate CNPJ
     */
    private function isValidCNPJ($cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpj) != 14) {
            return false;
        }

        // Validate first digit
        for ($i = 0, $j = 5, $sum = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $remainder = $sum % 11;
        if ($cnpj[12] != ($remainder < 2 ? 0 : 11 - $remainder)) {
            return false;
        }

        // Validate second digit
        for ($i = 0, $j = 6, $sum = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $remainder = $sum % 11;
        return $cnpj[13] == ($remainder < 2 ? 0 : 11 - $remainder);
    }
}