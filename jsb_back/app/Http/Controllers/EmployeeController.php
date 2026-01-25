<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use  App\Models\Employee_db;
use Illuminate\Support\Facades\DB;
use App\Traits\HelperTraits;
use App\Http\Controllers\LayoutController;

class EmployeeController extends Employee_db
{
    use HelperTraits;
    public function show()
    {
        $storeId = session()->get('storeId');
        $data['result'] =$this->all_employee($storeId);
        return view('employee/show', $data);
    }
    public function add()
    {
        $employee_number = $this->employee_number_db();
        $numberCarry = $employee_number[0]->employeeId+1;
        $data['employeeId'] = str_pad($numberCarry,6,"0",STR_PAD_LEFT);
       
        return view('employee/add',$data);
    }
    public function create(Request $req)
    {
        $storeId = session()->get('storeId');

        $inputfile = $req->inputfile;
        $employeeId = $req->employeeId;
        $fileName = $this->random_cid();
        $inputfile_pic=$this->teacherImg('inputfile',$fileName);
        $insert['employeeId'] = $req->employeeId;
        $insert['password'] = md5('gini'.$req->pwd);
        $insert['headImg'] = $fileName.'.jpg';
        $insert['name'] = $req->name;
        $insert['gender'] = $req->gender;
        $insert['phone'] = $req->phone;//帳號
        $insert['IDNumber'] = $req->IDNumber;
        $insert['birthday'] = $req->birthday;
        $insert['email'] = $req->email;
        $insert['education'] = $req->education;
        $insert['school'] = $req->school;
        $insert['storeId'] = $storeId;

        $insert['commAddr'] = $req->commAddr;
        $insert['resiAddr'] = $req->resiAddr;
        $insert['TEL'] = $req->TEL;
        $insert['urgentPsn'] = $req->urgentPsn;
        $insert['urgentTel'] = $req->urgentTel;
        $insert['comment'] = $req->comment;
        $insert['entryDate'] = $req->entryDate;
        //$insert['resignDate'] = $req->resignDate;
        DB::table('employee')->insert($insert);
        $this->talk('新增成功', route('employeeShow'), 3);
    }
    public function detail(Request $request)
    {
        $storeId = session()->get('storeId');
        $employeeId = $request->employeeId;
        $data['details'] = $this->employee_detail($storeId,$employeeId);
        return view('employee/employee_detail',$data);
    }
    public function disable(Request $req){
        $employeeId = $req->employeeId; 
        $update['isResign'] = $req->isResign;
        DB::table('employee')
        ->where('employeeId',$employeeId)
        ->update($update);
        $this->talk('',url()->previous(), 2);
    }
    public function detailEdit(Request $req){ 
        $employeeId = $req->employeeId;
        $pwd = $req->pwd;
        //如果有上傳新圖片
        if($_FILES['inputfile']["size"]!=0){
            $fileName = $this->random_cid();
            $inputfile_pic=$this->teacherImg('inputfile',$fileName);
			$update['headImg'] = $fileName.'.jpg';
			//把舊照片刪掉
			$headImg = $req->headImg;
            if(file_exists("assets/images/avatars/".$headImg)){
                unlink("assets/images/avatars/".$headImg);//將檔案刪除
            }
		}
        
        if(!empty($pwd)){
            $update['password'] = md5('gini'.$pwd);
        }

        $storeId = session()->get('storeId');

        $update['name'] = $req->name;
        $update['gender'] = $req->gender;
        $update['phone'] = $req->phone;//帳號
        $update['IDNumber'] = $req->IDNumber;
        $update['birthday'] = $req->birthday;
        $update['email'] = $req->email;
        $update['education'] = $req->education;
        $update['school'] = $req->school;
        $update['storeId'] = $storeId;
      
        $update['commAddr'] = $req->commAddr;
        $update['resiAddr'] = $req->resiAddr;
        $update['TEL'] = $req->TEL;
        $update['urgentPsn'] = $req->urgentPsn;
        $update['urgentTel'] = $req->urgentTel;
        $update['comment'] = $req->comment;
        $update['entryDate'] = $req->entryDate;
        $update['resignDate'] = $req->resignDate;
        DB::table('employee')
        ->where('employeeId',$employeeId)
        ->update($update);
        $this->talk('',url()->previous(), 2);
    }
    


}
