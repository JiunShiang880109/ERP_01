<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Models\Account_cate_main;
use App\Models\Account_cate_sub;
use App\Models\Account_item;
use App\Models\Account_ledger;
use App\Models\Account_voucher_item;
use App\Models\Account_opening_balance;

class AccountantController extends Controller
{
    public function categoryIndex(){

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
                "{$r->main_code}-{$r->sub_code}-{$r->item_code}-{$r->ledger_code}"
            );

        //dd($fiscalYear, $openingBalance->keys()->take(10), $openingBalance->count());

        return view('accountant.category', compact('categories','openingBalance'));
    }

    //新增
    public function category_add(Request $request){

        $request->validate([
            'main_code' => 'required|exists:account_category_main,code',
            'sub_code' => 'required',
            'item_code' => 'required',
            'code' => 'required|digits_between:1,4',
            'name' => 'required|string|max:50',
            'enable' => 'required|in:0,1',
        ],[
            'main_code.required' => '請選擇主類別',
            'sub_code.required' => '請選擇子類別',
            'item_code.required' => '請選擇主科目',
            'code.required' => '請輸入子科目代碼',
            'code.digits_between' => '子科目編號需 1-4 位數',
            'name.required' => '請輸入類別名稱',
            'enable.required' => '請選擇是否啟用',
        ]);

        //拆 sub_code
        if (str_contains($request->sub_code, '-')) {
            $subParts = explode('-', $request->sub_code);
            $subCode = array_pop($subParts); // 取最後一段
        } else {
            $subCode = $request->sub_code;
        }

        //拆 item_code
        if (str_contains($request->item_code, '-')) {
            $itemParts = explode('-', $request->item_code);
            $itemCode = array_pop($itemParts);
        } else {
            $itemCode = $request->item_code;
        }

        // 轉成 DB 需要的值
        $request->merge([
            'sub_code'  => $subCode,
            'item_code' => $itemCode,
        ]);

        $exists = Account_item::where('main_code', $request->main_code)
            ->where('sub_code', $request->sub_code)
            ->where('code', $request->item_code)
            ->exists();

        if (!$exists) {
            return back()->with('error', '主類別 / 子類別 / 主科目組合錯誤');
        }

        $existsLedgerCode = Account_ledger::where([
            'main_code' => $request->main_code,
            'sub_code'  => $request->sub_code,
            'item_code' => $request->item_code,
            'code'      => $request->code,
        ])->whereNull('deleted_at')->exists();
        
        if ($existsLedgerCode) {
            return back()->with('error', '子科目編號已存在');
        }

        Account_ledger::create([
            'main_code' => $request->main_code,
            'sub_code' => $request->sub_code,
            'item_code' => $request->item_code,
            'code' => $request->code,
            'employeeId' => $request->employeeId,
            'name' => $request->name,
            'enable' => $request->enable,
            
        ]);

        return redirect()->route('accountant.category')
            ->with('success', '新增成功');
    }

    //修改
    public function category_update(Request $request){
        $request->validate([
            'main_code' => 'required',
            'sub_code'  => 'required',
            'item_code' => 'required',
            'code'      => 'required',
            'name'      => 'required',
            'enable'    => 'required',
        ]);

        //sub_code
        if (str_contains($request->sub_code, '-')) {
            $subParts = explode('-', $request->sub_code);
            $subCode = array_pop($subParts); // 取最後一段
        } else {
            $subCode = $request->sub_code;
        }

        //item_code
        if (str_contains($request->item_code, '-')) {
            $itemParts = explode('-', $request->item_code);
            $itemCode = array_pop($itemParts);
        } else {
            $itemCode = $request->item_code;
        }

        // 轉成 DB 需要的值
        $request->merge([
            'sub_code'  => $subCode,
            'item_code' => $itemCode,
        ]);

        
        $exists = Account_item::where('main_code', $request->main_code)
            ->where('sub_code', $request->sub_code)
            ->where('code', $request->item_code)
            ->exists();

        if (!$exists) {
            return back()->with('error', '主類別 / 子類別 / 主科目組合錯誤');
        }

        $affected=Account_ledger::where([
            'main_code' => $request->main_code,
            'sub_code'  => $request->sub_code,
            'item_code' => $request->item_code,
            'code'      => $request->code,
        ])->whereNull('deleted_at')->update([
            'name'     => $request->name,
            'enable'   => $request->enable,
        ]);

        if ($affected === 0) {
            return back()->with('error', '找不到對應的子科目，未更新');
        }

        return redirect()->route('accountant.category')
            ->with('success', '更新成功');    

    }
    
    //刪除
    public function category_delete(Request $request){
        
        $request->validate([
            'main_code' => 'required',
            'sub_code'  => 'required',
            'item_code' => 'required',
            'code'      => 'required',
        ]);

        $ledger = Account_ledger::where([
            'main_code' => $request->main_code,
            'sub_code'  => $request->sub_code,
            'item_code' => $request->item_code,
            'code'      => $request->code,
        ])->first();
        
        if(!$ledger){
            return back()->with('error', '找不到該子科目');
        }

        //檢查是否已有傳票
        $used = Account_voucher_item::where('main_code', $ledger->main_code)
            ->where('sub_code', $ledger->sub_code)
            ->where('item_code', $ledger->item_code)
            ->where(function ($q) use ($ledger) {
                $q->where('ledger_code', $ledger->code)
                ->orWhereNull('ledger_code'); // 主科目也算使用
            })->exists();
        
        if($used){
            return back()->with('error', '此科目已有交易紀錄，僅能停用，無法刪除');
        }

        Account_ledger::where([
            'main_code' => $ledger->main_code,
            'sub_code'  => $ledger->sub_code,
            'item_code' => $ledger->item_code,
            'code'      => $ledger->code,
        ])->delete();

        return redirect()->route('accountant.category')
            ->with('success', '類別已刪除');
    
    }

    //科目停用
    public function category_disable(Request $request)
    {
        Account_ledger::where([
            'main_code' => $request->main_code,
            'sub_code'  => $request->sub_code,
            'item_code' => $request->item_code,
            'code'      => $request->code,
        ])->update(['enable' => 0]);

        return back()->with('success', '子科目已停用');
    }

}
