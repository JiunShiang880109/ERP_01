<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\Models\Product_db;
use DB;
use App\Traits\HelperTraits;
use App\Http\Controllers\LayoutController;

class ProductApiController extends Product_db
{
    use HelperTraits;

    private $PDO;
    use  HelperTraits;
     public function __construct(){
    //     $this->DBONE = $Product_db;
        $this->PDO = DB::connection()->getPdo();
     }

    //取得指定店家所有商品，客製化改在這裡一起撈
    public function product(Request $req)
    {
        $storeId = $req->storeId;
        if (empty($storeId)) {
            return ['success' => false, 'msg' => '參數錯誤'];
        }
        $products = DB::select("SELECT * from products
                                WHERE storeId = '$storeId'
                                AND enable = 1");
                                //  return $product;
        foreach($products as $product){

            $productId = $product->productId;
            // return $productId;
            $categories = DB::select("SELECT * from products_with_custom AS A
                                      LEFT JOIN custom_category AS B
								      ON A.customCateId = B.id
                                      WHERE A.productId = '$productId'
                                ");
            $custom_category = [];

            foreach($categories as $category){
                //客製化主分類id
                $id= $category->id;
                $options = DB::select("SELECT * from custom_option
                                    WHERE customCateId = '$id' AND enable = 1
                                    ORDER BY sort ASC
                                ");
                $category->options = $options;
                $custom_category[] = $category;
            }
            $product->custom_category = $custom_category;
        }

        return ['success' => true, 'product' => $products];

    }

    //取得指定店家的指定商品
    public function GetProduct($productId){
        $products = DB::select("SELECT * from products
                                WHERE productId = $productId
                                AND enable = 1");
        foreach ($products as $product) {

            $productId = $product->productId;
            // return $productId;
            $categories = DB::select("SELECT * from products_with_custom AS A
                                            LEFT JOIN custom_category AS B
                                            ON A.customCateId = B.id
                                            WHERE A.productId = '$productId'
                                        ");
            $custom_category = [];

            foreach ($categories as $category) {
                //客製化主分類id
                $id = $category->id;
                $options = DB::select("SELECT * from custom_option
                                            WHERE customCateId = '$id'
                                            AND enable = 1
                                            ORDER BY sort ASC
                                        ");
                $category->options = $options;
                $custom_category[] = $category;
            }
            $product->custom_category = $custom_category;
        }

        return ['success' => true, 'product' => $products[0]];


    }

    //檢查購物車商品是否售完
    public function checkcart(Request $req){
        // return $req;
        $orders = $req->orders;
        // return $orders;
        // var_dump($orders);
        foreach($orders as $key => $order){
            $storeId = $order['storeId'];
            $productId = $order['productId'];

            $existProduct = DB::select("SELECT * from products
                        WHERE storeId = '$storeId'
                        AND productId = $productId
                        AND deleted_at IS NULL");
            //沒有搜尋到商品
            if(count($existProduct) == 0){
                $orders[$key]['soldout'] = true;
            }
            else{
                //有搜尋到商品，繼續搜尋客製化選項是否開放
                // return 1;
                $options = $order['options'];
                foreach($options as $option){
                    $customCateId = $option['customCateId'];
                    $id=$option['id'];
                    $existOption = DB::select("SELECT * from custom_option
                        WHERE storeId = '$storeId'
                        AND customCateId = $customCateId
                        AND id = $id
                        AND enable = 1");

                    //沒有搜尋到客製化選項
                    if(count($existOption) == 0){
                        $orders[$key]['soldout'] = true;
                        break;
                    }
                }
            }
        }
        return ['success'=>true,'orders'=> $orders];
    }


    function index(Request $Request)
    {
        $cate1Id = $Request->cate1Id;
        $cate2Id = $Request->cate2Id;
        $cate3Id = $Request->cate3Id;
        $data['cate1'] = $this->cate_main_db();
        if(isset($cate2Id)){
            $data['cate2'] = $this->cate_mid_db($cate1Id);
        }
        if(isset($cate3Id)){
            $data['cate3'] = $this->cate_kid_db($cate1Id);
        }
        $data['cate1Id'] = $cate1Id;
        $data['cate2Id'] = $cate2Id;
        $data['cate3Id'] = $cate3Id;
        //商品顯示
        $data['product'] = $this->product_db($cate1Id,$cate2Id,$cate3Id);
        foreach($data['product'] as $key=>$value){
            $productId = $value->productId;
            $productDetail = $this->productDetail_db($productId);
            $data['product'][$key]->detail= $productDetail;
        }

        /********************取得layout資訊************************ */
        // $latout_employeeId = session()->get('employeeId');
        // $Layout = new LayoutController;
        // $data['pages'] = $Layout->pages_db($latout_employeeId);
        /********************************************************* */

        return view('product/index',$data);
    }
    function cate2_ajax(Request $Request){
       $cate1Id = $Request->cate1Id;
       $data['cate2'] = $this->cate_mid_db($cate1Id);

       $data['cate3'] = $this->cate_kid_db($cate1Id);

       echo json_encode($data);
    }
    function cate3_ajax(Request $Request){
        $cate2Id = $Request->cate2Id;
        $cate3 = $this->cate_kid_db($cate2Id);

        echo json_encode($cate3);
     }
    #新增商品
    function add_product(Request $Request){
        $cate1Id = $Request->cate1Id;
        $cate2Id = $Request->cate2Id;
        $cate3Id = $Request->cate3Id;
        $data['cate1'] = $this->cate_main_db();
        if(isset($cate2Id)){
            $data['cate2'] = $this->cate_mid_db($cate1Id);
        }
        if(isset($cate3Id)){
            $data['cate3'] = $this->cate_kid_db($cate2Id);
        }
        $data['cate1Id'] = $cate1Id;
        $data['cate2Id'] = $cate2Id;
        $data['cate3Id'] = $cate3Id;
        #撈取主檔的號碼+1號
        $rs = $this->productId_rowdb();
        $productIdNum = $rs[0]->productId;
        $rs2 = $this->productDetailId_rowdb();
        $productDetailIdNum = $rs2[0]->productDetailId;

        $data['productId'] = $productIdNum+1;
        $data['productDetailId'] = $productDetailIdNum+1;

        /********************取得layout資訊************************ */
        $latout_employeeId = session()->get('employeeId');
        $Layout = new LayoutController;
        $data['pages'] = $Layout->pages_db($latout_employeeId);
        /********************************************************* */

        return view('product/add_product',$data);
    }
    function product_create(Request $Request){
        $verify = $Request->verify;
        $cateMainId = $Request->cateMainId;
        $cateMidId = $Request->cateMidId;
        if($cateMainId=='-1'){
            Tool::Talk('3','請選擇主類別',url()->previous());
            exit();
        }elseif($cateMidId=='-1'){
            Tool::Talk('3','請選擇次類別',url()->previous());
            exit();
        }

        // if($verify==1){
        //     $start_ean = $Request->start_ean;
        //     $ck_ean = $Request->ck_ean;
        //     if($start_ean=='' || $ck_ean==''){
        //         Tool::Talk('3','請填寫國際碼',url()->previous());
        //         exit();
        //     }
        // }



        $productId = $Request->productId;
        $insert['productId'] = $Request->productId;
        $insert['name'] = $Request->name;
        $insert['cateMainId'] = $cateMainId;
        $insert['cateMidId'] = $cateMidId;
        $insert['cateKidId'] = $Request->cateKidId;
        $insert['specification'] = $Request->Main_specification;

        $this->product_create_insertdb($insert);

        $start_ean = $Request->start_ean;
        $ck_ean = $Request->ck_ean;
         //認證
         if($verify==1){
            $start_ean = $Request->start_ean;
            $ck_ean = $Request->ck_ean;
            if($start_ean=='' || $ck_ean==''){
                Tool::Talk('3','請填寫國際碼',url()->previous());
                exit();
            }
            $ean = $start_ean.$ck_ean;
            $correct= $this->valid_EAN($ean);
            if($correct=='correct'){
                $insert2['ean'] = $ean;
            }

        }elseif($verify==2){
            $fresh_ean = $Request->fresh_ean;
            if($fresh_ean==''){
                Tool::Talk('3','請填寫國際碼',url()->previous());
                exit();
            }
            $insert2['ean'] = $fresh_ean;

        }else{
            //不認證
            // $start_ean = $Request->start_ean;
            // $ck_ean = $Request->ck_ean;
            // $ean = $start_ean.$ck_ean;
            //$insert['ean'] = $ean;
        }


        $insert2['productDetailName'] = $Request->name;
        //$insert2['ean'] = $start_ean.$ck_ean;
        $insert2['specification'] = $Request->specification;
        $insert2['productId'] = $productId;
        $insert2['productDetailId'] = $Request->productDetailId;
        $insert2['unit'] = $Request->unit;
        $insert2['pp'] = $Request->pp;
        $insert2['price'] = $Request->price;
        $insert2['taxType'] = $Request->taxType;
        $this->detail_insertdb($insert2);


        Tool::Talk('2','','product/product_detail/'.$productId);
    }
    #刪除商品
    function product_del(Request $Request){
        $productId = $Request->productId;
        $ck = $this->ckProduct_db($productId);
        if ($ck>0)
			{
                Tool::Talk('3','詳情中有商品不能被刪除。',url()->previous());
				exit();
			}
			else
			{
				$this->product_deldb($productId);
                Tool::Talk('2','',url()->previous());
			}

    }
    /*************商品明細**************/
    function product_update (Request $Request){
       $productId = $Request->productId;
       $update['cateMainId'] = $Request->cate1Id;
       $update['cateMidId'] = $Request->cate2Id;
       $update['cateKidId'] = $Request->cate3Id;
       $update['name'] = $Request->name;
       $update['enable'] = $Request->enable;
       $update['storeAmount'] = $Request->storeAmount;
       $update['safeAmount'] = $Request->safeAmount;
       $this->product_update_db($update,$productId);
       Tool::Talk('2','',url()->previous());

    }
    function product_detail(Request $Request){
        $productId = $Request->productId;
        #$cateMidId = $Request->cate2Id;
        $data['productId'] = $productId;
        #$data['cateMidId'] = $cateMidId;
        /* */
        //主檔商品
        $rs2 = $this->productDetailId_rowdb();
        $productDetailIdNum = $rs2[0]->productDetailId;

        $pd = $this->product_rowdb($productId);
        $cate1Id = $pd[0]->cateMainId;
        $cate2Id = $pd[0]->cateMidId;
        $cate3Id = $pd[0]->cateKidId;
        $data['cate1Id'] = $cate1Id;
        $data['cate2Id'] = $cate2Id;
        $data['cate3Id'] = $cate3Id;
        $data['pd'] = $pd;
        $data['cate1'] = $this->cate_main_db();
        $data['cate2'] = $this->cate_mid_db($cate1Id);
        $data['cate3'] = $this->cate_kid_db($cate1Id);
        /* */
        ##$data['productDetailId'] = Tool::random_id();
        $data['productDetailId'] = $productDetailIdNum+1;
        //國際碼推薦
        $ean_center = $this->ean_Limit_1_db($productId);
        if(!empty($ean_center)){
            #$ean_bottom = $ean_center[0]->ean;
            #$ean_top = $ean_center[0]->ean+1000;
            $data['ean_search'] = $this->ean_search_db($ean_center[0]->ean);
        }else{
            $data['ean_search'] = [];
        }


        //推薦
        $data['catepd'] = $this->productMid_db($cate2Id);
        //商品明細
        $data['detail'] = $this->products_db($productId);
        //最後一次進貨
        $data['purchase'] = $this->purchase_db($productId);
        //庫存量

        //檔期
        //echo $data['detail'][0]->productDetailId;
        $today=date('Y-m-d H:i:s');
        //$specialTypes = array("0" => "特價", "1" => "限價", "2" => "買送", "3" => "M量" );
        foreach($data['detail'] as $key=>$value){
            $productId = $value->productId;
            $productDetailId = $value->productDetailId;
            $ean = $value->ean;
            $pp = $value->pp;
            //要將同PP的商品寫在同組，將資料塞到最底層
            $group= $this->group_product_db($productId,$pp);
            foreach($group as $key2 =>$value2){
                $group_ean = $value2->ean;
                $group_productDetailId = $value2->productDetailId;
                $correct = $this->valid_EAN($group_ean);
                $value2->validEan = $correct;
                //這期檔期
                $promotion = $this->special_product_db($group_productDetailId,$today);
                //驗證
                if(!empty($promotion)){
                    $value2->specialName= $promotion[0]->specialName;
                    $value2->promotionPrice= ceil($promotion[0]->promotionPrice).'元';
                    $value2->specialEnable= $promotion[0]->specialEnable;
                    $value2->validEan= $correct;
                    //$data['detail'][$key]->specialType= $specialTypes[$promotion[0]->specialType];
                    $value2->starttime= date("Y-m-d",strtotime($promotion[0]->starttime));
                    $value2->endtime= date("Y-m-d",strtotime($promotion[0]->endtime));
                }else{
                    $value2->specialName= '';
                    $value2->promotionPrice= NULL;
                    $value2->specialEnable= NULL;
                    $value2->validEan= $correct;
                    //$data['detail'][$key]->specialType= NULL;
                    $value2->starttime= NULL;
                    $value2->endtime= NULL;
                }
                //下期檔期
                $net_promotion = $this->net_special_product_db($productDetailId,$today);
                if(!empty($net_promotion)){
                    $value2->net_specialName= $net_promotion[0]->specialName;
                    $value2->net_promotionPrice= ceil($net_promotion[0]->promotionPrice).'元';
                    $value2->net_specialEnable= $net_promotion[0]->specialEnable;
                    //$data['detail'][$key]->net_specialType= $specialTypes[$net_promotion[0]->specialType];
                    $value2->net_starttime= date("Y-m-d",strtotime($net_promotion[0]->starttime));
                    $value2->net_endtime= date("Y-m-d",strtotime($net_promotion[0]->endtime));
                }else{
                    $value2->net_specialName= '';
                    $value2->net_promotionPrice= NULL;
                    $value2->net_specialEnable= NULL;
                    //$data['detail'][$key]->net_specialType= NULL;
                    $value2->net_starttime= NULL;
                    $value2->net_endtime= NULL;
                }
            }
            $data['detail'][$key]->group=$group;


            //$data['detail'][$key]->group=66;
           // foreach($group as $key2 => $value2){
                //$data['detail'][$key]['group'][$key2]->validEan= '8877';
            //     $group_ean = $value2->ean;
              //   $correct = $this->valid_EAN($ean);
            //     $data['detail'][$key]['group']->validEan = 8877776;
            //}


           /*
            //這期檔期
            $promotion = $this->special_product_db($productDetailId,$today);
            //驗證
            if(!empty($promotion)){
                $data['detail'][$key]->specialName= $promotion[0]->specialName;
                $data['detail'][$key]->promotionPrice= ceil($promotion[0]->promotionPrice).'元';
                $data['detail'][$key]->specialEnable= $promotion[0]->specialEnable;
                $data['detail'][$key]->validEan= $correct;
                //$data['detail'][$key]->specialType= $specialTypes[$promotion[0]->specialType];
                $data['detail'][$key]->starttime= date("Y-m-d",strtotime($promotion[0]->starttime));
                $data['detail'][$key]->endtime= date("Y-m-d",strtotime($promotion[0]->endtime));
            }else{
                $data['detail'][$key]->specialName= '';
                $data['detail'][$key]->promotionPrice= NULL;
                $data['detail'][$key]->specialEnable= NULL;
                $data['detail'][$key]->validEan= $correct;
                //$data['detail'][$key]->specialType= NULL;
                $data['detail'][$key]->starttime= NULL;
                $data['detail'][$key]->endtime= NULL;
            }
            //下期檔期
            $net_promotion = $this->net_special_product_db($productDetailId,$today);
            if(!empty($net_promotion)){
                $data['detail'][$key]->net_specialName= $net_promotion[0]->specialName;
                $data['detail'][$key]->net_promotionPrice= ceil($net_promotion[0]->promotionPrice).'元';
                $data['detail'][$key]->net_specialEnable= $net_promotion[0]->specialEnable;
                //$data['detail'][$key]->net_specialType= $specialTypes[$net_promotion[0]->specialType];
                $data['detail'][$key]->net_starttime= date("Y-m-d",strtotime($net_promotion[0]->starttime));
                $data['detail'][$key]->net_endtime= date("Y-m-d",strtotime($net_promotion[0]->endtime));
            }else{
                $data['detail'][$key]->net_specialName= '';
                $data['detail'][$key]->net_promotionPrice= NULL;
                $data['detail'][$key]->net_specialEnable= NULL;
                //$data['detail'][$key]->net_specialType= NULL;
                $data['detail'][$key]->net_starttime= NULL;
                $data['detail'][$key]->net_endtime= NULL;
            }*/




        }
        //print_r($data);

        /********************取得layout資訊************************ */
        $latout_employeeId = session()->get('employeeId');
        $Layout = new LayoutController;
        $data['pages'] = $Layout->pages_db($latout_employeeId);
        /********************************************************* */

        return view('product/product_detail',$data);
    }
    function ckEan(Request $Request){
        $start_ean = $Request->start_ean;
        $ckean = $Request->ckean;
        $ean = $start_ean.$ckean;
        //判斷有無重複
        $ckrepeat = $this->ckrepeat_db($ean);
        if($ckrepeat>=1){//已經存在
            $array['correct']= 'exist';
        }else{
            //驗證國際碼
            $array['correct']= $this->valid_EAN($ean);
        }
        echo json_encode($array);
    }
    function inckEan(Request $Request){
        $start_ean = $Request->start_ean;
        $ckean = $Request->ckean;
        $ean = $start_ean.$ckean;
        //判斷有無重複
        $ckrepeat = $this->ckrepeat_db($ean);
        if($ckrepeat>=1){//已經存在
            $array['correct']= 'exist';
        }
        echo json_encode($array);
    }
    //明細寫入
    function detail_create(Request $Request){
        $verify = $Request->verify;//國際碼認證
        $productId = $Request->productId;

        //認證
        if($verify==1){
            $start_ean = $Request->start_ean;
            $ck_ean = $Request->ck_ean;
            if($start_ean=='' || $ck_ean==''){
                Tool::Talk('3','請填寫國際碼',url()->previous());
                exit();
            }
            $ean = $start_ean.$ck_ean;
            $correct= $this->valid_EAN($ean);
            if($correct=='correct'){
                $insert['ean'] = $ean;
            }

        }elseif($verify==2){
            $fresh_ean = $Request->fresh_ean;
            if($fresh_ean==''){
                Tool::Talk('3','請填寫國際碼',url()->previous());
                exit();
            }
            $insert['ean'] = $fresh_ean;

        }else{
            //不認證
            // $start_ean = $Request->start_ean;
            // $ck_ean = $Request->ck_ean;
            // $ean = $start_ean.$ck_ean;
            //$insert['ean'] = $ean;
        }
        //
        //$insert['ean'] = $ean;
        $insert['productId'] = $productId;
        $insert['productDetailId'] = $Request->productDetailId;
        $insert['productDetailName'] = $Request->productDetailName;
        $insert['specification'] = $Request->specification;
        $insert['unit'] = $Request->unit;
        $insert['pp'] = $Request->pp;
        $insert['price'] = $Request->price;
        $insert['taxType'] = $Request->taxType;
        $insert['maxUnitType'] = $Request->maxUnitType;
        $this->detail_insertdb($insert);
        Tool::Talk('2','',url()->previous());
    }
    //明細編輯
    function edit_product_unit(Request $Request){
        $productDetailId = $Request->productDetailId;
        $data['unit']=$this->unit_db($productDetailId);
        // Tool::Talk('2','',url()->previous());

        /********************取得layout資訊************************ */
        $latout_employeeId = session()->get('employeeId');
        $Layout = new LayoutController;
        $data['pages'] = $Layout->pages_db($latout_employeeId);
        /********************************************************* */

        return view('product/edit_product_unit',$data);
    }
    function detail_update(Request $Request){
        $productDetailId = $Request->productDetailId;
        $update['ean'] = $Request->ean;
        // $update['productId'] = $Request->productId;
        $update['productDetailId'] = $Request->productDetailId;
        $update['productDetailName'] = $Request->productDetailName;
        $update['specification'] = $Request->specification;
        $update['unit'] = $Request->unit;
        $update['pp'] = $Request->pp;
        $update['price'] = $Request->price;
        $update['taxType'] = $Request->taxType;
        $this->detail_updatedb($update,$productDetailId);
        Tool::Talk('2','',url()->previous());

    }
    //明細刪除
    function detail_del(Request $Request){
        $productDetailId = $Request->productDetailId;
        $this->detail_deldb($productDetailId);
        Tool::Talk('2','',url()->previous());

    }
    //商品搜尋
    function productSearch(Request $Request){
        $pdName = $Request->pdName;

        $data['search'] = $this->productSearch_db($pdName);
        echo json_encode($data);
        //Tool::Talk('2','','product/product_detail/'.$search[0]->productId);
    }
    //商品搜尋
    function go_productSearch(Request $Request){
        $pdName = $Request->pdName;
        $data['search'] = $this->go_productSearch_db($pdName);
        echo json_encode($data);
        //Tool::Talk('2','','product/product_detail/'.$search[0]->productId);
    }
    /************商品報價************/
    function quote (Request $Request){
        $data['productId'] = $Request->productId;
        $data['pd_max'] = $this->pd_quote_max_db($data['productId']);
        $data['pd_min'] = $this->pd_quote_min_db($data['productId']);

        $data['quote'] =$this->supplier_quote_db($data['productId']);
        //
        $data['supplier'] =$this->supplier_db();
        //$data['unit'] = DB::table('unit')->get();
        //print_r($data);


        /********************取得layout資訊************************ */
        $latout_employeeId = session()->get('employeeId');
        $Layout = new LayoutController;
        $data['pages'] = $Layout->pages_db($latout_employeeId);
        /********************************************************* */

        return view('product/product_quote',$data);
     }
     #報價寫入
    function quote_add(Request $Request){
        $productId = $Request->productId;
        $supplierId = $Request->supplierId;
        if($supplierId=='0'){
            Tool::Talk('3','請選擇供應商','');
            exit();
        }
        #10.18報價計算折扣先註解
        #$rebate = 1-($Request->rebate*0.01);
        $chk = DB::table('supplier_quote')
        ->where('productId',$productId)
        ->where('supplierId',$supplierId)
        ->count();
        //先查詢廠商有無報價過，有報價做更新尚未報價做寫入
        if($chk>=1){
            $update['quoteMinPP'] = $Request->quoteMinPP;
            $update['quoteMinPrice'] = $Request->quoteMinPrice;
            $update['quoteMinUnit'] = $Request->quoteMinUnit;
            $update['quoteMaxPP'] = $Request->quoteMaxPP;
            $update['quoteMaxPrice'] = $Request->quoteMaxPrice;
            $update['quoteMaxUnit'] = $Request->quoteMaxUnit;
            $update['quoteDate'] = $Request->quoteDate;
            $update['is_tax'] = $Request->is_tax;
            $update['discount'] = $Request->rebate;
            $update['quoteRemark'] = $Request->quoteRemark;
            DB::table('supplier_quote')
            ->where('productId',$productId)
            ->where('supplierId',$supplierId)
            ->update($update);
        }else{
            $insert['supplierId'] = $supplierId;
            $insert['productId'] = $productId;
            $insert['quoteMinPP'] = $Request->quoteMinPP;
            $insert['quoteMinPrice'] = $Request->quoteMinPrice;
            $insert['quoteMinUnit'] = $Request->quoteMinUnit;
            $insert['quoteMaxPP'] = $Request->quoteMaxPP;
            $insert['quoteMaxPrice'] = $Request->quoteMaxPrice;
            $insert['quoteMaxUnit'] = $Request->quoteMaxUnit;
            $insert['quoteDate'] = $Request->quoteDate;
            $insert['is_tax'] = $Request->is_tax;
            $insert['discount'] = $Request->rebate;
            $insert['quoteRemark'] = $Request->quoteRemark;
            DB::table('supplier_quote')->insert($insert);
        }
        Tool::Talk('2','',route('product_quote',['productId'=>$productId]));
    }
    /************進貨查詢************/
    function product_purchase(Request $Request){

        $data['productId'] = $Request->productId;
        $data['pd'] = $this->productMax_Min_rowdb($data['productId']);
        $data['quote'] =$this->product_purchase_db($data['productId']);
        //
        $data['supplier'] =$this->supplier_db();
        //$data['unit'] = DB::table('unit')->get();

        /********************取得layout資訊************************ */
        $latout_employeeId = session()->get('employeeId');
        $Layout = new LayoutController;
        $data['pages'] = $Layout->pages_db($latout_employeeId);
        /********************************************************* */

        return view('product/product_purchase',$data);
     }

     //商品今日販賣數量
     public function salecount(Request $req){

        $storeId = $req->storeId;
        $salecount = DB::select("SELECT A.productId , SUM(A.quantity) AS SaleCount,B.orderTime FROM order_detail AS A
                                 LEFT JOIN orders AS B
                                 ON A.orderNum = B.orderNum
                                 WHERE TO_DAYS(orderTime) = TO_DAYS(NOW())
                                 AND B.storeId = '$storeId'
                                 GROUP BY A.productId");

        return ['success'=>true,'salecount'=>$salecount];

     }

}
