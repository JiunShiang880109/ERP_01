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

use App\Exceptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\FuncCall;

class VoucherTempCtrl extends Controller
{
    public function voucherTempIndex() {
        $vouchers_temp = Voucher_temp::with(['items'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($vouchers_temp as $voucher){
            $voucher->debit = $voucher->items->where('dc', 'D')->sum('amount');
            $voucher->credit = $voucher->items->where('dc', 'C')->sum('amount');
        }
            
        return view('accountant.voucher_temp', compact('vouchers_temp'));
    }

    public function voucherTempDetail($id){
        $voucher_temp = Voucher_temp::with([
            'items.mainCate'
        ])->findOrFail($id);

        $items_temp = Voucher_temp_item::query()
            ->where('voucher_temp_id', $id)
            ->leftJoin('account_item as i', function ($join){
                $join->on('i.main_code', '=', 'voucher_temp_item.main_code')
                    ->on('i.sub_code', '=', 'voucher_temp_item.sub_code')
                    ->on('i.code', '=', 'voucher_temp_item.item_code');
            })
            ->leftJoin('account_ledger as l', function ($join) {
                $join->on('l.main_code', '=', 'voucher_temp_item.main_code')
                    ->on('l.sub_code', '=', 'voucher_temp_item.sub_code')
                    ->on('l.item_code', '=', 'voucher_temp_item.item_code')
                    ->on('l.code', '=', 'voucher_temp_item.ledger_code');
            })
            ->select(
                'voucher_temp_item.*',
                'i.name as item_name',
                'l.name as ledger_name'
            )
            ->get();

        return view('accountant.voucher_temp_detail', compact('voucher_temp', 'items_temp'));
    }
    //新增
    public function voucherTempAdd(){
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

        return view('accountant.add_voucher_temp', compact('items'));
    }
    //編輯
    public function voucherTempEdit($id){
        //主檔
        $voucher_temp = Voucher_temp::with(['items'])
            ->findOrFail($id);

        //檢查是否為停用科目之交易資料
        $disabledLedger = Voucher_temp_item::query()
            ->join('account_ledger as l', function($join){
                $join->on('l.main_code', '=', 'voucher_temp_item.main_code')
                    ->on('l.sub_code', '=', 'voucher_temp_item.sub_code')
                    ->on('l.item_code', '=', 'voucher_temp_item.item_code')
                    ->on('l.code', '=', 'voucher_temp_item.ledger_code');
            })->where('voucher_temp_item.voucher_temp_id', $voucher_temp->id)
            ->where('l.enable', 0)
            ->exists();
        
        if($disabledLedger){
            return redirect()
                ->route('accountant.voucher_temp_detail', $voucher_temp->id)
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

        return view('accountant.edit_voucher_temp', compact('voucher_temp', 'items'));
    }

    public function voucherTempUpdate(Request $request, $id){
        DB::beginTransaction();

        try {
            //基本驗證
            $rules=[
                'action' => 'required|in:update_temp',
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

                if (!empty($item['ledger_code'])) {
                    $exists = Account_ledger::where([
                        'main_code' => $item['main_code'],
                        'sub_code'  => $item['sub_code'],
                        'item_code' => $item['item_code'],
                        'code'      => $item['ledger_code'],
                        'enable'    => 1,
                    ])->exists();

                    if (!$exists) {
                        throw new \Exception('子科目與主科目不相符');
                    }
                }
            }

            switch($request->voucher_type){
                case '0'://收入
                    if($credit > 0){
                        return back()->withInput()
                        ->withErrors(['balance' => '現金收入不可用貸方']);
                    }
                    break;
                case '1'://支出
                    if($debit > 0){
                        return back()->withInput()
                        ->withErrors(['balance' => '現金支出不可用借方']);
                    }
                    break;
                case '2'://轉帳
                    if($debit !== $credit){
                        return back()->withInput()
                            ->withErrors(['balance' => '轉帳借貸金額不平衡']);
                    }
                    break;
            }

            //action 分流
            switch($request->action){
                case 'update_temp':
                    
                    //建立模板主檔
                    $voucher = Voucher_temp::with(['items'])->findOrFail($id);
                    $voucher -> update([
                        'voucher_date' => $request->voucher_date,
                        'voucher_type' => $request->voucher_type,
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
                        Voucher_temp_item::whereIn('id', $deleteIds)->delete();
                    }

                    //更新分錄明細
                    foreach ($request->items as $item) {
                        $payload = [
                            'voucher_temp_id' => $voucher->id,
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
                            Voucher_temp_item::where('id', $item['id'])
                                ->where('voucher_temp_id', $voucher->id)
                                ->update($payload);
                        }else{
                            Voucher_temp_item::create($payload);
                        }
                    }

                    DB::commit();

                    return response()->json([
                        'status' => 'ok',
                        'message' => '已儲存常用分錄',
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
    public function voucherTempDelete($id){
        DB::beginTransaction();

        try{
            $voucher = Voucher_temp::findOrFail($id);
            $voucher->delete();

            DB::commit();

            return redirect()
                ->route('accountant.voucher_temp')
                ->with('success', '常用傳票已刪除');

        }catch(\Throwable $e){
            DB::rollBack();
            return redirect()
                ->route('accountant.voucher_temp')
                ->with('error', '刪除失敗'.$e->getMessage());
        }
        
    }
};
