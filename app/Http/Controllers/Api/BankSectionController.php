<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountType;
use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SectionResource;
use App\Http\Resources\Api\TransactionResource;
use App\Models\Bank;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class BankSectionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return SectionResource::collection(Section::typeBank()->with('bank')->paginate(10));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', "unique:sections,code", 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'branch' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:100'],
            'account_holder_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'account_type' => ['required', 'string', Rule::in(AccountType::values())],
            'routing_number' => ['nullable', 'string', 'max:100'],
            'swift_code' => ['nullable', 'string', 'max:100'],
            'opening_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request) {
            $section = Section::create([
                'code' => $request->code,
                'name' => $request->name,
                'type' => SectionType::Bank,
                'description' => $request->description ?? null,
            ]);

            Bank::create([
                'section_id' => $section->id,
                'branch' => $request->branch,
                'name' => $request->name,
                'account_number' => $request->account_number,
                'account_holder_name' => $request->account_holder_name,
                'address' => $request->address,
                'account_type' => $request->account_type,
                'routing_number' => $request->routing_number ?? null,
                'swift_code' => $request->swift_code ?? null,
                'opening_date' => $request->opening_date ?? null,
                'phone' => $request->phone ?? null,
                'telephone' => $request->telephone ?? null,
                'email' => $request->email ?? null,
                'website' => $request->website ?? null,
            ]);
        });

        return $this->success("Section created successfully.", 201);
    }

    public function show(Section $section): SectionResource
    {
        return new SectionResource($section->load('bank'));
    }

    public function update(Request $request, Section $section): JsonResponse
    {
        $request->validate([
            'code' => ['required', "string", Rule::unique('sections', 'code')->ignore($section), 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'branch' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:100'],
            'account_holder_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'account_type' => ['required', 'string', Rule::in(AccountType::values())],
            'routing_number' => ['nullable', 'string', 'max:100'],
            'swift_code' => ['nullable', 'string', 'max:100'],
            'opening_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $section) {
            $section->update([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description ?? null,
            ]);

            $section->bank->update([
                'branch' => $request->branch,
                'name' => $request->name,
                'account_number' => $request->account_number,
                'account_holder_name' => $request->account_holder_name,
                'address' => $request->address,
                'account_type' => $request->account_type,
                'routing_number' => $request->routing_number ?? null,
                'swift_code' => $request->swift_code ?? null,
                'opening_date' => $request->opening_date ?? null,
                'phone' => $request->phone ?? null,
                'telephone' => $request->telephone ?? null,
                'email' => $request->email ?? null,
                'website' => $request->website ?? null,
            ]);
        });

        return $this->success("Bank section updated successfully.");
    }

    public function bankSections(): AnonymousResourceCollection
    {
        return SectionResource::collection(Section::typeBank()->with('bank')->get());
    }

    public function deposit(Request $request, Section $section): JsonResponse
    {
        $request->validate([
            "voucher_no" => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric'],
            'date' => ['required', 'date'],
        ]);

        $section->transactions()->create([
            'type' => 'expense',
            'voucher_no' => $request->voucher_no,
            'title' => $request->title,
            'description' => $request->description,
            'before_balance' => $section->currentBalance(),
            'amount' => $request->amount,
            'after_balance' => $section->currentBalance() + $request->amount,
            'date' => $request->date,
        ]);

        return $this->success("Deposit transaction created successfully.", 201);
    }

    public function withdraw(Request $request, Section $section): JsonResponse
    {
        $request->validate([
            "voucher_no" => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric'],
            'date' => ['required', 'date'],
        ]);

        if ($section->currentBalance() < $request->amount) return $this->error("Insufficient balance for withdrawal.", 400);

        $section->transactions()->create([
            'type' => 'income',
            'voucher_no' => $request->voucher_no,
            'title' => $request->title,
            'description' => $request->description,
            'before_balance' => $section->currentBalance(),
            'amount' => $request->amount,
            'after_balance' => $section->currentBalance() - $request->amount,
            'date' => $request->date,
        ]);

        return $this->success("Withdraw transaction created successfully.", 201);
    }

    public function transactions(Section $section): AnonymousResourceCollection
    {
        return TransactionResource::collection($section->transactions()->latest()->paginate(10));
    }
}
