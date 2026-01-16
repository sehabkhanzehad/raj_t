<?php

namespace App\Http\Requests\Api\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePreRegRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "group_leader_id" => ["required", "integer", "exists:group_leaders,id"],
            'status' => ['required', Rule::in(['active', 'pending'])],
            'serial_no' => ['required_if:status,active', 'string', 'max:100'],
            'tracking_no' => ['required_if:status,active', 'string', 'max:100'],
            'bank_voucher_no' => ['required_if:status,active', 'string', 'max:100'],
            'voucher_name' => ['required_if:status,active', 'string', 'max:255'],
            'date' => ['required_if:status,active', 'date'],

            'pilgrim_type' => ['required', 'in:existing,new'],
            'pilgrim_id' => [
                Rule::requiredIf(fn() => $this->pilgrim_type === 'existing'),
                'exists:pilgrims,id'
            ],

            'new_pilgrim' => [
                Rule::requiredIf(fn() => $this->pilgrim_type === 'new'),
                'array'
            ],
            'new_pilgrim.avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'new_pilgrim.first_name' => ['required_with:new_pilgrim', 'string'],
            'new_pilgrim.first_name_bangla' => ['required_with:new_pilgrim', 'string'],
            'new_pilgrim.last_name' => ['nullable', 'string'],
            'new_pilgrim.last_name_bangla' => ['nullable', 'string'],
            'new_pilgrim.mother_name' => ['nullable', 'string'],
            'new_pilgrim.mother_name_bangla' => ['nullable', 'string'],
            'new_pilgrim.father_name' => ['nullable', 'string'],
            'new_pilgrim.father_name_bangla' => ['nullable', 'string'],
            'new_pilgrim.occupation' => ['nullable', 'string'],
            'new_pilgrim.spouse_name' => ['nullable', 'string'],
            'new_pilgrim.email' => ['nullable', 'email', 'unique:users,email'],
            'new_pilgrim.phone' => ['nullable', 'string'],
            'new_pilgrim.gender' => ['required_with:new_pilgrim', 'in:male,female,other'],
            'new_pilgrim.is_married' => ['required_with:new_pilgrim', 'boolean'],
            'new_pilgrim.nid' => ['required_with:new_pilgrim', 'string', 'unique:users,nid'],
            'new_pilgrim.birth_certificate_number' => ['nullable', 'string', 'unique:users,birth_certificate_number'],
            'new_pilgrim.date_of_birth' => ['required_with:new_pilgrim', 'date'],

            'new_pilgrim.present_address' => ['required', 'array'],
            'new_pilgrim.present_address.house_no' => ['nullable', 'string', 'max:255'],
            'new_pilgrim.present_address.road_no' => ['nullable', 'string', 'max:255'],
            'new_pilgrim.present_address.village' => ['required', 'string', 'max:255'],
            'new_pilgrim.present_address.post_office' => ['required', 'string', 'max:255'],
            'new_pilgrim.present_address.police_station' => ['required', 'string', 'max:255'],
            'new_pilgrim.present_address.district' => ['required', 'string', 'max:255'],
            'new_pilgrim.present_address.division' => ['required', 'string', 'max:255'],
            'new_pilgrim.present_address.postal_code' => ['required', 'string', 'max:20'],
            'new_pilgrim.present_address.country' => ['nullable', 'string', 'max:255'],

            'same_as_present_address' => ['required', 'boolean'],

            'new_pilgrim.permanent_address' => [
                Rule::requiredIf(fn() => !$this->sameAsPresentAddress()),
                'array'
            ],
            'new_pilgrim.permanent_address.house_no' => [
                Rule::requiredIf(fn() => !$this->sameAsPresentAddress()),
                'nullable',
                'string',
                'max:255'
            ],
            'new_pilgrim.permanent_address.road_no' => [
                Rule::requiredIf(fn() => !$this->sameAsPresentAddress()),
                'nullable',
                'string',
                'max:255'
            ],
            'new_pilgrim.permanent_address.village' => [
                Rule::requiredIf(fn() => !$this->sameAsPresentAddress()),
                'string',
                'max:255'
            ],
            'new_pilgrim.permanent_address.post_office' => [
                Rule::requiredIf(fn() => !$this->sameAsPresentAddress()),
                'string',
                'max:255'
            ],
            'new_pilgrim.permanent_address.police_station' => [
                Rule::requiredIf(fn() => !$this->sameAsPresentAddress()),
                'string',
                'max:255'
            ],
            'new_pilgrim.permanent_address.district' => [
                Rule::requiredIf(fn() => !$this->sameAsPresentAddress()),
                'string',
                'max:255'
            ],
            'new_pilgrim.permanent_address.division' => [
                Rule::requiredIf(fn() => !$this->sameAsPresentAddress()),
                'string',
                'max:255'
            ],
            'new_pilgrim.permanent_address.postal_code' => [
                Rule::requiredIf(fn() => !$this->sameAsPresentAddress()),
                'string',
                'max:20'
            ],
            'new_pilgrim.permanent_address.country' => [
                Rule::requiredIf(fn() => !$this->sameAsPresentAddress()),
                'nullable',
                'string',
                'max:255'
            ],

            'passport_type' => ['required', 'in:existing,new,none'],
            'passport_id' => [
                Rule::requiredIf(function () {
                    return $this->passport_type === 'existing';
                }),
                'exists:passports,id'
            ],
            'new_passport' => [
                Rule::requiredIf($this->newPassport(...)),
                'array'
            ],
            'new_passport.passport_number' => [
                Rule::requiredIf($this->newPassport(...)),
                'string',
                'unique:passports,passport_number'
            ],
            'new_passport.issue_date' => [
                Rule::requiredIf($this->newPassport(...)),
                'date'
            ],
            'new_passport.expiry_date' => [
                Rule::requiredIf($this->newPassport(...)),
                'date',
                'after:new_passport.issue_date'
            ],
            'new_passport.passport_type' => [
                Rule::requiredIf($this->newPassport(...)),
                'in:ordinary,official,diplomatic'
            ],
            'new_passport.file' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'new_passport.notes' => ['nullable', 'string'],
        ];
    }

    private function sameAsPresentAddress(): bool
    {
        return $this->boolean('same_as_present_address');
    }

    private function newPassport(): bool
    {
        return $this->passport_type === 'new';
    }
}
