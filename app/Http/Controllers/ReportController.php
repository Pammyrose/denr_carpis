<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            $currentYear = Carbon::now()->year; // 2025
            $currentMonth = Carbon::now()->month; // 10
            $selectedYear = min((int)$request->input('year', $currentYear), $currentYear);
            $selectedMonth = min(max((int)$request->input('month', $currentMonth), 1), 12);
            $monthStart = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $daysInMonth = $monthStart->daysInMonth;

            DB::enableQueryLog();
            $assetsByDay = DB::table('assets')
                ->selectRaw('SUM(unit_qty) as quantity, DATE(purchase_date) as date')
                ->whereBetween('purchase_date', [$monthStart, $monthEnd])
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get()
                ->keyBy('date');
            \Log::info('assetsByDay Query:', DB::getQueryLog());

            $allDays = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($selectedYear, $selectedMonth, $day)->format('Y-m-d');
                $found = $assetsByDay->get($date);
                $allDays[] = [
                    'date' => $date,
                    'quantity' => $found ? (int)$found->quantity : 0,
                ];
            }

            DB::enableQueryLog();
            $inventoryYears = DB::table('assets')
                ->selectRaw('DISTINCT YEAR(purchase_date) as year')
                ->orderBy('year', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'value' => (string)($item->year ?? ''),
                        'label' => (string)($item->year ?? ''),
                    ];
                })
                ->filter(function ($item) use ($currentYear) {
                    return $item['value'] !== '' && $item['value'] <= $currentYear;
                });
            \Log::info('inventoryYears Query:', DB::getQueryLog());

            $staticYears = collect(range($currentYear - 3, $currentYear))->map(function ($year) {
                return [
                    'value' => (string)$year,
                    'label' => (string)$year,
                ];
            });

            $availableYears = $inventoryYears
                ->merge($staticYears)
                ->unique('value')
                ->sortByDesc('value')
                ->values();

            $availableMonths = collect(range(1, 12))->map(function ($month) {
                return [
                    'value' => sprintf('%02d', $month),
                    'label' => Carbon::createFromFormat('m', $month)->format('F'),
                ];
            });

            DB::enableQueryLog();
            $totalSpendByMonth = [];
            $startMonth = Carbon::create($selectedYear, $selectedMonth, 1)->subMonths(5)->startOfMonth();
            $endMonth = $monthEnd;

            for ($date = $startMonth->copy(); $date <= $endMonth; $date->addMonth()) {
                $monthStart = $date->copy()->startOfMonth();
                $monthEnd = $date->copy()->endOfMonth();
                $totalSpend = Inventory::whereBetween('purchase_date', [$monthStart, $monthEnd])
                    ->sum('unit_qty');
                $totalSpendByMonth[] = [
                    'month' => $date->format('Y-m'),
                    'label' => $date->format('M Y'),
                    'totalSpend' => (float)$totalSpend ?: 0.0,
                ];
            }
            \Log::info('totalSpendByMonth Query:', DB::getQueryLog());

            try {
                $assetsByLocation = DB::table('assets')
                    ->select('location', DB::raw('SUM(unit_qty) as value'))
                    ->groupBy('location')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->location => ['name' => $item->location, 'value' => (int)$item->value]];
                    })
                    ->toArray();

                $assetsByLocation = array_values($assetsByLocation);

                Log::info('Assets by Location', ['data' => $assetsByLocation]);
            } catch (\Exception $e) {
                Log::error('Error fetching assets by location: ' . $e->getMessage());
                $assetsByLocation = [];
            }

            return Inertia::render('report/Index', [
                'assetsByDay' => $allDays,
                'assetsByLocation' => $assetsByLocation,
                'availableYears' => $availableYears->isEmpty() ? [[
                    'value' => (string)$currentYear,
                    'label' => (string)$currentYear,
                ]] : $availableYears,
                'availableMonths' => $availableMonths,
                'selectedYear' => (string)$selectedYear,
                'selectedMonth' => sprintf('%02d', $selectedMonth),
                'daysInMonth' => $daysInMonth,
                'totalSpendByMonth' => $totalSpendByMonth,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in ReportController@index: ' . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
            abort(500, 'An error occurred while generating the report.');
        }
    }
}