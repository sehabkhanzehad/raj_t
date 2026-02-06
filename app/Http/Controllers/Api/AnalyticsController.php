<?php

namespace App\Http\Controllers\Api;

use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\GroupLeader;
use App\Models\Loan;
use App\Models\Package;
use App\Models\Pilgrim;
use App\Models\PreRegistration;
use App\Models\Registration;
use App\Models\Section;
use App\Models\Transaction;
use App\Models\Umrah;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get comprehensive dashboard analytics
     */
    public function dashboard(Request $request)
    {
        $yearId = $request->input('year_id', currentYear()?->id);

        return response()->json([
            'overview' => $this->getOverviewStats($yearId),
            'financial' => $this->getFinancialStats($yearId),
            'registrations' => $this->getRegistrationStats($yearId),
            'loans' => $this->getLoanStats($yearId),
            'sections' => $this->getSectionStats($yearId),
            'trends' => $this->getTrendData($yearId),
        ]);
    }

    /**
     * Get list of years for selection
     */
    public function getYears()
    {
        return response()->json([
            'years' => Year::orderBy('start_date', 'desc')
                ->get()
                ->map(fn($y) => [
                    'id' => $y->id,
                    'name' => $y->name,
                    'start_date' => $y->start_date->format('Y-m-d'),
                    'end_date' => $y->end_date->format('Y-m-d'),
                    'is_active' => $y->status,
                ]),
            'current_year_id' => currentYear()?->id,
        ]);
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats($yearId)
    {
        $transactions = Transaction::where('year_id', $yearId);

        return [
            'total_pilgrims' => Pilgrim::count(),
            'total_registrations' => Registration::count(),
            'total_pre_registrations' => PreRegistration::count(),
            'total_umrah' => Umrah::count(),
            'total_transactions' => $transactions->count(),
            'total_income' => $transactions->clone()->where('type', 'income')->sum('amount'),
            'total_expense' => $transactions->clone()->where('type', 'expense')->sum('amount'),
            'net_balance' => $transactions->clone()->where('type', 'income')->sum('amount') -
                $transactions->clone()->where('type', 'expense')->sum('amount'),
        ];
    }

    /**
     * Get financial statistics
     */
    private function getFinancialStats($yearId)
    {
        $sections = Section::with('lastTransaction')->get();

        $bankBalance = $sections->where('type', SectionType::Bank)
            ->sum(fn($s) => $s->currentBalance());

        $groupLeaderBalance = $sections->where('type', SectionType::GroupLeader)
            ->sum(fn($s) => $s->currentBalance());

        $employeeBalance = $sections->where('type', SectionType::Employee)
            ->sum(fn($s) => $s->currentBalance());

        $billBalance = $sections->where('type', SectionType::Bill)
            ->sum(fn($s) => $s->currentBalance());

        $otherBalance = $sections->where('type', SectionType::Other)
            ->sum(fn($s) => $s->currentBalance());

        return [
            'bank_balance' => $bankBalance,
            'group_leader_balance' => $groupLeaderBalance,
            'employee_balance' => $employeeBalance,
            'bill_balance' => $billBalance,
            'other_balance' => $otherBalance,
            'total_balance' => $bankBalance + $groupLeaderBalance + $employeeBalance + $billBalance + $otherBalance,
            'section_breakdown' => [
                ['name' => 'Banks', 'value' => $bankBalance, 'type' => 'bank'],
                ['name' => 'Group Leaders', 'value' => $groupLeaderBalance, 'type' => 'group_leader'],
                ['name' => 'Employees', 'value' => $employeeBalance, 'type' => 'employee'],
                ['name' => 'Bills', 'value' => $billBalance, 'type' => 'bill'],
                ['name' => 'Others', 'value' => $otherBalance, 'type' => 'other'],
            ],
        ];
    }

    /**
     * Get registration statistics
     */
    private function getRegistrationStats($yearId)
    {
        $hajjPackages = Package::where('type', 'hajj')->get();
        $umrahPackages = Package::where('type', 'umrah')->get();

        return [
            'pre_registrations' => [
                'total' => PreRegistration::count(),
                'pending' => PreRegistration::where('status', 'pending')->count(),
                'approved' => PreRegistration::where('status', 'approved')->count(),
                'rejected' => PreRegistration::where('status', 'rejected')->count(),
            ],
            'registrations' => [
                'total' => Registration::count(),
                'by_package' => Registration::select('package_id', DB::raw('count(*) as count'))
                    ->groupBy('package_id')
                    ->with('package:id,name,type')
                    ->get()
                    ->map(fn($r) => [
                        'package_name' => $r->package?->name ?? 'Unknown',
                        'package_type' => $r->package?->type ?? 'unknown',
                        'count' => $r->count
                    ]),
            ],
            'umrah' => [
                'total' => Umrah::count(),
                'by_package' => Umrah::select('package_id', DB::raw('count(*) as count'))
                    ->groupBy('package_id')
                    ->with('package:id,name,type')
                    ->get()
                    ->map(fn($u) => [
                        'package_name' => $u->package?->name ?? 'Unknown',
                        'count' => $u->count
                    ]),
            ],
            'packages' => [
                'hajj' => [
                    'total' => $hajjPackages->count(),
                    'list' => $hajjPackages->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'price' => $p->price,
                        'registrations' => $p->registrations()->count(),
                    ]),
                ],
                'umrah' => [
                    'total' => $umrahPackages->count(),
                    'list' => $umrahPackages->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'price' => $p->price,
                        'registrations' => $p->umrahs()->count(),
                    ]),
                ],
            ],
        ];
    }

    /**
     * Get loan statistics
     */
    private function getLoanStats($yearId)
    {
        $lendings = Loan::where('direction', 'lend')->get();
        $borrowings = Loan::where('direction', 'borrow')->get();

        return [
            'lendings' => [
                'total' => $lendings->count(),
                'total_amount' => $lendings->sum('amount'),
                'total_paid' => $lendings->sum('paid_amount'),
                'total_due' => $lendings->sum(fn($l) => $l->amount - $l->paid_amount),
                'by_status' => [
                    'pending' => $lendings->where('status', 'pending')->count(),
                    'partial' => $lendings->where('status', 'partial')->count(),
                    'paid' => $lendings->where('status', 'paid')->count(),
                ],
            ],
            'borrowings' => [
                'total' => $borrowings->count(),
                'total_amount' => $borrowings->sum('amount'),
                'total_paid' => $borrowings->sum('paid_amount'),
                'total_due' => $borrowings->sum(fn($b) => $b->amount - $b->paid_amount),
                'by_status' => [
                    'pending' => $borrowings->where('status', 'pending')->count(),
                    'partial' => $borrowings->where('status', 'partial')->count(),
                    'paid' => $borrowings->where('status', 'paid')->count(),
                ],
            ],
        ];
    }

    /**
     * Get section statistics
     */
    private function getSectionStats($yearId)
    {
        return [
            'banks' => [
                'total' => Section::typeBank()->count(),
                'total_balance' => Section::typeBank()->get()->sum(fn($s) => $s->currentBalance()),
                'list' => Section::typeBank()->with('bank', 'lastTransaction')->get()->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'code' => $s->code,
                    'balance' => $s->currentBalance(),
                    'account_number' => $s->bank?->account_number,
                ]),
            ],
            'group_leaders' => [
                'total' => GroupLeader::count(),
                'total_balance' => Section::typeGroupLeader()->get()->sum(fn($s) => $s->currentBalance()),
                'list' => GroupLeader::with(['section.lastTransaction', 'user'])->get()->map(fn($gl) => [
                    'id' => $gl->id,
                    'name' => $gl->user ? trim($gl->user->first_name . ' ' . $gl->user->last_name) : $gl->group_name,
                    'phone' => $gl->user?->phone,
                    'balance' => $gl->section?->currentBalance() ?? 0,
                ]),
            ],
            'employees' => [
                'total' => Section::typeEmployee()->count(),
                'total_balance' => Section::typeEmployee()->get()->sum(fn($s) => $s->currentBalance()),
            ],
            'bills' => [
                'total' => Section::typeBill()->count(),
                'total_balance' => Section::typeBill()->get()->sum(fn($s) => $s->currentBalance()),
            ],
            'others' => [
                'total' => Section::typeOther()->count(),
                'total_balance' => Section::typeOther()->get()->sum(fn($s) => $s->currentBalance()),
            ],
        ];
    }

    /**
     * Get trend data for charts
     */
    private function getTrendData($yearId)
    {
        // Monthly transaction trends
        $monthlyTransactions = Transaction::where('year_id', $yearId)
            ->select(
                DB::raw('MONTH(date) as month'),
                DB::raw('YEAR(date) as year'),
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('year', 'month', 'type')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthName = date('M', mktime(0, 0, 0, $i, 1));
            $income = $monthlyTransactions->where('month', $i)->where('type', 'income')->first()?->total ?? 0;
            $expense = $monthlyTransactions->where('month', $i)->where('type', 'expense')->first()?->total ?? 0;

            $months[] = [
                'month' => $monthName,
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];
        }

        // Recent transactions
        $recentTransactions = Transaction::where('year_id', $yearId)
            ->with('section')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'type' => $t->type,
                'amount' => $t->amount,
                'section' => $t->section?->name,
                'date' => $t->date?->format('Y-m-d') ?? $t->date,
            ]);

        return [
            'monthly' => $months,
            'recent_transactions' => $recentTransactions,
        ];
    }

    /**
     * Get income vs expense comparison
     */
    public function incomeExpense(Request $request)
    {
        $yearId = $request->input('year_id', currentYear()?->id);
        $period = $request->input('period', 'monthly'); // daily, weekly, monthly, yearly

        $query = Transaction::where('year_id', $yearId);

        switch ($period) {
            case 'daily':
                $data = $query->select(
                    DB::raw('DATE(date) as period'),
                    'type',
                    DB::raw('SUM(amount) as total')
                )
                    ->groupBy('period', 'type')
                    ->orderBy('period')
                    ->get();
                break;

            case 'weekly':
                $data = $query->select(
                    DB::raw('YEARWEEK(date) as period'),
                    'type',
                    DB::raw('SUM(amount) as total')
                )
                    ->groupBy('period', 'type')
                    ->orderBy('period')
                    ->get();
                break;

            case 'yearly':
                $data = $query->select(
                    DB::raw('YEAR(date) as period'),
                    'type',
                    DB::raw('SUM(amount) as total')
                )
                    ->groupBy('period', 'type')
                    ->orderBy('period')
                    ->get();
                break;

            default: // monthly
                $data = $query->select(
                    DB::raw('DATE_FORMAT(date, "%Y-%m") as period'),
                    'type',
                    DB::raw('SUM(amount) as total')
                )
                    ->groupBy('period', 'type')
                    ->orderBy('period')
                    ->get();
                break;
        }

        return response()->json([
            'period' => $period,
            'data' => $data->groupBy('period')->map(function ($items, $period) {
                return [
                    'period' => $period,
                    'income' => $items->where('type', 'income')->first()?->total ?? 0,
                    'expense' => $items->where('type', 'expense')->first()?->total ?? 0,
                ];
            })->values(),
        ]);
    }

    /**
     * Get top performers (group leaders, packages, etc.)
     */
    public function topPerformers(Request $request)
    {
        $limit = $request->input('limit', 5);

        return response()->json([
            'top_packages' => Package::withCount('registrations')
                ->orderBy('registrations_count', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'type' => $p->type,
                    'price' => $p->price,
                    'registrations' => $p->registrations_count,
                ]),

            'top_group_leaders' => GroupLeader::withCount('pilgrims')
                ->with('user')
                ->orderBy('pilgrims_count', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn($gl) => [
                    'id' => $gl->id,
                    'name' => $gl->user ? trim($gl->user->first_name . ' ' . $gl->user->last_name) : $gl->group_name,
                    'phone' => $gl->user?->phone,
                    'pilgrims' => $gl->pilgrims_count,
                ]),
        ]);
    }
}
