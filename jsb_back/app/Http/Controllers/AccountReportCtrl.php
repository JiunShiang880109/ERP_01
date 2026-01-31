<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Traits\AccountTraits;
use PDO;
use Carbon\Carbon;
use App\Http\Controllers\Tool;

class AccountReportCtrl extends Controller
{
    //試算表
    public function trialBalanceIndex(Request $request){
        //dd($request->all());

        //會計期間
        $year = (int)$request->input('fiscal_year', now()->year);
        $month = (int)$request->input('fiscal_month', now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $mode = $request->input('mode', 'accumulate');

        //期初
        $opening = DB::table('account_opening_balance')
            ->selectRaw("
                main_code,
                sub_code,
                item_code,
                ledger_code,
                SUM(CASE WHEN dc='借' THEN opening_amount ELSE 0 END) AS opening_debit,
                SUM(CASE WHEN dc='貸' THEN opening_amount ELSE 0 END) AS opening_credit
            ")
            ->where('fiscal_year', $year)
            ->groupBy('main_code', 'sub_code', 'item_code', 'ledger_code');
        //累積
        $carry = DB::table('account_voucher_item as i')
            ->join('account_voucher as v', 'v.id', '=', 'i.voucher_id')
            ->selectRaw("
                i.main_code,
                i.sub_code,
                i.item_code,
                i.ledger_code,
                SUM(CASE WHEN i.dc='借' THEN i.amount ELSE 0 END) AS carry_debit,
                SUM(CASE WHEN i.dc='貸' THEN i.amount ELSE 0 END) AS carry_credit
            ")
            ->where('v.voucher_date', '<', $startDate)
            ->whereYear('v.voucher_date', $year)
            ->whereIn('v.voucher_kind', [0, 1])
            ->groupBy('i.main_code', 'i.sub_code', 'i.item_code', 'i.ledger_code');
        //本期
        $period = DB::table('account_voucher_item as i')
            ->join('account_voucher as v', 'v.id', '=', 'i.voucher_id')
            ->selectRaw("
                i.main_code,
                i.sub_code,
                i.item_code,
                i.ledger_code,
                SUM(CASE WHEN i.dc='借' THEN i.amount ELSE 0 END) AS period_debit,
                SUM(CASE WHEN i.dc='貸' THEN i.amount ELSE 0 END) AS period_credit
            ")
            ->whereBetween('v.voucher_date', [$startDate, $endDate])
            ->whereIn('v.voucher_kind', [0, 1])
            ->groupBy('i.main_code', 'i.sub_code', 'i.item_code', 'i.ledger_code');

        //合併期初+本期
        //本期
        $query = DB::query()
            ->fromSub($opening, 'o')
            ->leftJoinSub($period, 'p', function ($join) {
                $join->on('o.main_code', '=', 'p.main_code')
                    ->on('o.sub_code', '=', 'p.sub_code')
                    ->on('o.item_code', '=', 'p.item_code')
                    ->whereRaw('IFNULL(o.ledger_code,0)=IFNULL(p.ledger_code,0)');
        })
        ->leftJoin('account_item as ai', function ($join) {
            $join->on('o.main_code', '=', 'ai.main_code')
                ->on('o.sub_code', '=', 'ai.sub_code')
                ->on('o.item_code', '=', 'ai.code');
        })
        ->leftJoin('account_ledger as al', function ($join) {
            $join->on('o.main_code', '=', 'al.main_code')
                ->on('o.sub_code', '=', 'al.sub_code')
                ->on('o.item_code', '=', 'al.item_code')
                ->whereRaw('IFNULL(o.ledger_code,0)=IFNULL(al.code,0)');
        });

        if ($mode === 'period') {
            $rows = $query->selectRaw("
                o.main_code,
                o.sub_code,
                o.item_code,
                o.ledger_code,

                CONCAT(
                    o.main_code,
                    o.sub_code,
                    o.item_code,
                    IF(o.ledger_code IS NULL,'',CONCAT('.',o.ledger_code))
                ) AS account_code,

                IF(o.ledger_code IS NULL, ai.name, CONCAT(ai.name,'-',al.name)) AS account_name,

                o.opening_debit,
                o.opening_credit,

                IFNULL(p.period_debit,0)  AS period_debit,
                IFNULL(p.period_credit,0) AS period_credit,

                (o.opening_debit + IFNULL(p.period_debit,0) - IFNULL(p.period_credit,0)) AS ending_debit,
                (o.opening_credit + IFNULL(p.period_credit,0) - IFNULL(p.period_debit,0)) AS ending_credit
            ")->get();
        }else if($mode === 'accumulate'){

            $query->leftJoinSub($carry, 'c', function ($join) {
                $join->on('o.main_code', '=', 'c.main_code')
                    ->on('o.sub_code', '=', 'c.sub_code')
                    ->on('o.item_code', '=', 'c.item_code')
                    ->whereRaw('IFNULL(o.ledger_code,0)=IFNULL(c.ledger_code,0)');
            });

            $rows = $query->selectRaw("
                o.main_code,
                o.sub_code,
                o.item_code,
                o.ledger_code,

                CONCAT(
                    o.main_code,
                    o.sub_code,
                    o.item_code,
                    IF(o.ledger_code IS NULL,'',CONCAT('.',o.ledger_code))
                ) AS account_code,

                IF(o.ledger_code IS NULL, ai.name, CONCAT(ai.name,'-',al.name)) AS account_name,

                (o.opening_debit + IFNULL(c.carry_debit,0) - IFNULL(c.carry_credit,0)) AS opening_debit,
                (o.opening_credit + IFNULL(c.carry_credit,0) - IFNULL(c.carry_debit,0)) AS opening_credit,

                IFNULL(p.period_debit,0)  AS period_debit,
                IFNULL(p.period_credit,0) AS period_credit,

                (
                    o.opening_debit
                    + IFNULL(c.carry_debit,0)
                    - IFNULL(c.carry_credit,0)
                    + IFNULL(p.period_debit,0)
                    - IFNULL(p.period_credit,0)
                ) AS ending_debit,

                (
                    o.opening_credit
                    + IFNULL(c.carry_credit,0)
                    - IFNULL(c.carry_debit,0)
                    + IFNULL(p.period_credit,0)
                    - IFNULL(p.period_debit,0)
                ) AS ending_credit
            ")->get();

        }

        $sumOpeningDebit  = $rows->sum('opening_debit');
        $sumOpeningCredit = $rows->sum('opening_credit');

        $sumPeriodDebit   = $rows->sum('period_debit');
        $sumPeriodCredit  = $rows->sum('period_credit');

        $sumEndingDebit   = $rows->sum('ending_debit');
        $sumEndingCredit  = $rows->sum('ending_credit');

        return view('accountReport.trialBalance', 
                    compact('year', 'month', 'rows', 'sumOpeningDebit', 'sumOpeningCredit', 'sumPeriodDebit', 'sumPeriodCredit', 'sumEndingDebit', 'sumEndingCredit'));
    }
    
    //總分類帳
    public function generalLedgerIndex(Request $request){
        //dd($request->all());
        //會計期間
        $year = (int)$request->input('fiscal_year', now()->year);
        $month = (int)$request->input('fiscal_month', now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $startKey = Tool::toSubjectSortKey($request->input('subject_key_start'));
        $endKey   = Tool::toSubjectSortKey($request->input('subject_key_end'));

        $isSearched = $request->hasAny(['fiscal_year', 'fiscal_month', 'subject_key_start', 'subject_key_end']);

        //科目選單
        $subjects = Tool::getSubjects();
        
        $subjectNameMap = Tool::getSubjectNameMap();

        //帳簿顯示科目
        //期初
        $opening = DB::table('account_opening_balance')
            ->selectRaw("
                SUM(CASE WHEN dc='借' THEN opening_amount ELSE 0 END) AS opening_debit,
                SUM(CASE WHEN dc='貸' THEN opening_amount ELSE 0 END) AS opening_credit
            ")
            ->where('fiscal_year', $year)
            ->when($startKey && $endKey, function ($q) use ($startKey, $endKey) {
                $q->whereBetween(Tool::subjectSortExpr(), [$startKey, $endKey]);
            });

        //本期
        $period = DB::table('account_voucher_item as i')
            ->join('account_voucher as v', 'v.id', '=', 'i.voucher_id')
            ->selectRaw("
                SUM(CASE WHEN i.dc='借' THEN i.amount ELSE 0 END) AS period_debit,
                SUM(CASE WHEN i.dc='貸' THEN i.amount ELSE 0 END) AS period_credit
            ")
            ->whereBetween('v.voucher_date', [$startDate, $endDate])
            ->whereIn('v.voucher_kind', [0, 1])
            ->when($startKey && $endKey, function ($q) use ($startKey, $endKey) {
                $q->whereBetween(Tool::subjectSortExpr('i'), [$startKey, $endKey]);
            });

        //期初餘額
        $openingDebit = $opening->value('opening_debit') ?? 0;
        $openingCredit = $opening->value('opening_credit') ?? 0;

        $openingBalance = $openingDebit - $openingCredit;

        //逐筆分錄
        $entries = DB::table('account_voucher_item as i')
            ->join('account_voucher as v', 'v.id', '=', 'i.voucher_id')
            ->select(
                'v.voucher_date',
                'v.voucher_code',
                'v.voucher_type',
                'v.voucher_kind',
                'i.main_code',
                'i.sub_code',
                'i.item_code',
                'i.ledger_code',
                'i.dc',
                'i.amount',
                'i.note'
            )
            ->whereBetween('v.voucher_date', [$startDate, $endDate])
            ->when($startKey && $endKey, function ($q) use ($startKey, $endKey) {
                $q->whereBetween(Tool::subjectSortExpr('i'), [$startKey, $endKey]);
            })
            ->whereIn('v.voucher_kind', [0, 1])
            ->orderBy('v.voucher_date')
            ->orderBy('i.id')
            ->get();

        //餘額
        $rows = collect();

        //借貸總額
        $totalDebit = 0;
        $totalCredit = 0;

        // 期初
        $balance = $openingBalance;
        $rows->push([
            'type' => 'opening',
            'date' => null,
            'voucher' => null,
            'voucher_type' => null,
            'voucher_kind' => null,
            'note' => '期初餘額',
            'debit'  => $balance > 0 ? $balance : null,
            'credit' => $balance < 0 ? $balance : null,
            'balance' => $balance,
        ]);

        //轉換
        $voucherTypeMap = Tool::VOUCHER_TYPE_MAP;
        $voucherKindMap = Tool::VOUCHER_KIND_MAP;

        foreach ($entries as $row) {

            if ($row->dc === '借') {
                $balance += $row->amount;
                $debit = $row->amount;
                $credit = null;

                $totalDebit += $row->amount;
            } else {
                $balance -= $row->amount;
                $debit = null;
                $credit = $row->amount;

                $totalCredit += $row->amount;
            }

            $subjectCode = sprintf(
                '%d%d%02d%s',
                $row->main_code,
                $row->sub_code,
                $row->item_code,
                $row->ledger_code ? '-' . $row->ledger_code : ''
            );

            $rows->push([
                'type' => 'entry',
                'subject_code' => $subjectCode,
                'subject_name' => $subjectNameMap[$subjectCode] ?? '-',
                'date' => $row->voucher_date,
                'voucher' => $row->voucher_code,
                'voucher_type' => $voucherTypeMap[$row->voucher_type] ?? '-',
                'voucher_kind' => $voucherKindMap[$row->voucher_kind] ?? '-',
                'note' => $row->note,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $balance, 
            ]);
        }

        return view('accountReport.generalLedger', compact(
                    'year',
                    'month',
                    'isSearched',
                    'subjects',
                    'rows',
                    'openingBalance',
                    'startKey',
                    'endKey',
                    'totalDebit',
                    'totalCredit'
                ));
    }
    //明細分類帳
    public function detailedLedgerIndex(Request $request){
        $year = (int)$request->input('fiscal_year', now()->year);
        $month = (int)$request->input('fiscal_month', now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $startKey = Tool::toSubjectSortKey($request->input('subject_key_start'));
        $endKey   = Tool::toSubjectSortKey($request->input('subject_key_end'));

        $isSearched = $request->hasAny(['fiscal_year', 'fiscal_month', 'subject_key_start', 'subject_key_end']);

        //科目選單
        $subjects = Tool::getSubjects();

        $subjectNameMap = Tool::getSubjectNameMap();

        //未查詢，不查帳
        if(!$isSearched || !$startKey || !$endKey){
            return view('accountReport.detailedLedger', compact(
                'year', 'month', 'isSearched', 'subjects'
            ));
        }
        //科目編碼
        $subjectSortExprNI = Tool::subjectSortExpr();
        $subjectSortExpr = Tool::subjectSortExpr('i');

        // 1) 期初（opening_balance）依科目彙總
        $openingBySubject = DB::table('account_opening_balance')
            ->selectRaw("
                {$subjectSortExprNI} as subject_sort,
                CONCAT(
                    main_code,
                    sub_code,
                    LPAD(item_code, 2, '0'),
                    CASE
                        WHEN ledger_code IS NOT NULL THEN CONCAT('-', CAST(ledger_code AS UNSIGNED))
                        ELSE ''
                    END
                ) as subject_code,
                SUM(CASE WHEN dc='借' THEN opening_amount ELSE 0 END) as debit,
                SUM(CASE WHEN dc='貸' THEN opening_amount ELSE 0 END) as credit
            ")
            ->where('fiscal_year', $year)
            ->whereBetween($subjectSortExprNI, [$startKey, $endKey])
            ->groupBy('subject_sort', 'subject_code')
            ->get()
            ->keyBy('subject_code');

        // 2) 期初加上「本期之前」累計（carry）依科目彙總
        $carryBySubject = DB::table('account_voucher_item as i')
            ->join('account_voucher as v', 'v.id', '=', 'i.voucher_id')
            ->selectRaw("
                CONCAT(
                    i.main_code,
                    i.sub_code,
                    LPAD(i.item_code, 2, '0'),
                    CASE
                        WHEN i.ledger_code IS NOT NULL THEN CONCAT('-', CAST(i.ledger_code AS UNSIGNED))
                        ELSE ''
                    END
                ) as subject_code,
                SUM(CASE WHEN i.dc='借' THEN i.amount ELSE 0 END) as debit,
                SUM(CASE WHEN i.dc='貸' THEN i.amount ELSE 0 END) as credit
            ")
            ->where('v.voucher_date', '<', $startDate)
            ->whereYear('v.voucher_date', $year) // 跨年度累計要移除
            ->whereIn('v.voucher_kind', [0, 1])
            ->whereBetween($subjectSortExpr, [$startKey, $endKey])
            ->groupBy('subject_code')
            ->get()
            ->keyBy('subject_code');

        // 3) 本期逐筆分錄（entries）
        $entries = DB::table('account_voucher_item as i')
            ->join('account_voucher as v', 'v.id', '=', 'i.voucher_id')
            ->select(
                'v.voucher_date',
                'v.voucher_code',
                'v.voucher_type',
                'v.voucher_kind',
                'i.main_code', 'i.sub_code', 'i.item_code', 'i.ledger_code',
                'i.dc', 'i.amount', 'i.note',
                DB::raw("
                    CONCAT(
                        i.main_code,
                        i.sub_code,
                        LPAD(i.item_code, 2, '0'),
                        CASE
                            WHEN i.ledger_code IS NOT NULL THEN CONCAT('-', CAST(i.ledger_code AS UNSIGNED))
                            ELSE ''
                        END
                    ) as subject_code
                "),
                DB::raw("{$subjectSortExpr} as subject_sort")
            )
            ->whereBetween('v.voucher_date', [$startDate, $endDate])
            ->whereIn('v.voucher_kind', [0, 1])
            ->whereBetween($subjectSortExpr, [$startKey, $endKey])
            ->orderBy('subject_sort')
            ->orderBy('v.voucher_date')
            ->orderBy('i.id')
            ->get()
            ->groupBy('subject_code');

        //轉換
        $voucherTypeMap = Tool::VOUCHER_TYPE_MAP;
        $voucherKindMap = Tool::VOUCHER_KIND_MAP;

        $rows = collect();
        $grandDebit = 0;
        $grandCredit = 0;

        // 依科目排序跑每個科目一段
        $allSubjectCodes = collect()
            ->merge($openingBySubject->keys())
            ->merge($carryBySubject->keys())
            ->merge($entries->keys())
            ->unique()
            ->sort();

        foreach ($allSubjectCodes as $subjectCode) {
            $openingDebit  = (float)($openingBySubject[$subjectCode]->debit  ?? 0);
            $openingCredit = (float)($openingBySubject[$subjectCode]->credit ?? 0);

            $carryDebit  = (float)($carryBySubject[$subjectCode]->debit  ?? 0);
            $carryCredit = (float)($carryBySubject[$subjectCode]->credit ?? 0);

            // 固定用「opening + carry」當期初
            $openingBalance = ($openingDebit + $carryDebit) - ($openingCredit + $carryCredit);
            $balance = $openingBalance;

            // 科目段落標題
            $rows->push([
                'type' => 'subject_header',
                'subject_code' => $subjectCode,
                'subject_name' => $subjectNameMap[$subjectCode] ?? '-',
            ]);

            // 期初列
            $rows->push([
                'type' => 'opening',
                'subject_code' => $subjectCode,
                'subject_name' => $subjectNameMap[$subjectCode] ?? '-',
                'date' => null,
                'voucher' => null,
                'voucher_type' => null,
                'voucher_kind' => null,
                'note' => '期初餘額',
                'debit'  => null,
                'credit' => null,
                'balance' => $balance,
            ]);

            // 本期逐筆
            $totalDebit = 0;
            $totalCredit = 0;

            foreach (($entries[$subjectCode] ?? collect()) as $e) {
                if ($e->dc === '借') {
                    $balance += (float)$e->amount;
                    $debit = (float)$e->amount;
                    $credit = null;
                    $totalDebit += $debit;
                    $grandDebit += $debit;
                } else {
                    $balance -= (float)$e->amount;
                    $debit = null;
                    $credit = (float)$e->amount;
                    $totalCredit += $credit;
                    $grandCredit += $credit;
                }

                $rows->push([
                    'type' => 'entry',
                    'subject_code' => $subjectCode,
                    'subject_name' => $subjectNameMap[$subjectCode] ?? '-',
                    'date' => $e->voucher_date,
                    'voucher' => $e->voucher_code,
                    'voucher_type' => $voucherTypeMap[$e->voucher_type] ?? '-',
                    'voucher_kind' => $voucherKindMap[$e->voucher_kind] ?? '-',
                    'note' => $e->note,
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $balance,
                ]);
            }

            // 每科目小計列（可選，但非常符合明細分類帳）
            $rows->push([
                'type' => 'subtotal',
                'subject_code' => $subjectCode,
                'subject_name' => $subjectNameMap[$subjectCode] ?? '-',
                'note' => '本期合計',
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'ending_balance' => $balance,
            ]);
        }

        // 你 blade tfoot 想顯示總合計就用這兩個
        $totalDebit = $grandDebit;
        $totalCredit = $grandCredit;

        return view('accountReport.detailedLedger', compact(
            'year',
            'month',
            'isSearched',
            'subjects',
            'rows',
            'totalDebit',
            'totalCredit'
        ));
    }
}
