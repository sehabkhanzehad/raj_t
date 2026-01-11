<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LoanResource;
use App\Http\Resources\Api\TransactionResource;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;

class BorrowingSectionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return LoanResource::collection(Loan::borrow()->with('loanable')->paginate(perPage()));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            "user_id" => ['nullable', 'exists:users,id'],
            "amount" => ['required', 'numeric', 'min:0'],
            "date" => ['required', 'date'],
            "description" => ['nullable', 'string'],
            "first_name" => ['required_if:user_id,null', 'string', 'max:255'],
            "last_name" => ['nullable', 'string', 'max:255'],
            "email" => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            "phone" => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($request) {
            $user = $request->user_id ? User::find($request->user_id) : User::create([
                "first_name" => $request->first_name,
                "last_name" => $request->last_name ?? null,
                "email" => $request->email ?? null,
                "phone" => $request->phone ?? null
            ]);

            $loan = Loan::firstOrCreate([
                'loanable_type' => User::class,
                'loanable_id' => $user->id,
                'direction' => 'borrow',
            ], [
                'amount' => 0,
            ]);

            $loan->increment('amount', $request->amount);

            $section = $loan->getSection();

            $transaction = $section->transactions()->create([
                'type' => 'income',
                'amount' => $request->amount,
                'before_balance' => $section->currentBalance(),
                'after_balance' => $section->currentBalance() + $request->amount,
                'date' => $request->date,
                'title' => 'Borrowing' . ' from ' . $user->fullName(),
                'description' => $request->description ?? 'Borrow from ' . $user->fullName(),
            ]);

            $transaction->references()->create([
                'referenceable_type' => Loan::class,
                'referenceable_id' => $loan->id,
            ]);
        });

        return $this->success("Borrowing record created successfully.", 201);
    }

    public function transactions(Loan $loan): AnonymousResourceCollection
    {
        return TransactionResource::collection($loan->transactions()->latest()->paginate(10));
    }

    public function show(Loan $loan): LoanResource
    {
        return new LoanResource($loan->load('loanable'));
    }
}
