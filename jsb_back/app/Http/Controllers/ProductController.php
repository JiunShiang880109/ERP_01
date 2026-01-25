<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\Models\Product_db;
use DB;
use App\Traits\HelperTraits;
use App\Http\Controllers\LayoutController;
use App\Models\Product;
use App\Exports\ProductExport;
use App\Imports\ProductImport;
use App\Models\Product_recipe;
use App\Models\Ingredients_db;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Product_db
{
    use HelperTraits;
    
    private $PDO;
    use  HelperTraits;
     public function __construct(){
        
    //     $this->DBONE = $Product_db;
        $this->PDO = DB::connection()->getPdo();
     }
     //取得指定店家所有商品
    public function product(Request $req)
    {
        $storeId = $req->storeId;
        if (empty($storeId)) {
            return ['success' => false, 'msg' => '參數錯誤'];
        }
        $product = DB::select("SELECT * from products
                                 WHERE storeId = $storeId
                                 AND enable = 1");
                                //  return $product;
        return ['success' => true, 'product' => $product];
    }
    function index(Request $Request)
    {      
        $storeId = session()->get('storeId');
        // $data['product'] = $this->product_db($storeId);

        // foreach ($data['product'] as $value){
        //     $productId = $value->productId;
        //     $option = $this->product_category_db($storeId,$productId);
        //     $value->pd_taste = $option;

        // }
        $data['product'] = Product::with([
            'recipe.ingredient',
            'pd_taste'
        ])->where('storeId', $storeId)
        ->whereNull('deleted_at')
        ->orderBy('enable', 'DESC')
        ->get();

        //print_r($data);

        return view('product/index',$data);
    }
    #新增商品
    function add_product(Request $Request){
        $storeId = session()->get('storeId');
        #自動編號
        $productIdNum = rand(1,9).date('mdHis').rand(0,9);
        ###############
        $data['productId'] = $productIdNum;
        $data['cate'] = $this->cate_main_db($storeId);
        $data['taste'] = $this->custom_cate_db($storeId);
        $data['ingredients'] = Ingredients_db::where('storeId',$storeId)->where('enable',1)->get();
        return view('product/add_product',$data);
    }
    function cate2_ajax(Request $Request){
        $cate1Id = $Request->cate1Id;
        $data['cate2'] = $this->cate_mid_db($cate1Id);
       
    
 
        echo json_encode($data);
     }
    function product_create(Request $req){
        $productId = $req->productId;

        if(DB::table('products')->where('productId',$productId)->exists()){
            return back()->with('error','⚠ 商品編號已存在，請重新輸入');
        }

        $inputfile = $req->inputfile;
        $categoryId = $req->categoryId;
        if($categoryId=='-1'){
            $this->talk('必需輸入類別',url()->previous(), 1); 
            exit();
        }
        $fileName = $this->random_cid();
        $storeId = session()->get('storeId');
        $inputfile_pic=$this->NewUploadImg('inputfile',$fileName,'products');   
        if(!empty($inputfile_pic)){
            $insert['imageUrl'] = $fileName.'.jpg';
        }
        $insert['storeId'] = $storeId;
        $insert['categoryId'] = $req->categoryId;
        $insert['price'] = $req->price;
        
        $insert['productId'] = $productId;
        $insert['product_title'] = $req->product_title;
        // $insert['unit'] = $req->unit;
        $insert['enable'] = 1;
        $insert['taxType'] = $req->taxType;
        $insert['enable'] = $req->enable;
        $insert['feedback_point'] = $req->feedback_point;
        //DB::table('products')->insert($insert);
        $productDbId = DB::table('products')->insertGetId($insert);// 取得商品(products.id)
        
        //原物料/配方
        if($req->ingredientId){
            foreach($req->ingredientId as $ig=>$ingId){
               Product_recipe::create([
                    'productId' => $productDbId,
                    'ingredientId' => $ingId,
                    'usageQty' => $req->usageQty[$ig],
                    'unit' => $req->unit[$ig],
                    'created_at' => now(),
                    'updated_at' => now(),
               ]);
            }
        }
        //客製化
        $customCateId = $req->customCateId;
        if(!empty($customCateId)){
            foreach($customCateId as $value){
                $insert2['productId'] = $productId;
                $insert2['customCateId'] = $value;
                DB::table('products_with_custom')->insert($insert2);
            }
        }
        
        $this->talk('新增成功', route('Products'), 3);    
        exit();
    }
    #刪除商品
    function product_del(Request $Request){
        $productId = $Request->productId;
        $update['enable'] = 0;
        $update['categoryId'] = null;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        $this->product_update_db($update,$productId);   
        
        //配方
        $productDbId = DB::table('products')->where('productId', $productId)->value('id');

        Product_recipe::where('productId', $productDbId)->delete();
        
        $this->talk('',url()->previous(), 2); 
    }
    /*************商品明細**************/ 
    function product_update(Request $Request){
       $storeId = session()->get('storeId');
       $productId = $Request->productId;
       
         //如果有上傳新圖片
         if($_FILES['inputfile']["size"]!=0){
            $inputfile = $Request->inputfile;
            $fileName = $this->random_cid();
            $inputfile_pic=$this->NewUploadImg('inputfile',$fileName,'products');  
			$update['imageUrl'] = $fileName.'.jpg';
			//把舊照片刪掉
			$imageUrl = $Request->imageUrl;
            if(file_exists("assets/images/products/".$imageUrl) && $imageUrl != null){
                unlink("assets/images/products/".$imageUrl);//將檔案刪除
            }
		}
       $update['productId'] = $Request->productId;
       $update['product_title'] = $Request->product_title;
       $update['categoryId'] = $Request->categoryId;
       //$update['unit'] = $Request->unit;
       $update['price'] = $Request->price;
       $update['taxType'] = $Request->taxType;
       $update['enable'] = $Request->enable;
        $update['feedback_point'] = $Request->feedback_point;
       $this->product_update_db($update,$productId);
       $customCateId = $Request->customCateId;
       if(!empty($customCateId)){
        //先刪除後新增
        DB::table('products_with_custom')->where('productId',$productId)->delete();
        //
            foreach($customCateId as $value){
                $insert2['productId'] = $productId;
                $insert2['customCateId'] = $value;
                DB::table('products_with_custom')->insert($insert2);
            }
       }else{
        DB::table('products_with_custom')->where('productId',$productId)->delete();
       }   

       //配方
       $productDbId = DB::table('products')->where('productId', $productId)->value('id');

       Product_recipe::where('productId', $productDbId)->delete();

       if($Request->ingredientId){
            foreach($Request->ingredientId as $ig=>$ingId){
                Product_recipe::create([
                    'productId' => $productDbId,
                    'ingredientId' => $ingId,
                    'usageQty' => $Request->usageQty[$ig],
                    'unit' => $Request->unit[$ig],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
       }

        $this->talk('資料以更新',url()->previous(), 2); 
    }
    function product_detail(Request $Request){
        $storeId = session()->get('storeId');
        $productId = $Request->productId;
        //主檔商品
        $data['pd'] = $this->product_rowdb($storeId,$productId);
        $data['cate'] = $this->cate_main_db($storeId);
        $data['pd_taste'] = $this->product_category_db($storeId,$productId);

        $cateId = $data['pd'][0]->categoryId;
        $data['taste'] = $this->custom_category_db($storeId,$productId,$cateId);

        $productDbId = DB::table('products')->where('productId',$productId)->value('id');
        $data['recipe'] = Product_recipe::where('productId',$productDbId)->get();
        $data['ingredients'] = Ingredients_db::where('storeId',$storeId)->where('enable',1)->get();

        return view('product/product_detail',$data);
    }
    /************商品類別*************/
    function category(Request $req){
        $storeId = session()->get('storeId');
        $data['cate'] = $this->cate_db($storeId);
        return view('product/category',$data);
    }
    function category_insert(Request $req){
        $storeId = session()->get('storeId');
        $insert['category_title'] = $req->category_title;
        $insert['sort'] = $req->sort;
        $insert['storeId'] = $storeId;
        $insert['enable'] = 1;
        DB::table('category')->insert($insert);
        $this->talk('新增成功', route('category'), 3);     

    }
    function category_edit(Request $req){
        $storeId = session()->get('storeId');
        $cateId = $req->cateId;
        $data['cate'] = $this->cate_rowdb($storeId,$cateId);
        return view('product/category_edit',$data);
    }
    function category_update(Request $req){
        $storeId = session()->get('storeId');
        $id = $req->cateId;
        $update['category_title'] = $req->category_title;
        $update['sort'] = $req->sort;
        $update['enable'] = $req->enable;
       
        $this->category_updatedb($update,$id,$storeId);
        $this->talk('', route('category'), 2); 
    }
    function category_delete(Request $req){
        $storeId = session()->get('storeId');
        $id = $req->id;
        //先檢查存不存在
        $num = $this->pd_chk_cate_db($storeId,$id);
    
        if($num[0]->num>=1){
            $this->talk('類別底下還有商品',route('category'), 3); 
            exit();
        }else{
             $storeId = session()->get('storeId');
                DB::table('category')
                ->where('id',$id)
                ->where('storeId',$storeId)
                ->delete();
                $this->talk('',route('category'), 2); 
        }
       
    
    }
    /************商品規格*************/

    function spec(Request $req){
        $storeId = session()->get('storeId');
        //類別
        $data['cate'] = $this->cate_db($storeId);
        $data['spec'] = $this->custom_cate_db($storeId);
        //$data['spec'] = DB::table('custom_category')->where('storeId', $storeId)->get();
       foreach($data['spec'] as $key=>$value){
            $customCateId = $value->id;
            $data['spec'][$key]->opetion = $this->custom_option($storeId,$customCateId);
            //$this->custom_option($storeId,$customCateId);
        }
        return view('product/spec',$data);
    }
    function spec_insert(Request $req){
        $storeId = session()->get('storeId');
        $insert['cateId'] = $req->cateId;
        $insert['customCateTitle'] = $req->customCateTitle;
        $insert['require'] = $req->require;
        $insert['single'] = $req->single;
        $insert['storeId'] = $storeId;
        $insert['enable'] = 1;
        DB::table('custom_category')->insert($insert);
        $customCateId = DB::getPdo()->lastInsertId();
        $specification = $req->specification;
        $price = $req->price;        
        //取得有參數有幾個
        $count = count(array_filter($specification));
        for($i=0;$i<=$count-1;$i++){
            if($price[$i]==Null){$price[$i]=0;}
            $spec[] = [
                "custom_option_title" => $specification[$i],
                "price" => $price[$i],
                "enable" => 1,
                "sort" => $i+1,
                "storeId" => $storeId,
                "customCateId" => $customCateId,
            ];
        }
       foreach($spec as $insert2){
            DB::table('custom_option')->insert($insert2);
       }    
       $this->talk('新增成功', route('spec'), 3);   
         
    }
    function spec_delete(Request $req){
        $id = $req->id;
        $storeId = session()->get('storeId');
        DB::table('custom_category')
        ->where('id',$id)
        ->where('storeId',$storeId)
        ->delete();
        $this->talk('',url()->previous(), 2); 
    
    }
    function spec_edit(Request $req){
        $customCateId = $req->customCateId;
        $storeId = session()->get('storeId');
        $data['cate'] = $this->cate_main_db($storeId);
        $data['spec'] = $this->custom_category_edit_db($customCateId,$storeId);
        
        return view('product/spec_edit',$data);
    }
    function spec_update(Request $Request){
        $storeId = session()->get('storeId');
        $customCateId = $Request->customCateId;
        $update['cateId'] = $Request->cateId;
        $update['customCateTitle'] = $Request->customCateTitle;
        $update['require'] = $Request->require;
        $update['single'] = $Request->single;
        $this->custom_category_updatedb($update,$customCateId,$storeId);
        
        
        $this->talk('',url()->previous(), 2); 
    }
    function spec_option_update(Request $Request){
        $storeId = session()->get('storeId');
        $optionId = $Request->optionId;
        $update['custom_option_title'] = $Request->custom_option_title;
        $update['price'] = $Request->price;
        $update['sort'] = $Request->sort;
       $this->spec_option_updatedb($update,$optionId,$storeId);
       $this->talk('',url()->previous(), 2); 
    }
    function spec_option_del(Request $Request){
        $storeId = session()->get('storeId');
        $optionId = $Request->optionId;
       
        $storeId = session()->get('storeId');
                DB::table('custom_option')
                ->where('id',$optionId)
                ->where('storeId',$storeId)
                ->delete();

       $this->talk('',url()->previous(), 2); 
    }
    function spec_option_insert(Request $Request){
        $storeId = session()->get('storeId');
        $insert['custom_option_title'] = $Request->custom_option_title;
        $insert['enable'] = 1;
        $insert['storeId'] = $storeId;
        $insert['customCateId'] = $Request->customCateId;
        $insert['price'] = $Request->price;
        $insert['sort'] = $Request->sort;

        DB::table('custom_option')->insert($insert);
       

       $this->talk('',url()->previous(), 2); 
    }
    /************商品規格END*************/




    
    //商品搜尋
    function productSearch(Request $Request){
        $pdName = $Request->pdName;
        $data['search'] = $this->productSearch_db($pdName);   
        echo json_encode($data);
    }
    //商品搜尋
    function go_productSearch(Request $Request){
        $pdName = $Request->pdName;
        $data['search'] = $this->go_productSearch_db($pdName);   
        echo json_encode($data);
    }
   


    /**************** 匯出入商品  *****************/
    public function importCSV(Request $req)
    {
    //    return date('Ym-d H:i:s', strtotime("2022-01-07 08:38:40"));

        // return new ProductImport($req->file('file'));
        Excel::import(new ProductImport, $req->file('file') );
        return redirect()->route('Products');
    }
    
    public function exportCSV(){
        return Excel::download(new ProductExport, 'product.csv');
    }

    public function exportXLSX()
    {
        return Excel::download(new ProductExport, 'product.xlsx');
    }

}
