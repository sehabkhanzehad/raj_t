<?php

namespace App\Http\Controllers\Api;

use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TransactionRequest;
use App\Http\Resources\Api\GroupLeaderResource;
use App\Http\Resources\Api\LoanResource;
use App\Http\Resources\Api\PilgrimResource;
use App\Http\Resources\Api\RegistrationResource;
use App\Http\Resources\Api\SectionResource;
use App\Http\Resources\Api\TransactionResource;
use App\Models\Loan;
use App\Models\PreRegistration;
use App\Models\Registration;
use App\Models\Section;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransactionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TransactionResource::collection(
            Transaction::with(['section', 'references.referenceable'])
                ->latest()
                ->paginate(perPage())
        );
    }

    public function show(Transaction $transaction): TransactionResource
    {
        return new TransactionResource(
            $transaction->load(['section', 'references.referenceable'])
        );
    }

    public function sections(): AnonymousResourceCollection
    {
        return SectionResource::collection(Section::whereNotIn('type', [SectionType::Bank, SectionType::GroupLeader])->orderBy('name')->get());
    }

    public function loans(): AnonymousResourceCollection
    {
        return LoanResource::collection(Loan::all());
    }

    public function preRegistrations(): JsonResponse
    {
        $preRegistrations = PreRegistration::with('pilgrim.user', 'groupLeader')->get()->map(function ($preRegistration) {
            return [
                "type" => "pre-registration",
                "id" => $preRegistration->id,
                "attributes" => [
                    "serialNo" => $preRegistration->serial_no,
                    "bankVoucherNo" => $preRegistration->bank_voucher_no,
                    "date" => $preRegistration->date,
                ],
                "relationships" => [
                    "pilgrim" => new PilgrimResource($preRegistration->relationLoaded('pilgrim') ? $preRegistration->pilgrim : null),
                    "groupLeader" => new GroupLeaderResource($preRegistration->relationLoaded('groupLeader') ? $preRegistration->groupLeader : null),
                ],
            ];
        });

        return response()->json(['data' => $preRegistrations]);
    }

    public function registrations(): AnonymousResourceCollection
    {
        return RegistrationResource::collection(Registration::with('pilgrim.user')
            ->latest()
            ->get());
    }

    public function store(TransactionRequest $request): JsonResponse
    {
        $section = $request->section();

        if ($section->isloan()) {
            // Todo: Implement loan transaction  
            //Handle Loan specific logic like deducting amount from loan balance
            return $this->error('Loan section transactions are not supported yet.', 400);
        }

        // $isIncome = $request->type === 'income';

        $transaction = $section->transactions()->create([
            'type' => $request->type,
            'voucher_no' => $request->voucher_no,
            'title' => $request->title,
            'description' => $request->description,
            'before_balance' => $section->currentBalance(),
            'amount' => $request->amount,
            'after_balance' => $section->afterBalance($request),
            'date' => $request->date,
        ]);

        $transaction->addReferences($request);



        return $this->success("Transaction created successfully.", 201);
    }

    // 301.1/income                      | before balance|amount|after balance
    //11-12-2025 | Md Abu Bakar Siddique | 0             |1000  | 1000,
    //12-12-2025 | Md Abu Bakar Siddique | 1000          |500   | 1500





    public function update(TransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $section = $request->section();

        $transaction->update([
            'type' => $request->type,
            'voucher_no' => $request->voucher_no,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'date' => $request->date,
        ]);

        // Remove existing references and add new ones
        $transaction->references()->delete();
        $transaction->addReferences($request);

        return $this->success("Transaction updated successfully.");
    }
}
