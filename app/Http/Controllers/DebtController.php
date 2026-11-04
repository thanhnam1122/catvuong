<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Partner;
use App\Models\Debt;
use App\Models\DebtPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isCompanyUser = $currentUser && !$currentUser->isAdmin();

        if ($isCompanyUser) {
            $companyId = $currentUser->company_id;
            $companies = Company::where('id', $companyId)->get();
            $partners = Partner::where('company_id', $companyId)->orderBy('name')->get();
        } else {
            $companyId = $request->get('company_id');
            $companies = Company::orderBy('name')->get();
            $partners = Partner::with('company')->orderBy('name')->get();
        }

        $query = Debt::with(['company', 'partner', 'payments' => function ($q) {
            $q->orderBy('payment_date', 'desc');
        }]);

        // Filter by company
        if ($companyId && $companyId !== 'all') {
            $query->where('company_id', $companyId);
        }

        // Filter by debt type
        if ($type && in_array($type, ['receivable', 'payable'])) {
            $query->where('type', $type);
        }

        // Filter by status
        if ($status && $status !== 'all') {
            if ($status === 'overdue') {
                $query->where(function ($q) {
                    $q->where('status', 'overdue')
                      ->orWhere(function ($sub) {
                          $sub->whereIn('status', ['pending', 'partial'])
                              ->whereNotNull('due_date')
                              ->where('due_date', '<', Carbon::today()->toDateString());
                      });
                });
            } else {
                $query->where('status', $status);
            }
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('partner', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $debts = $query->orderBy('issue_date', 'desc')->paginate(20)->withQueryString();

        // Calculate statistics based on current company filter
        $statsQuery = Debt::query();
        if ($companyId && $companyId !== 'all') {
            $statsQuery->where('company_id', $companyId);
        }

        // Họ nợ tôi (Receivables)
        $totalReceivable = (clone $statsQuery)->where('type', 'receivable')->sum('amount');
        $paidReceivable = (clone $statsQuery)->where('type', 'receivable')->sum('paid_amount');
        $remainingReceivable = max(0, $totalReceivable - $paidReceivable);

        // Tôi nợ họ (Payables)
        $totalPayable = (clone $statsQuery)->where('type', 'payable')->sum('amount');
        $paidPayable = (clone $statsQuery)->where('type', 'payable')->sum('paid_amount');
        $remainingPayable = max(0, $totalPayable - $paidPayable);

        // Công nợ ròng (Net Receivable)
        $netDebt = $remainingReceivable - $remainingPayable;

        // Số khoản quá hạn
        $today = Carbon::today()->toDateString();
        $overdueCount = (clone $statsQuery)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->whereRaw('(amount - paid_amount) > 0')
            ->count();

        return view('debt.index', compact(
            'companies',
            'partners',
            'debts',
            'companyId',
            'type',
            'status',
            'search',
            'totalReceivable',
            'paidReceivable',
            'remainingReceivable',
            'totalPayable',
            'paidPayable',
            'remainingPayable',
            'netDebt',
            'overdueCount'
        ));
    }

    public function storeDebt(Request $request)
    {
        if ($request->has('amount')) {
            $request->merge(['amount' => str_replace(',', '', (string)$request->amount)]);
        }
        if ($request->has('paid_amount')) {
            $request->merge(['paid_amount' => str_replace(',', '', (string)$request->paid_amount)]);
        }

        $currentUser = Auth::user();
        if ($currentUser && !$currentUser->isAdmin()) {
            $request->merge(['company_id' => $currentUser->company_id]);
        }

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'partner_id' => 'nullable',
            'new_partner_name' => 'nullable|string|max:255',
            'type' => 'required|in:receivable,payable',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Nếu người dùng nhập đối tác mới ngay trong form
        if (empty($validated['partner_id']) && !empty($validated['new_partner_name'])) {
            $partner = Partner::create([
                'company_id' => $validated['company_id'],
                'name' => trim($validated['new_partner_name']),
                'type' => $validated['type'] === 'receivable' ? 'customer' : 'supplier',
                'phone' => $request->input('new_partner_phone'),
            ]);
            $partnerId = $partner->id;
        } else {
            $partnerId = $validated['partner_id'];
        }

        if (!$partnerId) {
            return back()->with('error', 'Vui lòng chọn hoặc nhập tên đối tác/khách hàng.')->withInput();
        }

        // Tự động sinh mã phiếu
        $prefix = $validated['type'] === 'receivable' ? 'THU' : 'TRA';
        $code = 'CN-' . $prefix . '-' . date('ymd') . '-' . rand(100, 999);

        $paidAmount = (float)($validated['paid_amount'] ?? 0);
        $amount = (float)$validated['amount'];

        $status = 'pending';
        if ($paidAmount >= $amount && $amount > 0) {
            $status = 'completed';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        }

        $debt = Debt::create([
            'code' => $code,
            'company_id' => $validated['company_id'],
            'partner_id' => $partnerId,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'amount' => $amount,
            'paid_amount' => $paidAmount,
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'status' => $status,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Nếu có số tiền thanh toán ban đầu thì ghi nhận luôn 1 bản ghi thanh toán
        if ($paidAmount > 0) {
            DebtPayment::create([
                'debt_id' => $debt->id,
                'payment_date' => $validated['issue_date'],
                'amount' => $paidAmount,
                'payment_method' => 'transfer',
                'notes' => 'Thanh toán / Tạm ứng khi tạo phiếu',
            ]);
        }

        return redirect()->back()->with('success', "Đã thêm khoản công nợ {$debt->code} thành công!");
    }

    public function updateDebt(Request $request, $id)
    {
        $debt = Debt::findOrFail($id);

        if ($request->has('amount')) {
            $request->merge(['amount' => str_replace(',', '', (string)$request->amount)]);
        }

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'partner_id' => 'required|exists:partners,id',
            'type' => 'required|in:receivable,payable',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $debt->update([
            'company_id' => $validated['company_id'],
            'partner_id' => $validated['partner_id'],
            'type' => $validated['type'],
            'title' => $validated['title'],
            'amount' => (float)$validated['amount'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $debt->updateStatusAutomatically();

        return redirect()->back()->with('success', "Cập nhật công nợ {$debt->code} thành công!");
    }

    public function destroyDebt($id)
    {
        $debt = Debt::findOrFail($id);
        $code = $debt->code;
        $debt->delete();

        return redirect()->back()->with('success', "Đã xóa khoản công nợ {$code} thành công!");
    }

    public function addPayment(Request $request, $id)
    {
        $debt = Debt::findOrFail($id);

        if ($request->has('amount')) {
            $request->merge(['amount' => str_replace(',', '', (string)$request->amount)]);
        }

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'reference_code' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($debt, $validated) {
            DebtPayment::create([
                'debt_id' => $debt->id,
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_code' => $validated['reference_code'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Cập nhật lại tổng đã thanh toán
            $totalPaid = $debt->payments()->sum('amount');
            $debt->paid_amount = $totalPaid;
            $debt->updateStatusAutomatically();
        });

        return redirect()->back()->with('success', "Ghi nhận thanh toán " . number_format($validated['amount']) . " đ thành công!");
    }

    public function destroyPayment($paymentId)
    {
        $payment = DebtPayment::findOrFail($paymentId);
        $debt = $payment->debt;

        DB::transaction(function () use ($payment, $debt) {
            $payment->delete();
            $totalPaid = $debt->payments()->sum('amount');
            $debt->paid_amount = $totalPaid;
            $debt->updateStatusAutomatically();
        });

        return redirect()->back()->with('success', "Đã xóa bản ghi thanh toán và cập nhật lại số dư công nợ!");
    }

    // Quản lý công ty
    public function storeCompany(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Company::create($validated);

        return redirect()->back()->with('success', "Đã thêm công ty [{$validated['name']}] vào hệ thống!");
    }

    // Quản lý đối tác
    public function storePartner(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'tax_code' => 'nullable|string|max:50',
            'code' => 'nullable|string|max:50',
            'type' => 'required|in:customer,supplier,both',
            'representative' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Partner::create($validated);

        return redirect()->back()->with('success', "Đã thêm đối tác [{$validated['name']}] thành công!");
    }

    // Trang đối soát / In phiếu công nợ
    public function statement($id)
    {
        $debt = Debt::with(['company', 'partner', 'payments' => function ($q) {
            $q->orderBy('payment_date', 'asc');
        }])->findOrFail($id);

        return view('debt.statement', compact('debt'));
    }
}
