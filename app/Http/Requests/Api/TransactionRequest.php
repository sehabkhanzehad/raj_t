<?php

namespace App\Http\Requests\Api;

use App\Enums\SectionType;
use App\Models\Loan;
use App\Models\PreRegistration;
use App\Models\Registration;
use App\Models\Section;
use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
            'section_id' => ['required', 'exists:sections,id'],
            'type' => ['required', 'in:income,expense'],
            "voucher_no" => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric'],
            'date' => ['required', 'date'],
        ];

        $section = $this->section();

        if ($section?->isloan()) {
            $rules['loan_id'] = ['required', 'exists:loans,id'];

            $rules['type'][] = ($this->loan()->isLend()) ? 'in:income' : 'in:expense';
        }

        if ($section?->isPreRegistration()) {
            $rules['pre_registration_ids'] = ['required', 'array'];
            $rules['pre_registration_ids.*'] = ['exists:pre_registrations,id'];
        }

        if ($section?->isRegistration()) {
            $rules['registration_ids'] = ['required', 'array'];
            $rules['registration_ids.*'] = ['exists:registrations,id'];
        }

        if ($section?->isGroupLeader()) {
            $groupLeader = $section->groupLeader;
            if ($groupLeader?->pilgrim_required) {
                $rules['pre_registration_id'] =  ['required', 'exists:pre_registrations,id'];
            }
        }

        return $rules;
    }

    public function getReferenceConfig(): array
    {
        return match ($this->section()->type) {
            SectionType::Lend            => ['key' => 'loan_id', 'type' => Loan::class, 'isArray' => false],
            SectionType::Borrow          => ['key' => 'loan_id', 'type' => Loan::class, 'isArray' => false],
            SectionType::GroupLeader     => ['key' => 'pre_registration_id', 'type' => PreRegistration::class, 'isArray' => false],
            SectionType::Registration    => ['key' => 'registration_ids', 'type' => Registration::class, 'isArray' => true],
            SectionType::PreRegistration => ['key' => 'pre_registration_ids', 'type' => PreRegistration::class, 'isArray' => true],
            default                      => throw new \Exception('Unsupported section type for references'),
        };
    }

    public function section(): Section
    {
        return Section::findOrFail($this->section_id);
    }

    public function loan(): ?Loan
    {
        return $this->has('loan_id') ? Loan::find($this->loan_id) : null;
    }

    public function isIncome(): bool
    {
        return $this->type === 'income';
    }
}
