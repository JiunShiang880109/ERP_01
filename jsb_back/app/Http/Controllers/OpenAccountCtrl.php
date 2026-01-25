<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

use App\Models\Account_cate_main;
use App\Models\Account_cate_sub;
use App\Models\Account_closing_period;
use App\Models\Account_item;
use App\Models\Account_ledger;
use App\Models\Account_opening_balance;

class OpenAccountCtrl extends Controller
{
    //顯示
    public function openAccountIndex(){
        $categories = Account_cate_main::query()
            ->orderBy('account_category_main.code')
            ->get()
            ->map(function ($main) {

                $main->subCates = Account_cate_sub::where('main_code', $main->code)
                    ->orderBy('code')
                    ->get()
                    ->map(function ($sub) {

                    $sub->accountItems = Account_item::query()
                        ->where('account_item.main_code', $sub->main_code)
                        ->where('account_item.sub_code', $sub->code)
                        ->orderBy('code')
                        ->get()
                        ->map(function($item){

                        $item->accountLedgers = Account_ledger::query()
                            ->where('account_ledger.main_code', $item->main_code)
                            ->where('account_ledger.sub_code', $item->sub_code)
                            ->where('account_ledger.item_code', $item->code)
                            ->where('account_ledger.enable', 1)
                            ->orderBy('code')
                            ->get();

                        return $item;
                    });
                        
                    return $sub;
                });

            return $main;
        });

        return view('accountant.open_account', compact('categories'));
    }

