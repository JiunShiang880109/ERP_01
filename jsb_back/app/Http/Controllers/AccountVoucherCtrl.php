<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\Models\Account_item;
use App\Models\Account_ledger;
use App\Models\Account_voucher;
use App\Models\Account_voucher_item;
use App\Models\Voucher_temp;
use App\Models\Voucher_temp_item;
use App\Http\Controllers\Tool;
use App\Models\Account_closing_period;
use App\Models\Account_opening_balance;

use App\Exceptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\FuncCall;

use Illuminate\Support\Facades\Log;
class AccountVoucherCtrl extends Controller
{
    public function voucherIndex() {
        $vouchers = Account_voucher::with(['items'])
            ->orderBy('voucher_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($vouchers as $voucher){
            $voucher->debit = $voucher->items->where('dc', 'D')->sum('amount');
            $voucher->credit = $voucher->items->where('dc', 'C')->sum('amount');
        }
            
        return view('accountant.voucher', compact('vouchers'));
    }

    public function voucherDetail($id){
        $voucher = Account_voucher::with([
            'items.mainCate'
        ])->findOrFail($id);

        $items = Account_voucher_item::query()
            ->where('voucher_id', $id)
            ->leftJoin('account_item as i', function ($join){
                $join->on('i.main_code', '=', 'account_voucher_item.main_code')
                    ->on('i.sub_code', '=', 'account_voucher_item.sub_code')
                    ->on('i.code', '=', 'account_voucher_item.item_code');
            })
            ->leftJoin('account_ledger as l', function ($join) {
                $join->on('l.main_code', '=', 'account_voucher_item.main_code')
                    ->on('l.sub_code', '=', 'account_voucher_item.sub_code')
                    ->on('l.item_code', '=', 'account_voucher_item.item_code')
                    ->on('l.code', '=', 'account_voucher_item.ledger_code');
            })
            ->select(
                'account_voucher_item.*',
                'i.name as item_name',
                'l.name as ledger_name'
            )
            ->get();

        return view('accountant.voucher_detail', compact('voucher', 'items'));
    }
    //新增
    public function voucherAdd(){

        $items = Account_item::query()
            ->leftjoin('account_ledger as l', function($join){
                $join->on('l.main_code', '=', 'account_item.main_code')
                    ->on('l.sub_code', '=', 'account_item.sub_code')
                    ->on('l.item_code', '=', 'account_item.code')
                    ->where('l.enable', 1);
            })->whereNull('account_item.deleted_at')
            ->select('account_item.*', 'l.code as ledger_code', 'l.name as ledger_name')
            ->orderBy('account_item.main_code')
            ->orderBy('account_item.sub_code')
            ->orderBy('account_item.code')
            ->get();

        return view('accountant.add_voucher', compact('items'));
        
    }

