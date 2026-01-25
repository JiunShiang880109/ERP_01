<?php

namespace App\Models;

use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
class Supplier_db extends Model{

    use SoftDeletes;

    protected $table = 'employee';


    function __construct(){


    }
    public function supplier_db(){
        return DB::table('supplier')
        ->where('disabled',1)
        ->orderby('disabled','desc')
        ->get();      
    }
    public function detail_db($supplierId){
        return DB::table('supplier')
        ->where('supplierId',$supplierId)
        ->get();      
    }
    public function supplier_quote_db($supplierId){
        return DB::select("SELECT * FROM supplier_quote
                           LEFT JOIN product ON product.productId=supplier_quote.productId 
                           LEFT JOIN supplier ON supplier.supplierId=supplier_quote.supplierId  
                           WHERE supplier_quote.supplierId = '$supplierId'
                           ORDER BY quoteDate");    
    }

    /**************************************廠商自訂折扣*********************************** */
    //撈廠商自訂折扣
    public function supplier_discount_db($supplierId)
    {
        return DB::select("SELECT * FROM supplier_discount WHERE supplierId = '$supplierId'");
    }

    //廠商新增自訂折扣
    public function discount_updateOrInsert_db($id,$data)
    {
        DB::table('supplier_discount')
            ->updateOrInsert(
                [
                    'id' => $id,
                    'supplierId' => $data['supplierId']
                ], $data);
    }

    //廠商刪除自訂折扣
    public function discount_delete_db($discountId,$supplierId)
    {
        DB::table("supplier_discount")->where('id',$discountId)->where('supplierId',$supplierId)->delete();
    }


    

}
