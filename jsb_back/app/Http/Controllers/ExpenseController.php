<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Employee_db;
use App\Models\Expense_Cate_Main;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Exp;

class ExpenseController extends Controller
{
    public function index(){
        $storeId = session()->get('storeId');
        $data['expenses'] = Expense::where('storeId', $storeId)
            ->orderBy('date', 'desc')
            ->get();

        // dd(session()->all());

        return view('expenses.index', $data);
    }
    //新增
    public function add(){
        $storeId = session()->get('storeId');
        $categoryMain= Expense_Cate_Main::where('storeId', $storeId)
            ->where('enable', 1)
            ->get();

        return view('expenses.add', compact('categoryMain'));
    }

    public function store(Request $request)
    {
        $storeId = session()->get('storeId');
        $emp = Employee_db::where('employeeId', $request->employeeId)->firstOrFail();

        Expense::create([
            'storeId' => $storeId,
            'employeeId'=> $request->employeeId,
            'employeeName'=> $emp->name, // 快照寫入當前員工姓名
            'category_main_id' => $request->category_main_id,
            'category_sub' => $request->category_sub,
            'amount' => $request->amount,
            'note' => $request->note,
            'payMethod' => $request->payMethod,
            'date' => $request->date,
        ]);

        return redirect()->route('expenses.index')
            ->with('success', '資料已新增');
    }
    //修改
    public function edit($id){
        $storeId = session()->get('storeId');
        $expense = Expense::where('storeId', $storeId)
            ->where('id', $id)
            ->firstOrFail();

        $categoryMain= Expense_Cate_Main::where('storeId', $storeId)
            ->where('enable', 1)
            ->get();
            
        return view('expenses.edit',  compact('expense', 'categoryMain'));
    }

    public function update(Request $request, $id){
        $storeId = session()->get('storeId');

        $expense = Expense::where('storeId', $storeId)
            ->where('id', $id)
            ->firstOrFail();
        
        $emp = Employee_db::where('employeeId', $request->employeeId)->firstOrFail();

        $expense->update([
            'date'=>$request->date,
            'employeeId'=> $request->employeeId,
            'employeeName'=> $emp->name, // 快照寫入當前員工姓名
            'category_main_id'=>$request->category_main_id,
            'category_sub'=>$request->category_sub,
            'amount'=>$request->amount,
            'payMethod'=>$request->payMethod,
            'note'=>$request->note,
        ]);

        return redirect()->route('expenses.index')
            ->with('success', '資料已修改');

    }
    //刪除
    public function delete($id){
        $storeId = session()->get('storeId');
        $expense = Expense::where('storeId', $storeId)
            ->where('id', $id)
            ->firstOrFail();
    
        $expense->delete();
        
        return redirect()->route('expenses.index')
            ->with('success', '支出已刪除');
    
    }

    //支出類別
    public function categoryIndex(){
        $storeId = session()->get('storeId');
        $categoryMain=Expense_Cate_Main::where('storeId', $storeId)->get();

        return view('expenses.category', compact('categoryMain'));
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

        Expense_Cate_Main::create([
            'storeId' => $storeId,
            'sort' => $request->sort,
            'name' => $request->name,
            
        ]);

        return redirect()->route('expenses.category')
            ->with('success', '新增成功');    

    }

    //修改
    public function category_update(Request $request, $id){
        $storeId = session()->get('storeId');

        $cate = Expense_Cate_Main::where('storeId', $storeId)
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:50',
        ],[
            'name.required' => '請輸入類別名稱',
        ]);

        // dd($request->all());
        //檢查同店同名，但排除自己這一筆
        $duplicate = Expense_Cate_Main::where('storeId', $storeId)
            ->where('name', $request->name)
            ->where('id', '!=', $id)
            ->first();

        if($duplicate){
            return back()
                ->withInput()
                ->with('error', '類別名稱已存在，請更換名稱');
        }

        $cate->update([
            
            'sort' => $request->sort,
            'name' => $request->name,
            
        ]);

        return redirect()->route('expenses.category')
            ->with('success', '更新成功');    

    }

    //刪除
    public function category_delete($id){
        $storeId = session()->get('storeId');
        $cate = Expense_Cate_Main::where('storeId', $storeId)
            ->where('id', $id)
            ->firstOrFail();
        
        Expense::where('category_main_id', $id)
            ->update(['category_main_id' => null]);

        $cate->delete();
        
        return redirect()->route('expenses.category')
            ->with('success', '類別已刪除');
    
    }
}
