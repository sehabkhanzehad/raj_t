<?php

namespace App\Http\Controllers\Api;

use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TransactionRequest;
use App\Http\Resources\Api\GroupLeaderResource;
use App\Http\Resources\Api\PilgrimResource;
use App\Http\Resources\Api\RegistrationResource;
use App\Http\Resources\Api\SectionResource;
use App\Http\Resources\Api\TransactionResource;
use App\Models\PreRegistration;
use App\Models\Registration;
use App\Models\Section;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Transaction::with(['section', 'references.referenceable']);

        // Search by voucher number
        if ($request->has('search') && !empty($request->search)) {
            $query->where('voucher_no', 'like', '%' . $request->search . '%');
        }

        // Filter by date range
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('date', '<=', $request->end_date);
        }

        // Filter by specific date
        if ($request->has('date') && !empty($request->date)) {
            $query->where('date', $request->date);
        }

        return TransactionResource::collection(
            $query->latest()->paginate(perPage())
        );
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

    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        $request->validate([
            'voucher_no' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $transaction->update([
            'voucher_no' => $request->voucher_no,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return $this->success("Transaction updated successfully.");
    }

    public function sections(): AnonymousResourceCollection
    {
        return SectionResource::collection(Section::whereIn('type', [
            SectionType::Other,
            SectionType::Employee,
            SectionType::Bill,
        ])->orderBy('name')->get());
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

    public function overview(): JsonResponse
    {
        $totalTransactions = Transaction::count();
        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');
        $currentBalance = Section::all()->sum(fn($section) => $section->currentBalance());

        $today = now()->toDateString();
        $todayTransactionsCount = Transaction::where('date', $today)->count();
        $todayIncome = Transaction::where('date', $today)->where('type', 'income')->sum('amount');
        $todayExpense = Transaction::where('date', $today)->where('type', 'expense')->sum('amount');

        // Additional metrics
        $averageTransactionAmount = Transaction::avg('amount') ?? 0;
        $profitLoss = $totalIncome - $totalExpense;
        $incomePercentage = $totalIncome > 0 ? round(($totalIncome / ($totalIncome + $totalExpense)) * 100, 1) : 0;
        $expensePercentage = $totalExpense > 0 ? round(($totalExpense / ($totalIncome + $totalExpense)) * 100, 1) : 0;

        // Last 7 days transactions
        $last7DaysCount = Transaction::where('date', '>=', now()->subDays(7))->count();
        $last7DaysIncome = Transaction::where('date', '>=', now()->subDays(7))->where('type', 'income')->sum('amount');
        $last7DaysExpense = Transaction::where('date', '>=', now()->subDays(7))->where('type', 'expense')->sum('amount');

        // Transaction type distribution
        $incomeCount = Transaction::where('type', 'income')->count();
        $expenseCount = Transaction::where('type', 'expense')->count();

        // Monthly trends for last 12 months
        $monthlyData = Transaction::selectRaw('YEAR(date) as year, MONTH(date) as month, type, SUM(amount) as total')
            ->where('date', '>=', now()->subMonths(12))
            ->groupBy('year', 'month', 'type')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->groupBy(['year', 'month']);

        $trends = [];
        foreach ($monthlyData as $year => $months) {
            foreach ($months as $month => $data) {
                $income = $data->where('type', 'income')->sum('total');
                $expense = $data->where('type', 'expense')->sum('total');
                $trends[] = [
                    'month' => $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT),
                    'income' => $income,
                    'expense' => $expense,
                    'net' => $income - $expense,
                ];
            }
        }

        // Top sections by transaction count
        $topSections = Transaction::selectRaw('section_id, COUNT(*) as count')
            ->groupBy('section_id')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->with('section')
            ->get()
            ->map(fn($t) => ['section' => $t->section->name, 'count' => $t->count]);

        return response()->json([
            'total_transactions' => $totalTransactions,
            'total_income' => (float) $totalIncome,
            'total_expense' => (float) $totalExpense,
            'current_balance' => (float) $currentBalance,
            'today_transactions_count' => $todayTransactionsCount,
            'today_income' => (float) $todayIncome,
            'today_expense' => (float) $todayExpense,
            'average_transaction_amount' => round($averageTransactionAmount, 2),
            'profit_loss' => (float) $profitLoss,
            'income_percentage' => $incomePercentage,
            'expense_percentage' => $expensePercentage,
            'last_7_days_count' => $last7DaysCount,
            'last_7_days_income' => (float) $last7DaysIncome,
            'last_7_days_expense' => (float) $last7DaysExpense,
            'income_count' => $incomeCount,
            'expense_count' => $expenseCount,
            'monthly_trends' => $trends,
            'top_sections' => $topSections,
        ]);
    }
}
