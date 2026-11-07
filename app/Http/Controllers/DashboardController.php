<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Partner;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Sơ đồ phân tích & Dashboard so sánh Hóa đơn Đầu vào - Đầu ra của tất cả doanh nghiệp
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user ? $user->isAdmin() : true;

        // Phân quyền dữ liệu theo vai trò
        if (!$isAdmin && $user && $user->company_id) {
            $companyId = $user->company_id;
            $companies = Company::where('id', $companyId)->get();
            $partners = Partner::where('company_id', $companyId)->orderBy('name')->get();
        } else {
            $companyId = $request->get('company_id', 'all');
            $companies = Company::orderBy('name')->get();
            if ($companyId && $companyId !== 'all') {
                $partners = Partner::where('company_id', $companyId)->orderBy('name')->get();
            } else {
                $partners = Partner::orderBy('name')->get();
            }
        }

        $partnerId = $request->get('partner_id', 'all');
        $concern = $request->get('concern', 'all');
        $timeRange = $request->get('time_range', 'all');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        // Xử lý bộ lọc thời gian
        $now = Carbon::now();
        if ($timeRange === 'this_month') {
            $fromDate = $now->copy()->startOfMonth()->toDateString();
            $toDate = $now->copy()->endOfMonth()->toDateString();
        } elseif ($timeRange === 'last_month') {
            $fromDate = $now->copy()->subMonth()->startOfMonth()->toDateString();
            $toDate = $now->copy()->subMonth()->endOfMonth()->toDateString();
        } elseif ($timeRange === 'this_quarter') {
            $fromDate = $now->copy()->firstOfQuarter()->toDateString();
            $toDate = $now->copy()->lastOfQuarter()->toDateString();
        } elseif ($timeRange === 'this_year') {
            $fromDate = $now->copy()->startOfYear()->toDateString();
            $toDate = $now->copy()->endOfYear()->toDateString();
        }

        // Xây dựng Query gốc theo bộ lọc
        $baseQuery = Invoice::query();

        if (!$isAdmin && $user && $user->company_id) {
            $baseQuery->where('company_id', $user->company_id);
        } elseif ($companyId && $companyId !== 'all') {
            $baseQuery->where('company_id', $companyId);
        }

        if ($partnerId && $partnerId !== 'all') {
            $baseQuery->where('partner_id', $partnerId);
        }

        if ($concern !== null && $concern !== 'all') {
            $baseQuery->where('is_concerned', (bool)$concern);
        }

        if ($fromDate) {
            $baseQuery->whereDate('issue_date', '>=', $fromDate);
        }
        if ($toDate) {
            $baseQuery->whereDate('issue_date', '<=', $toDate);
        }

        // 1. Thống kê tổng hợp Đầu Vào (in)
        $inQuery = (clone $baseQuery)->where('type', 'in');
        $totalIn = (float) (clone $inQuery)->sum('total_amount');
        $countIn = (clone $inQuery)->count();
        $paidIn = (float) (clone $inQuery)->sum('paid_amount');
        $remainIn = max(0, $totalIn - $paidIn);
        $paidInPercent = $totalIn > 0 ? round(($paidIn / $totalIn) * 100, 1) : 0;

        // 2. Thống kê tổng hợp Đầu Ra (out)
        $outQuery = (clone $baseQuery)->where('type', 'out');
        $totalOut = (float) (clone $outQuery)->sum('total_amount');
        $countOut = (clone $outQuery)->count();
        $paidOut = (float) (clone $outQuery)->sum('paid_amount');
        $remainOut = max(0, $totalOut - $paidOut);
        $paidOutPercent = $totalOut > 0 ? round(($paidOut / $totalOut) * 100, 1) : 0;

        // 3. Tỷ lệ % Luân Chuyển Dòng Tiền & Cân Đối
        $totalVolume = $totalIn + $totalOut;
        $inPercent = $totalVolume > 0 ? round(($totalIn / $totalVolume) * 100, 1) : 0;
        $outPercent = $totalVolume > 0 ? round(($totalOut / $totalVolume) * 100, 1) : 0;

        // Chênh lệch (Xuất Ra - Đầu Vào)
        $netDiff = $totalOut - $totalIn;
        $diffPercent = $totalIn > 0 ? round(($netDiff / $totalIn) * 100, 1) : ($totalOut > 0 ? 100 : 0);

        // 4. Thống kê chi tiết theo từng Doanh nghiệp (Đối tác)
        $invoicesGrouped = (clone $baseQuery)
            ->select(
                'partner_id',
                'type',
                DB::raw('COUNT(*) as inv_count'),
                DB::raw('COALESCE(SUM(total_amount), 0) as total_amount'),
                DB::raw('COALESCE(SUM(paid_amount), 0) as paid_amount')
            )
            ->groupBy('partner_id', 'type')
            ->get();

        $partnerStats = [];
        $activePartnerCount = 0;

        foreach ($partners as $p) {
            $inRow = $invoicesGrouped->first(fn($i) => $i->partner_id == $p->id && $i->type === 'in');
            $outRow = $invoicesGrouped->first(fn($i) => $i->partner_id == $p->id && $i->type === 'out');

            $pInTotal = $inRow ? (float)$inRow->total_amount : 0;
            $pInCount = $inRow ? (int)$inRow->inv_count : 0;
            $pInPaid = $inRow ? (float)$inRow->paid_amount : 0;
            $pInRemain = max(0, $pInTotal - $pInPaid);
            $pInPercent = $totalIn > 0 ? round(($pInTotal / $totalIn) * 100, 1) : 0;
            $pInPaidRate = $pInTotal > 0 ? round(($pInPaid / $pInTotal) * 100, 1) : 0;

            $pOutTotal = $outRow ? (float)$outRow->total_amount : 0;
            $pOutCount = $outRow ? (int)$outRow->inv_count : 0;
            $pOutPaid = $outRow ? (float)$outRow->paid_amount : 0;
            $pOutRemain = max(0, $pOutTotal - $pOutPaid);
            $pOutPercent = $totalOut > 0 ? round(($pOutTotal / $totalOut) * 100, 1) : 0;
            $pOutPaidRate = $pOutTotal > 0 ? round(($pOutPaid / $pOutTotal) * 100, 1) : 0;

            $pVolume = $pInTotal + $pOutTotal;
            $pBalance = $pOutTotal - $pInTotal;
            $hasInvoices = ($pInCount + $pOutCount) > 0;

            if ($hasInvoices) {
                $activePartnerCount++;
            }

            $partnerStats[] = [
                'partner' => $p,
                'in_total' => $pInTotal,
                'in_count' => $pInCount,
                'in_paid' => $pInPaid,
                'in_remain' => $pInRemain,
                'in_percent' => $pInPercent,
                'in_paid_rate' => $pInPaidRate,
                'out_total' => $pOutTotal,
                'out_count' => $pOutCount,
                'out_paid' => $pOutPaid,
                'out_remain' => $pOutRemain,
                'out_percent' => $pOutPercent,
                'out_paid_rate' => $pOutPaidRate,
                'volume' => $pVolume,
                'balance' => $pBalance,
                'has_invoices' => $hasInvoices,
            ];
        }

        // Sắp xếp: ưu tiên các doanh nghiệp có tổng giao dịch lớn nhất lên đầu
        usort($partnerStats, fn($a, $b) => $b['volume'] <=> $a['volume']);

        // 5. Chuẩn bị dữ liệu biểu đồ xu hướng theo 12 tháng (Timeline Chart)
        $targetYear = $now->year;
        if ($fromDate && $toDate && substr($fromDate, 0, 4) === substr($toDate, 0, 4)) {
            $targetYear = (int)substr($fromDate, 0, 4);
        }

        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthKey = sprintf('%04d-%02d', $targetYear, $m);
            $monthlyData[$monthKey] = [
                'label' => "T$m/" . substr($targetYear, 2),
                'in' => 0,
                'out' => 0,
            ];
        }

        $timelineBase = Invoice::query();
        if (!$isAdmin && $user && $user->company_id) {
            $timelineBase->where('company_id', $user->company_id);
        } elseif ($companyId && $companyId !== 'all') {
            $timelineBase->where('company_id', $companyId);
        }
        if ($partnerId && $partnerId !== 'all') {
            $timelineBase->where('partner_id', $partnerId);
        }
        if ($concern !== null && $concern !== 'all') {
            $timelineBase->where('is_concerned', (bool)$concern);
        }
        $timelineBase->whereYear('issue_date', $targetYear);

        $timelineResults = $timelineBase
            ->select(
                DB::raw("TO_CHAR(issue_date, 'YYYY-MM') as month_str"),
                'type',
                DB::raw('COALESCE(SUM(total_amount), 0) as total')
            )
            ->groupBy('month_str', 'type')
            ->get();

        foreach ($timelineResults as $tr) {
            if (isset($monthlyData[$tr->month_str])) {
                if ($tr->type === 'in') {
                    $monthlyData[$tr->month_str]['in'] = (float)$tr->total;
                } elseif ($tr->type === 'out') {
                    $monthlyData[$tr->month_str]['out'] = (float)$tr->total;
                }
            }
        }

        // 6. Dữ liệu biểu đồ so sánh Top Doanh nghiệp (Top 8 Doanh nghiệp có volume cao nhất)
        $topPartners = array_slice(array_filter($partnerStats, fn($item) => $item['has_invoices']), 0, 8);
        $barChartLabels = [];
        $barChartIn = [];
        $barChartOut = [];

        foreach ($topPartners as $tp) {
            $name = $tp['partner']->name;
            // Rút gọn tên nếu quá dài cho nhãn biểu đồ
            $shortName = mb_strlen($name) > 22 ? mb_substr($name, 0, 20) . '...' : $name;
            $barChartLabels[] = $shortName;
            $barChartIn[] = round($tp['in_total'], 2);
            $barChartOut[] = round($tp['out_total'], 2);
        }

        // 7. Top cơ cấu tỷ trọng % Đầu Vào & Đầu Ra (Pie/Donut Charts)
        // Top 5 DN đầu vào
        $sortedByIn = $partnerStats;
        usort($sortedByIn, fn($a, $b) => $b['in_total'] <=> $a['in_total']);
        $pieInLabels = [];
        $pieInData = [];
        $pieInPercents = [];
        $top5InSum = 0;
        foreach (array_slice($sortedByIn, 0, 5) as $item) {
            if ($item['in_total'] > 0) {
                $pieInLabels[] = mb_strlen($item['partner']->name) > 18 ? mb_substr($item['partner']->name, 0, 16) . '..' : $item['partner']->name;
                $pieInData[] = round($item['in_total'], 2);
                $pieInPercents[] = $item['in_percent'];
                $top5InSum += $item['in_total'];
            }
        }
        $otherIn = max(0, $totalIn - $top5InSum);
        if ($otherIn > 0) {
            $pieInLabels[] = 'Khác';
            $pieInData[] = round($otherIn, 2);
            $pieInPercents[] = round(($otherIn / $totalIn) * 100, 1);
        }

        // Top 5 DN đầu ra
        $sortedByOut = $partnerStats;
        usort($sortedByOut, fn($a, $b) => $b['out_total'] <=> $a['out_total']);
        $pieOutLabels = [];
        $pieOutData = [];
        $pieOutPercents = [];
        $top5OutSum = 0;
        foreach (array_slice($sortedByOut, 0, 5) as $item) {
            if ($item['out_total'] > 0) {
                $pieOutLabels[] = mb_strlen($item['partner']->name) > 18 ? mb_substr($item['partner']->name, 0, 16) . '..' : $item['partner']->name;
                $pieOutData[] = round($item['out_total'], 2);
                $pieOutPercents[] = $item['out_percent'];
                $top5OutSum += $item['out_total'];
            }
        }
        $otherOut = max(0, $totalOut - $top5OutSum);
        if ($otherOut > 0) {
            $pieOutLabels[] = 'Khác';
            $pieOutData[] = round($otherOut, 2);
            $pieOutPercents[] = round(($otherOut / $totalOut) * 100, 1);
        }

        return view('dashboard.index', compact(
            'isAdmin',
            'companies',
            'companyId',
            'partners',
            'partnerId',
            'concern',
            'timeRange',
            'fromDate',
            'toDate',
            'totalIn',
            'countIn',
            'paidIn',
            'remainIn',
            'paidInPercent',
            'totalOut',
            'countOut',
            'paidOut',
            'remainOut',
            'paidOutPercent',
            'totalVolume',
            'inPercent',
            'outPercent',
            'netDiff',
            'diffPercent',
            'partnerStats',
            'activePartnerCount',
            'targetYear',
            'monthlyData',
            'barChartLabels',
            'barChartIn',
            'barChartOut',
            'pieInLabels',
            'pieInData',
            'pieInPercents',
            'pieOutLabels',
            'pieOutData',
            'pieOutPercents'
        ));
    }
}

