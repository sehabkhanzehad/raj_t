<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LoanResource;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LendingSectionController extends Controller
{

    public function index(Request $request): AnonymousResourceCollection
    {
        return LoanResource::collection(Loan::lend()->with('loanable')->paginate($request->get('per_page', 10)));
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
                'direction' => 'lend',
            ], [
                'amount' => 0,
                'date' => $request->date,
                'description' => $request->description,
            ]);

            $loan->increment('amount', $request->amount);

            $loan->transactions()->create([
                'section_id' => $loan->getSection()->id,
                'type' => 'expense',
                'amount' => $request->amount,
                'before_balance' => 0,
                'after_balance' => 0 + $request->amount,
                'date' => $request->date,
                'description' => $request->description ?? 'Lend to ' . $user->fullName(),
            ]);
        });

        return $this->success("Lending record created successfully.", 201);
    }

    public function update(Request $request, Loan $loan): JsonResponse
    {
        $request->validate([
            "date" => ['required', 'date'],
            "description" => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $loan) {
            $loan->update([
                'date' => $request->date,
                'description' => $request->description,
            ]);

            // For simplicity, not adjusting transactions here
        });

        return $this->success("Lending record updated successfully.");
    }

    public function destroy(Loan $loan): JsonResponse
    {
        DB::transaction(function () use ($loan) {
            $loan->transactions()->delete();
            $loan->delete();
        });

        return $this->success("Lending record deleted successfully.");
    }
}
