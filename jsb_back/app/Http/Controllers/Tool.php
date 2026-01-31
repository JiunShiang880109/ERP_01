<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ingredients_db;
use App\Models\Account_voucher;
class Tool extends Controller
{
    //進貨更新成本(加權平均成本法)
    public function updateIngredientCost($ingredientId, $qty, $packPrice)
    {
        $ingredient = Ingredients_db::find($ingredientId);

        //舊有庫存與成本
        $oldStock = $ingredient->stockAmount;
        $oldCost  = $ingredient->costPerUnit ?? 0;

        $unit = strtolower($ingredient->unit);
        preg_match('/([\d\.]+)/', $unit, $num);//數字
        preg_match('/[a-zA-Z]+/', $unit, $u);//單位

        $unitVal = isset($num[1]) ? (float)$num[1] : 1;
        $unitType = isset($u[0]) ? strtolower($u[0]) : 'count';

        switch($unitType){
            case 'kg':  $baseWeight = $unitVal * 1000; break;
            case 'g':   $baseWeight = $unitVal;        break;
            case 'l':   $baseWeight = $unitVal * 1000; break;
            case 'ml':  $baseWeight = $unitVal;        break;
            default:    $baseWeight = $unitVal;        break;    // 顆、份等直接以數量為基底
        }

        //基底單位成本
        if($baseWeight <= 0) $baseWeight = 1;//避免除0
        $unitCost = $packPrice / $baseWeight;

        //新加權平均成本
        $newCost = (($oldStock * $oldCost) + ($qty * $unitCost)) / ($oldStock + $qty);

        
        $ingredient->stockAmount = $oldStock + $qty;
        $ingredient->costPerUnit = round($newCost, 2);
        $ingredient->save();
    }

    //售出扣庫存
    public function reduceIngredientStock($productId, $qty)
    {
        //取得商品配方
        $recipes = DB::table('product_recipe')
            ->where('productId', $productId)
            ->get();

        foreach($recipes as $recipe){
            $ingredient = DB::table('ingredients')->where('id',$recipe->ingredientId)->first();

            if(!$ingredient) continue;


            $unit = strtolower($ingredient->unit);
            preg_match('/([\d\.]+)/', $unit, $num);//數字
            preg_match('/[a-zA-Z]+/', $unit, $u);//單位

            $unitVal = isset($num[1]) ? (float)$num[1] : 1;
            $unitType = isset($u[0]) ? strtolower($u[0]) : 'count';

            //計算用量
            $usage = $recipe->usageQty * $qty;

            //單位換算
            switch($unitType){
                case 'kg':   $usedPack = $usage / ($unitVal * 1000); break;   // kg轉g
                case 'g':    $usedPack = $usage /  $unitVal;         break;

                case 'l':    $usedPack = $usage / ($unitVal * 1000); break;   // L轉ml
                case 'ml':   $usedPack = $usage /  $unitVal;         break;

                default:     // 顆/片/份（配方直接扣包數但須注意設定）
                            $usedPack = $usage / $unitVal;
            }
            
            //避免庫存負值
            $remain = max($ingredient->stockAmount - $usedPack, 0);

            DB::table('ingredients')
                ->where('id',$recipe->ingredientId)
                ->update(['stockAmount' => $remain]);
        }
    }

    public function orderCostAndInventory($orderNum, $order_detail){
        //計算訂單成本
        $usedCost = DB::table('order_detail')
            ->join('products', 'products.productId', '=', 'order_detail.productId')
            ->join('product_recipe', 'product_recipe.productId', '=', 'products.id')
            ->join('ingredients', 'ingredients.id', '=', 'product_recipe.ingredientId')
            ->where('order_detail.orderNum', $orderNum)
            ->selectRaw('SUM(product_recipe.usageQty * ingredients.costPerUnit * order_detail.quantity) as cost')
            ->value('cost') ?? 0;

        // 寫回訂單
        DB::table('orders')->where('orderNum', $orderNum)->update([
            'cost' => $usedCost
        ]);

        //售出扣庫存
        foreach($order_detail as $od){
            $this->reduceIngredientStock($od['productId'], $od['quantity']);
        }
    }
    //傳票編號
    public function generateVoucherCode(string $date, int $type):string
    {
        
        $prefixMap = [
            0 => 'CR', // 現金收入
            1 => 'CP', // 現金支出
            2 => 'TR', // 轉帳
        ];

        $prefix = $prefixMap[(int)$type] ?? 'UN';

        $date = Carbon::parse($date);
        $dateStr = $date->format('Ymd');

        //同一天、同類別各自流水號
        $count = Account_voucher::whereDate('voucher_date', now())
            ->where('voucher_type', (int)$type)
            ->count() + 1;

        return sprintf('%s%s-%03d', $prefix, $dateStr, $count);
    }
    //會計科目轉換
    public function toSubjectSortKey(?string $key): ?string{
        if(!$key) return null;

        [$m, $s, $i, $l] = array_pad(explode('-', $key), 4, 0);

         return sprintf('%02d%02d%02d%03d', (int)$m, (int)$s, (int)$i, (int)$l);
    }
    //分類帳科目查詢條件選項
    public function getSubjects(){
        //科目選單
        $subjects = DB::table('account_item as ai')
            ->leftJoin('account_ledger as al', function($join){
                $join->on('ai.main_code', '=', 'al.main_code')
                    ->on('ai.sub_code', '=', 'al.sub_code')
                    ->on('ai.code', '=', 'al.item_code');
            })
            ->select(
                'ai.main_code',
                'ai.sub_code',
                'ai.code as item_code',
                'ai.name as item_name',
                'al.code as ledger_code',
                'al.name as ledger_name'
            )
            ->orderBy('ai.main_code')
            ->orderBy('ai.sub_code')
            ->orderBy('ai.code')
            ->orderBy('al.code')
            ->get();
        
        return $subjects;
    }
    //傳票轉換用字典
    public const VOUCHER_TYPE_MAP = [
        0 => '現金收入',
        1 => '現金支出',
        2 => '轉帳',
    ];

    public const VOUCHER_KIND_MAP = [
        0 => '一般',
        1 => '調整',
    ];
    //科目名稱
    public function getSubjectNameMap(){
        
        return DB::table('account_item as ai')
            ->leftJoin('account_ledger as al', function ($join) {
                $join->on('ai.main_code', '=', 'al.main_code')
                    ->on('ai.sub_code', '=', 'al.sub_code')
                    ->on('ai.code', '=', 'al.item_code');
            })
            ->selectRaw("
                CONCAT(
                    ai.main_code,
                    ai.sub_code,
                    LPAD(ai.code, 2, '0'),
                    CASE
                        WHEN al.code IS NOT NULL THEN CONCAT('-', CAST(al.code AS UNSIGNED))
                        ELSE ''
                    END
                ) as subject_code
            ")
            ->selectRaw("
                CASE
                    WHEN al.code IS NOT NULL THEN CONCAT(ai.name, '-', al.name)
                    ELSE ai.name
                END as subject_name
            ")
            ->pluck('subject_name', 'subject_code');
    }
    public function subjectSortExpr(?string $alias = null){
        $p = $alias ? ($alias . '.') : '';

        return DB::raw("
            CONCAT(
                LPAD({$p}main_code, 2, '0'),
                LPAD({$p}sub_code, 2, '0'),
                LPAD({$p}item_code, 2, '0'),
                LPAD(COALESCE({$p}ledger_code, 0), 3, '0')
            )
        ");
    }
}