    //儲存
    public function openAccountStore(Request $request)
    {
        $request->validate([
            'fiscal_year'  => 'required|integer',
            'fiscal_month' => 'required|integer|between:1,12',
            'items'        => 'required|array',
        ]);

        $itemsToSave = collect($request->items)
        ->filter(fn ($i) =>
            isset($i['opening_amount']) &&
            (float)$i['opening_amount'] !== 0.0
        )
        ->values();

        foreach ($itemsToSave as $idx => $item) {

            validator($item, [
                'main_code'       => 'required|integer',
                'sub_code'        => 'required|integer',
                'item_code'       => 'required|integer',
                'ledger_code'     => 'nullable',
                'dc'              => 'required|in:借,貸',   
                'opening_amount'  => 'required|numeric|min:0',
                'is_offset'       => 'nullable|boolean',
                'offset_start_date' => 'nullable|date',
            ])->validate();
        }

        $startYear = (int)$request->fiscal_year;
        $startMonth = (int)$request->fiscal_month;

        //年度防止重複開帳
        if(Account_opening_balance::where('fiscal_year', $startYear)->exists()){
            return back()->withInput()->with('error', '該年度已完成期初開帳，請勿重複操作!');
        }

        $openStart = Carbon::create($startYear, $startMonth, 1)->startOfDay();

        $totalDebit = 0;
        $totalCredit = 0;

        //作所有檢查
        foreach($itemsToSave as $item){
            $ledgerCode = $item['ledger_code'] ?? null;
            $ledgerCode = ($ledgerCode === '' ? null : (int)$ledgerCode);

            //立沖設定
            $isOffset = (int)($item['is_offset'] ?? 0);
            if ($isOffset === 1 && empty($item['offset_start_date'])) {
                return back()
                    ->withInput()
                    ->with('error', '設定立沖時必須填寫立沖起始日');
            }
            
            //防止起始日早於開帳年月
            if (!empty($item['offset_start_date'])) {
                $offsetDate = Carbon::parse($item['offset_start_date'])->startOfDay();

                if($offsetDate->lt($openStart)){
                    return back()
                        ->withInput()
                        ->with('error', '起始日需在開帳年月之後');
                }
            }

            //借貸平衡
            if ($item['dc'] === '借') {
                $totalDebit += (float)$item['opening_amount'];
            } else {
                $totalCredit += (float)$item['opening_amount'];
            }
        }

        if(bccomp((string)$totalDebit, (string)$totalCredit, 2)!== 0){
            return back()->withInput()->with('error', '期初借貸金額不平衡');
        }
        DB::beginTransaction();

        try {
                
            foreach ($itemsToSave as $item) {
                $ledgerCode = $item['ledger_code'] ?? null;
                $ledgerCode = ($ledgerCode === '' ? null : (int)$ledgerCode);

                Account_opening_balance::create([
                    'main_code' => (int)$item['main_code'],
                    'sub_code'  => (int)$item['sub_code'],
                    'item_code' => (int)$item['item_code'],
                    'ledger_code' => $ledgerCode,
                    'fiscal_year' => $startYear,
                    'fiscal_month' => $startMonth,
                    'opening_amount' => $item['opening_amount'],
                    'dc' => $item['dc'],
                    'is_offset' => (int)($item['is_offset'] ?? 0),
                    'offset_start_date' => ((int)($item['is_offset'] ?? 0) === 1)
                        ? ($item['offset_start_date'] ?? null)
                        : null,
                    'employeeId' => $request->employeeId,
                ]);
            }

            DB::commit();

            return back()->with('success', '已儲存期初開帳');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', '儲存失敗：' . $e->getMessage());
        }
    }
    //編輯
    public function openAccountEdit(){
        $categories = Account_cate_main::query()
            ->orderBy('account_category_main.code')
            ->get()
            ->map(function ($main) {

                $main->subCates = Account_cate_sub::where('main_code', $main->code)
                    ->orderBy('code')
                    ->get()
                    ->map(function ($sub) {

                    $sub->accountItems = Account_item::query()
                        ->where('account_item.main_code', $sub->main_code)
                        ->where('account_item.sub_code', $sub->code)
                        ->orderBy('code')
                        ->get()
                        ->map(function($item){

                        $item->accountLedgers = Account_ledger::query()
                            ->where('account_ledger.main_code', $item->main_code)
                            ->where('account_ledger.sub_code', $item->sub_code)
                            ->where('account_ledger.item_code', $item->code)
                            ->where('account_ledger.enable', 1)
                            ->orderBy('code')
                            ->get();

                        return $item;
                    });
                        
                    return $sub;
                });

            return $main;
        });

        $fiscalYear = now()->year;
        $openingBalance = Account_opening_balance::where('fiscal_year', $fiscalYear)
            ->get()
            ->keyBy(fn ($r) =>
                "{$r->main_code}-{$r->sub_code}-{$r->item_code}-" . ($r->ledger_code ?? 0)
            );
        // dd($openingBalance->keys()->take(20));
        if($openingBalance->isEmpty()){
            abort(404, '該會計年度無期初開帳資料，無法進行微調');
        }

        $fiscalMonth = $openingBalance->first()->fiscal_month;

        return view('accountant.edit_open_account', compact('categories','fiscalYear', 'fiscalMonth', 'openingBalance'));
    }
    public function openAccountUpdate(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'fiscal_year' => 'required|integer',
            'fiscal_month' => 'required|integer|between:1,12',
            'items' => 'required|array',
            'items.*.main_code'=>'required|integer',
            'items.*.sub_code' =>'required|integer',
            'items.*.item_code'=>'required|integer',
            'items.*.ledger_code'=>'nullable',
            'items.*.dc' => 'required|in:借,貸',
            'items.*.opening_amount' => 'required|numeric|min:0',
            'items.*.is_offset' => 'nullable|boolean',
            'items.*.offset_start_date' => 'nullable|date',
        ]);

        $year = (int)$request->fiscal_year;
        $month = (int)$request->fiscal_month;

        DB::transaction(function () use ($request, $year, $month) {

            foreach ($request->items as $row) {

                $ledgerCodeRaw = $row['ledger_code'] ?? null;
                $ledgerCodeRaw = is_string($ledgerCodeRaw) ? trim($ledgerCodeRaw) : $ledgerCodeRaw;
                $ledgerCode = ($ledgerCodeRaw === '' || $ledgerCodeRaw === null) ? null : (int)$ledgerCodeRaw;

                $ledgerKey = $ledgerCode === null ? 0 : $ledgerCode;

                // 找對應的期初帳
                $query = Account_opening_balance::query()
                    ->where('fiscal_year', $year)
                    ->where('fiscal_month', $month)
                    ->where('main_code', (int)$row['main_code'])
                    ->where('sub_code', (int)$row['sub_code'])
                    ->where('item_code', (int)$row['item_code'])
                    ->where('ledger_code_key', $ledgerKey);

                $opening = $query->lockForUpdate()->first();
                //防止偷補帳
                if (!$opening) {
                    $lc = $ledgerCode === null ? 'NULL' : $ledgerCode;
                    throw new \Exception(
                        "找不到期初帳：{$row['main_code']}-{$row['sub_code']}-{$row['item_code']}-{$row['ledger_code']}"
                    );
                }

                //一致性檢查
                if (
                    (string)$opening->dc !== (string)$row['dc'] ||
                    bccomp((string)$opening->opening_amount, (string)$row['opening_amount'], 2) !==0
                ) {
                    throw new \Exception('期初帳金額或借貸被異動，更新已中止');
                }

                $isOffset = (int)($row['is_offset'] ?? 0) === 1;

                //更新「允許變動」欄位
                $opening->update([
                    'is_offset'         => $isOffset,
                    'offset_start_date' => $isOffset
                        ? ($row['offset_start_date'] ?? null)
                        : null,
                ]);
            }
        });

        return redirect()
            ->back()
            ->with('success', '期初帳微調已成功更新');
    }
    //關帳
    public function closeAccount(){

        return view('accountant.close_account');
    }
    //關帳儲存
    public function closeAccountStore(Request $request){
        $request->validate([
            'fiscal_year' => 'required|integer',
            'closing_type' => 'required|in:month,year',
            'fiscal_month' => 'nullable|integer|min:1|max:12',
            'note'         => 'nullable|string',
        ]);

        //年/月結
        $year = $request->fiscal_year;
        $month = $request->closing_type === 'year' ? null : $request->fiscal_month;

        //檢查是否有期初帳
        $hasOpenAccount = Account_opening_balance::where('fiscal_year', $year)->exists();
        if(!$hasOpenAccount){
            return back()->withErrors('該會計年度無期初帳資料，無法進行關帳')->withInput();
        }

        //防止重複關
        if(Account_closing_period::isClosed($year, null)){
            return back()->withErrors('該會計年度已年結，禁止重複操作!')->withInput();
        }
        if ($request->closing_type === 'month') {
           $period = Account_closing_period::where('fiscal_year', $year)
                ->where('fiscal_month', $month)
                ->first();

            //已月結->不允許再關
            if($period && $period->is_closed){
                return back()->withErrors('該月份已完成月結')->withErrors();
            }

            Account_closing_period::updateOrcreate(
                [
                    'fiscal_year'  => $year,
                    'fiscal_month' => $month,
                ],
                [
                    'is_closed'    => 1,
                    'closed_at'    => now(),
                    'employeeId'   => $request->employeeId,
                    'note'         => $request->note,
                ]
            );

            return back()->with('success', '月結已完成');
        }

        //年結檢查:1-12月是否月結
        $closedMonths = Account_closing_period::where('fiscal_year', $year)
            ->whereNotNull('fiscal_month')
            ->where('is_closed', 1)
            ->pluck('fiscal_month')
            ->unique()
            ->toArray();

        $missingMonths = [];
        $missingMonths = array_diff(range(1, 12), $closedMonths);

        if(!empty($missingMonths) && !$request->boolean('force_close_months')){
            return back()->withErrors(
                '尚有未完成月結的月份：' . implode('、', $missingMonths)
            )
            ->with('need_confirm', true)
            ->with('missingMonths', $missingMonths)
            ->withInput();
            
        }
        DB::transaction(function () use ($missingMonths, $year, $request) {
            // 補齊月結：用 firstOrCreate 防止並發/重送造成 duplicate
            foreach ($missingMonths as $m) {
                Account_closing_period::firstOrCreate(
                    ['fiscal_year' => $year, 'fiscal_month' => $m],
                    [
                        'is_closed'  => 1,
                        'closed_at'  => now(),
                        'employeeId' => $request->employeeId,
                        'note'       => '系統於年結時自動補月結',
                    ]
                );
            }

            // 年結：如果你維持 fiscal_month = NULL，務必先檢查是否已存在
            $existsYearClose = Account_closing_period::where('fiscal_year', $year)
                ->whereNull('fiscal_month')
                ->where('is_closed', 1)
                ->exists();

            if (!$existsYearClose) {
                Account_closing_period::create([
                    'fiscal_year'  => $year,
                    'fiscal_month' => null,
                    'is_closed'    => 1,
                    'closed_at'    => now(),
                    'employeeId'   => $request->employeeId,
                    'note'         => '完成年度關帳（含自動補月結）',
                ]);
            }
        });

        return back()->with('success', '年結完成');
        
    }
    //關帳紀錄
    public function closeAccountDetail(){
        $logs = Account_closing_period::orderBy('fiscal_year', 'desc')
            ->orderByRaw('ISNULL(fiscal_month), fiscal_month desc')
            ->get();
        
        $yearClosed = Account_closing_period::where('fiscal_year', now()->year)
            ->whereNull('fiscal_month')
            ->where('is_closed', 1)
            ->exists();

        return view('accountant.close_account_detail', compact('logs', 'yearClosed'));
    }
    //重啟月份
    public function closeAccountreopen(Request $request){
        $request->validate([
            'fiscal_year' => 'required|integer',
            'fiscal_month' => 'required|integer|min:1|max:12',
        ]);

        $year = (int)$request->fiscal_year;
        $month = (int)$request->fiscal_month;

        //重開本年度 當月
        if($year !== now()->year || $month !== now()->month){
            return back()->withErrors('僅允許重開當月份月結');
        }

        //年結後禁止重開
        $yearClosed = Account_closing_period::where('fiscal_year', $year)
            ->whereNull('fiscal_month')
            ->where('is_closed', 1)
            ->exists();

        if($yearClosed){
            return back()->withErrors('已完成年結，禁止任何重開操作!');
        }

        //找該月月結紀錄
        $record = Account_closing_period::where('fiscal_year', $year)
            ->where('fiscal_month', $month)
            ->where('is_closed', 1)
            ->first();

        if(!$record){
            return back()->withErrors('找不到可重開的月結紀錄');
        }

        //重開
        $record->update([
            'is_closed' => 0,
            'closed_at' => null,
            'note'      => '重開月結(系統操作)',
        ]);

        return back()->with('success', "{$month} 月月結已重開");

    }
}
