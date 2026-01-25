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

}
