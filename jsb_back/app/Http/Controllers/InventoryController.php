<?php

namespace App\Http\Controllers;

use App\Models\Ingredients_db;
use App\Models\Ingredients_purchase_cateMain;
use App\Traits\HelperTraits;
use App\Models\Employee_db;
use App\Http\Controllers\Tool;
// use App\Models\Ingredients_purchase_list;
use App\Models\Ingredients_purchase_order;
use App\Models\Ingredients_purchase_order_detail;
use Illuminate\Http\Request;
use PHPUnit\Framework\MockObject\DuplicateMethodException;

class InventoryController extends Controller
{
    use  HelperTraits;

    //庫存成本類別
    public function categoryIndex(){
        $storeId = session()->get('storeId');
        $categoryMain = Ingredients_purchase_cateMain::where('storeId', $storeId)->get();
        
        return view('inventory.category', compact('categoryMain'));
    }
    //新增
    public function category_add(Request $request){
        $request->validate([
            'name' => 'required|string|max:50',
        ],[
            'name.required' => '請輸入類別名稱',
        ]);

        $storeId = session()->get('storeId');

        // dd($request->all());
        //檢查重複項目
        $duplicateName=Ingredients_purchase_cateMain::where('storeId', $storeId)
            ->where('name', $request->name)
            ->first();

        if($duplicateName){
            return back()->withInput()->with('error', '類別名稱已存在');
        }

        if ($request->sort !== null) {
            $duplicateSort = Ingredients_purchase_cateMain::where('storeId', $storeId)
                ->where('sort', $request->sort)
                ->first();

            if ($duplicateSort) {
                return back()
                    ->withInput()
                    ->with('error', '排序已存在');
            }
        }

        Ingredients_purchase_cateMain::create([
            'storeId' => $storeId,
            'sort' => $request->sort,
            'name' => $request->name,
            
        ]);

        return redirect()->route('inventory.category')
            ->with('success', '新增成功');    

    }
    //修改
    public function category_update(Request $request, $id){
        $storeId = session()->get('storeId');

        $cate = Ingredients_purchase_cateMain::where('storeId', $storeId)
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:50',
        ],[
            'name.required' => '請輸入類別名稱',
        ]);

        // dd($request->all());
        //檢查重複
        $duplicate = Ingredients_purchase_cateMain::where('storeId', $storeId)
            ->where('name', $request->name)
            ->where('id', '!=', $id)
            ->first();

        if($duplicate){
            return back()
                ->withInput()
                ->with('error', '類別名稱已存在');
        }

        if ($request->sort !== null) {
            $duplicateSort = Ingredients_purchase_cateMain::where('storeId', $storeId)
                ->where('sort', $request->sort)
                ->first();

            if ($duplicateSort) {
                return back()
                    ->withInput()
                    ->with('error', '排序已存在');
            }
        }

        $cate->update([
            
            'sort' => $request->sort,
            'name' => $request->name,
            
        ]);

        return redirect()->route('inventory.category')
            ->with('success', '更新成功');    

    }
    //刪除
    public function category_delete($id){
        $storeId = session()->get('storeId');
        $cate = Ingredients_purchase_cateMain::where('storeId', $storeId)
            ->where('id', $id)
            ->firstOrFail();
        
        // Expense::where('category_main_id', $id)
        //     ->update(['category_main_id' => null]);

        $cate->delete();
        
        return redirect()->route('inventory.category')
            ->with('success', '類別已刪除');
    
    }

    //庫存成本項目管理
    public function index(){
        $storeId = session()->get('storeId');
        $ingredients = Ingredients_db::with(['ingredientsCateMain', 'lastPurchase'])
            ->where('storeId', $storeId)->get();

        return view('inventory.index', compact('ingredients'));
    }
    //新增
    public function add(){
        $storeId = session()->get('storeId');
        $ingredientsCateMain= Ingredients_purchase_cateMain::where('storeId', $storeId)
            ->where('enable', 1)
            ->orderBy('sort', 'asc')
            ->get();

        return view('inventory.add_ingredient', compact('ingredientsCateMain'));
    }
    public function store(Request $request)
    {
        $storeId = session()->get('storeId');

        //自動建立資料夾
        $dateFolder = date('Y') . '/' . date('m') . '/';

        $uploadPath=public_path('assets/images/ingredients/' . $dateFolder);
        if(!file_exists($uploadPath)){
            mkdir($uploadPath, 0777, true);
        }

        //上傳圖片
        $imageUrl=null;
           
        if($request->hasFile('imageUrl')){
            $file = $request->file('imageUrl');
            //副檔名
            $extension = $file->getClientOriginalExtension();
            $fileName = $this->random_cid() . '.' . $extension;

            $file->move($uploadPath, $fileName);
            //儲存相對路徑
            $imageUrl = $dateFolder . $fileName;
        }

        Ingredients_db::create([
            'storeId' => $storeId,
            'categoryMainId' => $request->categoryMainId,
            'imageUrl' => $imageUrl,
            'name' => $request->name,
            'unit' => $request->unit,
            'safeAmount' => $request->safeAmount,
            'stockAmount' => $request->stockAmount,
            'enable' =>1,//預設啟用
        ]);

        return redirect()->route('inventory.index')
            ->with('success', '新增成功');
    }
    //修改
    public function edit($id){
        $storeId = session()->get('storeId');
        $ingredients = Ingredients_db::where('storeId', $storeId)
            ->where('id', $id)
            ->firstOrFail();

        $ingredientsCateMain= Ingredients_purchase_cateMain::where('storeId', $storeId)
            ->where('enable', 1)
            ->orderBy('sort', 'asc')
            ->get();
            
        return view('inventory.edit_ingredient',  compact('ingredients', 'ingredientsCateMain'));
    }
    public function update(Request $request, $id){
        $storeId = session()->get('storeId');

        $ingredients = Ingredients_db::where('storeId', $storeId)
            ->where('id', $id)
            ->firstOrFail();

        $dateFolder = date('Y') . '/' . date('m') . '/';
        $uploadPath = public_path('assets/images/ingredients/' . $dateFolder);

        if(!file_exists($uploadPath)){
            mkdir($uploadPath, 0777, true);
        }

        //預設保留舊圖
        $imageUrl = $ingredients->imageUrl;

        //若有新圖才更新
        if($request->hasFile('imageUrl')){
            $file = $request->file('imageUrl');
            $extension = $file->getClientOriginalExtension();
            $fileName = $this->random_cid() . '.' . $extension;

            $file->move($uploadPath, $fileName);
            //新圖覆蓋
            $imageUrl = $dateFolder . $fileName;
        }

        $ingredients->update([
            'categoryMainId' => $request->categoryMainId,
            'imageUrl' => $imageUrl,
            'name' => $request->name,
            'unit' => $request->unit,
            'safeAmount' => $request->safeAmount,
            'stockAmount' => $request->stockAmount,
            'enable' =>1,//預設啟用
        ]);

        return redirect()->route('inventory.index')
            ->with('success', '資料已修改');

    }
    //刪除
    public function delete($id){
        $storeId = session()->get('storeId');
        $ingredients = Ingredients_db::where('storeId', $storeId)
            ->where('id', $id)
            ->firstOrFail();
    
        $ingredients->delete();
        
        return redirect()->route('inventory.index')
            ->with('success', '項目已刪除');
    
    }

    //清單管理
    public function checkListIndex(){
        $storeId = session()->get('storeId');
        // $ingredientsList = Ingredients_purchase_list::where('storeId', $storeId)->get();
        $oreders = Ingredients_purchase_order::with('details.ingredient')
            ->where('storeId', $storeId)
            ->orderBy('id', 'desc')
            ->get();

        return view('inventory.checklist', compact('oreders'));
    }
    //新增
    public function checkList_add(){
        $storeId = session()->get('storeId');
        $ingredients = Ingredients_db::where('storeId', $storeId)
            ->where('enable', 1)
            ->orderBy('name')
            ->get();

        return view('inventory.add_checklist', compact('ingredients'));
    }
    public function checkList_store(Request $request){
        $storeId = session()->get('storeId');

        $request->validate([
            'purchaseDate' => 'required|date',
            'ingredientId.*' => 'required',
            'quantity.*'     => 'required|numeric|min:0.01',
            'unitPrice.*'    => 'required|numeric|min:0'
        ]);

        $emp = Employee_db::where('employeeId', $request->employeeId)->firstOrFail();
        
        //主單
        $order=Ingredients_purchase_order::create([
            'storeId'       => $storeId,
            'employeeId'    => $emp->employeeId,
            'employeeName'  => $emp->name,
            'buyer'         => $request->buyer,
            'supplier'      => $request->supplier,
            'invoiceNumber' => $request->invoiceNumber,
            'purchaseDate'  => $request->purchaseDate,
            'note'          => $request->note,
            'status'        => 0,
        ]);

        $total=0;

        //明細
        foreach($request->ingredientId as $i => $ingId){
            $qty  = $request->quantity[$i];
            $price= $request->unitPrice[$i];
            $lineTotal = $qty * $price;

            Ingredients_purchase_order_detail::create([
                'orderId'       => $order->id,
                'ingredientId'  => $ingId,
                'categoryMainId'=> $request->categoryMainId[$i] ?? null,
                'quantity'      => $qty,
                'unitPrice'     => $price,
            ]);

            $total += $lineTotal;
        }

        //total寫回主表
        $order->update(['total' => $total]);

        return redirect()->route('inventory.checklist')
            ->with('success', '紀錄已新增');
    }
    //取單位
    private function extractWeight($unitString)
    {
        return (int) filter_var($unitString, FILTER_SANITIZE_NUMBER_INT);
    }


    public function checkListArrival($id){
        $order = Ingredients_purchase_order::with('details.ingredient')->findOrFail($id);
        
        $tool = new Tool();

        // 更新庫存
        foreach($order->details as $dt){
            if($dt->ingredient){
                //進貨量寫入庫存
                $tool->updateIngredientCost(
                    $dt->ingredientId,
                    $dt->quantity,
                    $dt->unitPrice
                );
            }
        }

        // 更新狀態
        $order->status = 1;
        $order->save();

        return back()->with('success','入庫完成，庫存已更新');
    }

    public function checkListCancel($id){
        $order = Ingredients_purchase_order::findOrFail($id);

        // 更新狀態
        $order->status = 2;
        $order->save();

        return back()->with('success','此筆進貨紀錄已取消');
    }

    //刪除
    public function checkListDelete($id){
        $storeId = session()->get('storeId');
        $order = Ingredients_purchase_order::with('details')
            ->where('storeId', $storeId)
            ->findOrFail($id);
        
        //刪明細
        foreach($order->details as $d){
            $d->delete();
        }
        //刪主表
        $order->delete();
        
        return back()->with('success', '資料已刪除');
    
    }

}