    public function voucherStore(Request $request){
        //dd($request->all());
        DB::beginTransaction();

        try {

            //基本驗證
            $rules = [
                'action' => 'required|in:store,save_temp',
                'voucher_type' => 'required|in:0,1,2',
                'voucher_kind' => 'nullable|in:0,1',
                'items'        => 'required|array|min:1',
                'items.*.dc'   => 'required|in:借,貸',
                'items.*.amount' => 'required|numeric|min:1',
                'items.*.main_code' => 'required',
                'items.*.sub_code'  => 'required',
                'items.*.item_code' => 'required',
            ];

            if($request->action === 'store'){
                $rules['voucher_date'] = 'required|date';
            }

            $request->validate($rules);

            //檢查借貸平衡
            $debit = 0;
            $credit = 0;

            foreach ($request->items as $item) {
                if ($item['dc'] === '借') {
                    $debit += $item['amount'];
                } else {
                    $credit += $item['amount'];
                }
            }

            switch($request->voucher_type){
                case '0'://收入
                    if($debit > 0){
                        return back()
                            ->withErrors(['balance' => '現金收入不可用借方'])->withInput();
                    }
                    break;
                case '1'://支出
                    if($credit > 0){
                        return back()
                            ->withErrors(['balance' => '現金支出不可用貸方'])->withInput();
                    }
                    break;
                case '2'://轉帳
                    if($debit !== $credit){
                        return back()
                            ->withErrors(['balance' => '轉帳借貸金額不平衡'])->withInput();
                    }
                    break;
            }

            $tool = new Tool();
            //action 分流
            switch($request->action){
                //一般
                case 'store':
                    //檢察關帳
                    $voucherDate = Carbon::parse($request->voucher_date);
                    $year = $voucherDate->year;
                    $month = $voucherDate->month;

                    //檢查是否有期初帳
                    $hasOpenAccount = Account_opening_balance::where('fiscal_year', $year)->exists();
                    if(!$hasOpenAccount){
                        return back()
                            ->withErrors(['close' => '該會計年度無期初帳資料，禁止新增傳票'])->withInput();
                    }

                    //年結
                    if(Account_closing_period::isClosed($year, null)){
                        return back()
                            ->withErrors(['close' => '此會計年度已關帳，禁止新增傳票'])->withInput();
                    }
                    //月結
                    if(Account_closing_period::isClosed($year, $month)){
                        //月結禁止一般傳票
                        if((int)$request->voucher_kind !== 1){
                            return back()
                                ->withErrors(['close' => "會計期間 {$year}年 {$month} 月已關帳，禁止新增傳票"])->withInput();
                        }

                        $closedYear = $year;
                        $closedMonth = $month;

                        //允許調整傳票補帳
                        if($voucherDate->year < $closedYear || ($voucherDate->year == $closedYear && $voucherDate->month <= $closedMonth)){
                            return back()
                                ->withErrors(['close' => '調整傳票需於下一會計期間開立'])->withInput();    
                        }
                        
                    }
                    //建立傳票主檔
                    $voucher = Account_voucher::create([
                        'voucher_date' => $request->voucher_date,
                        'voucher_code' => $tool->generateVoucherCode(
                            $request->voucher_date,
                            (int)$request->voucher_type),
                        'voucher_type' => $request->voucher_type,
                        'voucher_kind' => $request->voucher_kind ?? 0,
                        'employeeId'   => $request->employeeId,
                        'note'         => $request->note,
                    ]);

                    //建立分錄明細
                    foreach ($request->items as $item) {

                        Account_voucher_item::create([
                            'voucher_id' => $voucher->id,
                            'main_code'  => $item['main_code'],
                            'sub_code'   => $item['sub_code'],
                            'item_code'  => $item['item_code'],
                            'ledger_code'=> $item['ledger_code'] ?? null, // 允許 null
                            'dc'         => $item['dc'],
                            'amount'     => $item['amount'],
                            'note'       => $item['note'] ?? null,
                        ]);
                    }

                    DB::commit();

                    return redirect()
                        ->route('accountant.add_voucher')
                        ->with('success', '傳票新增成功');
                    
                //常用分錄
                case 'save_temp':
                    
                    //建立傳票主檔
                    $voucher = Voucher_temp::create([
                        'voucher_date' => $request->voucher_date,
                        'voucher_type' => $request->voucher_type,
                        'employeeId'   => $request->employeeId,
                        'note'         => $request->note,
                    ]);

                    //建立分錄明細
                    foreach ($request->items as $item) {

                        Voucher_temp_item::create([
                            'voucher_temp_id' => $voucher->id,
                            'main_code'  => $item['main_code'],
                            'sub_code'   => $item['sub_code'],
                            'item_code'  => $item['item_code'],
                            'ledger_code'=> $item['ledger_code'] ?? null, // 允許 null
                            'dc'         => $item['dc'],
                            'amount'     => $item['amount'],
                            'note'       => $item['note'] ?? null,
                        ]);
                    }

                    DB::commit();

                    return response()->json([
                        'status' => 'ok',
                        'message' => '已儲存為常用分錄',
                    ]);
                
                default:
                    throw new \Exception('未知操作');
            }
        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['system' => '系統錯誤：'.$e->getMessage()]);
        }
    }

    //常用分錄
    public function getVoucherTemp(){
        return Voucher_temp::select('id', 'note', 'voucher_type', 'created_at')
            ->orderByDesc('id')
            ->get();
    }
    public function getVoucherTempItems($id){
        return Voucher_temp::with('items')->findOrFail($id);
    }
    //編輯
    public function voucherEdit($id){
        //主檔
        $voucher = Account_voucher::with(['items'])
            ->findOrFail($id);

        if($voucher->voucher_kind === 2){
            return redirect()
                ->route('accountant.voucher_detail', $voucher->id)
                ->with('error', '此為系統結帳傳票，不可編輯');
        }

        //檢查是否為停用科目之交易資料
        $disabledLedger = Account_voucher_item::query()
            ->join('account_ledger as l', function($join){
                $join->on('l.main_code', '=', 'account_voucher_item.main_code')
                    ->on('l.sub_code', '=', 'account_voucher_item.sub_code')
                    ->on('l.item_code', '=', 'account_voucher_item.item_code')
                    ->on('l.code', '=', 'account_voucher_item.ledger_code');
            })->where('account_voucher_item.voucher_id', $voucher->id)
            ->where('l.enable', 0)
            ->exists();
        
        if($disabledLedger){
            return redirect()
                ->route('accountant.voucher_detail', $voucher->id)
                ->with('error', '此傳票目前屬於停用科目，僅能檢視，無法編輯');
        }
        
        //科目清單
        $items = Account_item::query()
            ->leftJoin('account_ledger as l', function($join){
                $join->on('l.main_code', '=', 'account_item.main_code')
                    ->on('l.sub_code', '=', 'account_item.sub_code')
                    ->on('l.item_code', '=', 'account_item.code')
                    ->where('l.enable', 1);
            })->select('account_item.*', 'l.code as ledger_code', 'l.name as ledger_name')
            ->whereNull('account_item.deleted_at')
            ->orderBy('account_item.main_code')
            ->orderBy('account_item.sub_code')
            ->orderBy('account_item.code')
            ->get();

        return view('accountant.edit_voucher', compact('voucher', 'items'));
    }

    public function voucherUpdate(Request $request, $id){
        //dd($request->all());
        //Log::info('voucherUpdate payload', $request->all());
        DB::beginTransaction();

        try {

            //基本驗證
            $rules=[
                'action' => 'required|in:store,save_temp',
                'voucher_type' => 'required|in:0,1,2',
                'items'        => 'required|array|min:1',
                'items.*.dc'   => 'required|in:借,貸',
                'items.*.amount' => 'required|numeric|min:1',
                'items.*.main_code' => 'required',
                'items.*.sub_code'  => 'required',
                'items.*.item_code' => 'required',
                'items.*.ledger_code' => 'nullable',
                'items.*.id' => 'nullable|integer',
            ];

            if($request->action === 'store'){
                $rules['voucher_date'] = 'required|date';
                $rules['voucher_kind'] = 'required|in:0,1,2';
            }

            $request->validate($rules);
            
            //檢查借貸平衡
            $debit = 0;
            $credit = 0;

            foreach ($request->items as $item) {
                if ($item['dc'] === '借') {
                    $debit += $item['amount'];
                } else {
                    $credit += $item['amount'];
                }
            }

            switch($request->voucher_type){
                case '0'://收入
                    if($debit > 0){
                        return back()
                            ->withErrors(['balance' => '現金收入不可用借方'])->withInput();
                    }
                    break;
                case '1'://支出
                    if($credit > 0){
                        return back()
                            ->withErrors(['balance' => '現金支出不可用貸方'])->withInput();
                    }
                    break;
                case '2'://轉帳
                    if($debit !== $credit){
                        return back()
                            ->withErrors(['balance' => '轉帳借貸金額不平衡'])->withInput();
                    }
                    break;
            }

            //action 分流
            switch($request->action){
                //一般
                case 'store':
                    //檢察關帳
                    $voucherDate = Carbon::parse($request->voucher_date);
                    $year = $voucherDate->year;
                    $month = $voucherDate->month;

                    //年結
                    if(Account_closing_period::isClosed($year, null)){
                        return back()->withInput()
                            ->withErrors(['close' => '此會計年度已關帳，禁止新增傳票']);
                    }
                    //月結
                    if(Account_closing_period::isClosed($year, $month)){
                        //月結禁止一般傳票
                        if((int)$request->voucher_kind !== 1){
                            return back()
                                ->withErrors(['close' => "會計期間 {$year}年 {$month} 月已關帳，禁止新增傳票"])->withInput();
                        }

                        $closedYear = $year;
                        $closedMonth = $month;

                        //允許調整傳票補帳
                        if($voucherDate->year < $closedYear || ($voucherDate->year == $closedYear && $voucherDate->month <= $closedMonth)){
                            return back()
                                ->withErrors(['close' => '調整傳票需於下一會計期間開立'])->withInput();    
                        }
                        
                    }
                    //更新傳票主檔
                    $voucher = Account_voucher::with(['items'])->findOrFail($id);

                    if($voucher->voucher_kind === 2){
                        throw new \Exception('系統結帳傳票不可修改');
                    }

                    $voucher->update([
                        'voucher_date' => $request->voucher_date,
                        'voucher_type' => $request->voucher_type,
                        'voucher_kind' => $request->voucher_kind,
                        'employeeId'   => $request->employeeId,
                        'note'         => $request->note,
                    ]);

                    //查詢對應的分錄id
                    $existItemIds = $voucher->items->pluck('id')->all();

                    //確認表單送來item id
                    $incomingIds = collect($request->items)
                        ->pluck('id')
                        ->filter()
                        ->map(fn($v) =>(int)$v)
                        ->all();

                    //若表單沒有送來原有itemId 表示被刪除
                    $deleteIds = array_diff($existItemIds, $incomingIds);
                    if(!empty($deleteIds)){
                        Account_voucher_item::whereIn('id', $deleteIds)->delete();
                    }

                    //更新分錄明細
                    foreach ($request->items as $item) {
                        $payload = [
                            'voucher_id' => $voucher->id,
                            'main_code'  => $item['main_code'],
                            'sub_code'   => $item['sub_code'],
                            'item_code'  => $item['item_code'],
                            'ledger_code'=> $item['ledger_code'] ?? null, // 允許 null
                            'dc'         => $item['dc'],
                            'amount'     => $item['amount'],
                            'note'       => $item['note'] ?? null,
                        ];
                        
                        if(!empty($item['id'])) {
                            //更新指定的id
                            Account_voucher_item::where('id', $item['id'])
                                ->where('voucher_id', $voucher->id)
                                ->update($payload);
                        }else{
                            Account_voucher_item::create($payload);
                        }
                    }

                    DB::commit();

                    return redirect()
                        ->route('accountant.edit_voucher', $voucher->id)
                        ->with('success', '傳票更新成功');
                    
                //常用分錄
                case 'save_temp':
                    
                    //建立傳票主檔
                    $voucher = Voucher_temp::create([
                        'voucher_date' => $request->voucher_date,
                        'voucher_type' => $request->voucher_type,
                        'employeeId'   => $request->employeeId,
                        'note'         => $request->note,
                    ]);

                    //建立分錄明細
                    foreach ($request->items as $item) {

                        Voucher_temp_item::create([
                            'voucher_temp_id' => $voucher->id,
                            'main_code'  => $item['main_code'],
                            'sub_code'   => $item['sub_code'],
                            'item_code'  => $item['item_code'],
                            'ledger_code'=> $item['ledger_code'] ?? null, // 允許 null
                            'dc'         => $item['dc'],
                            'amount'     => $item['amount'],
                            'note'       => $item['note'] ?? null,
                        ]);
                    }

                    DB::commit();

                    return response()->json([
                        'status' => 'ok',
                        'message' => '已儲存為常用分錄',
                    ]);
                
                default:
                    throw new \Exception('未知操作');
            }
            

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['system' => '系統錯誤：'.$e->getMessage()]);
        }

    }

    //刪除
    public function voucherDelete($id){
        DB::beginTransaction();

        try{
            $voucher = Account_voucher::findOrFail($id);

            $voucherDate = Carbon::parse($voucher->voucher_date);
            $year = $voucherDate->year;
            $month = $voucherDate->month;

            // 年結
            if (Account_closing_period::isClosed($year, null)) {
                return redirect()
                    ->route('accountant.voucher')
                    ->with('error', '此會計年度已年結，禁止刪除傳票');
            }

            // 月結
            if (Account_closing_period::isClosed($year, $month)) {
                return redirect()
                    ->route('accountant.voucher')
                    ->with('error', "會計期間 {$year} 年 {$month} 月已月結，禁止刪除傳票");
            }
            
            $voucher->delete();

            DB::commit();

            return redirect()
                ->route('accountant.voucher')
                ->with('success', '傳票已刪除');

        }catch(\Throwable $e){
            DB::rollBack();
            return redirect()
                ->route('accountant.voucher')
                ->with('error', '刪除失敗'.$e->getMessage());
        }
        
    }
};
