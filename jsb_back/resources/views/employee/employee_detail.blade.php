@extends('layout')
<!-- 引用模板 -->
@section('head')
@endsection
@section('content')
    <div class="bg-white p-3">
        <div class="row p-2 justify-content-between align-items-center border-bottom">
            <!--breadcrumb-->
           <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3 col-md-6">
               <div class="breadcrumb-title pe-3">供應商與員工</div>
               <div class="ps-3">
                   <nav aria-label="breadcrumb">
                       <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url()->previous()}}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{route('employeeShow')}}">員工資料</a></li>
                            <li class="breadcrumb-item active" aria-current="page">編輯員工</li>
                       </ol>
                   </nav>
               </div>
           </div>
           <!-- -------------------------- -->
       </div>
       {{--  --}}
        <div class="content-wrap mt-3">
            <div class="main">
                <div class="card alert ">
                   
                        <div class="card-body">
                            <form action="{{route('employee_detailEdit')}}" enctype="multipart/form-data"  method="POST" class="row g-3">
                                 @csrf
                                 <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bxs-edit me-1 font-22 text-primary"></i>
                                    </div>
                                    <h5 class="mb-0 text-primary">員工資料</h5>
                                </div>
                                <hr>
                                {{--  --}}
                                <div class="col-lg-12">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <img id="preview" src="{{asset('assets/images/avatars/'.$details[0]->headImg.'')}}" class="rounded-circle p-1 border" width="95" height="95" alt="...">
                                        </div>
                                       
                                        <div class="col-lg-8">
                                            <label for="inputFirstName" class="form-label">照片上傳</label>
                                                <input type="hidden" name="headImg" value="{{$details[0]->headImg}}">
                                                <div class="input-group">
                                                    <input type="file" name="inputfile" class="form-control" onchange="readURL(this)" accept="image/gif, image/jpeg, image/png" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                {{--  --}}
                                <div class="col-md-4">
                                    <label for="inputFirstName" class="form-label">員工編號</label>
                                    <input type="text" class="form-control" name="employeeId" value="{{$details[0]->employeeId}}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">姓名</label>
                                    <input type="text" name="name" class="form-control" value="{{$details[0]->name}}">
                                </div>
                                <div class="col-md-4">
                                    <label for="inputEmail" class="form-label">性別</label>
                                    <select class="form-select" name="gender">
                                        <option value="1" @if(intval($details[0]->gender)==1)selected @endif>男</option>
                                        <option value="0" @if(intval($details[0]->gender)==0)selected @endif>女</option>
                                    </select>
                                </div>
                                {{--  --}}
                                <div class="col-md-6">
                                    <label for="inputLastName" class="form-label">手機 <font color="#FF0000">(POS帳號)</font></label>
                                    <input type="tel" name="phone" class="form-control" value="{{$details[0]->phone}}">
                                </div>
                                <div class="col-md-6">
                                    <label for="inputLastName" class="form-label la">POS密碼</label>
                                    <input type="password" name="pwd" class="form-control" value="">
                                </div>    
                                {{--  --}}
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">身分證字號</label>
                                    <input type="text" name="IDNumber" class="form-control" value="{{$details[0]->IDNumber}}">
                                </div>
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">生日</label>
                                    <input type="date" name="birthday" class="form-control" value="{{$details[0]->birthday}}">
                                </div>
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">信箱</label>
                                    <input type="text" name="email" class="form-control" value="{{$details[0]->email}}">
                                </div>
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">最高學歷</label>
                                    <input type="text" name="education" class="form-control" value="{{$details[0]->education}}">
                                </div>
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">學校名稱</label>
                                    <input type="text" name="school" class="form-control" value="{{$details[0]->school}}">
                                </div>
                                 {{-- 其他  --}}
                                 <div class="card-title d-flex align-items-center mt-4">
                                    <div><i class="bx bxs-edit me-1 font-22 text-primary"></i>
                                    </div>
                                    <h5 class="mb-0 text-primary">其他</h5>
                                </div>
                                <hr>
                                {{--  --}}
                                <div class="col-md-6">
                                    <label for="inputLastName" class="form-label">住址</label>
                                    <input type="text" name="commAddr" class="form-control" value="{{$details[0]->commAddr}}">
                                </div>
                                <div class="col-md-6">
                                    <label for="inputLastName" class="form-label">戶籍地址</label>
                                    <input type="text" name="resiAddr" class="form-control" value="{{$details[0]->resiAddr}}">
                                </div>
                                {{--  --}}
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">電話(家)</label>
                                    <input type="tel" name="TEL" class="form-control" value="{{$details[0]->TEL}}">
                                </div>
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">緊急聯絡人</label>
                                    <input type="text" name="urgentPsn" class="form-control" value="{{$details[0]->urgentPsn}}">
                                </div>
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">連絡人電話</label>
                                    <input type="text" name="urgentTel" class="form-control" value="{{$details[0]->urgentTel}}">
                                </div>
                                <div class="col-md-12">
                                    <label for="inputLastName" class="form-label">備註</label>
                                    <input type="text" name="comment" class="form-control" value="{{$details[0]->comment}}">
                                </div>
                                {{--  --}}
                                <div class="col-md-4">
                                    <label for="entryDate" class="form-label">入職日期</label>
                                    <input type="date" name="entryDate" class="form-control" value="{{$details[0]->entryDate}}">
                                </div>
                                <div class="col-md-4">
                                    <label for="resignDate" class="form-label">離職日期</label>
                                    <input type="date" name="resignDate" class="form-control" value="{{$details[0]->resignDate}}">
                                </div>

                                {{--  --}}
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary px-5">更新</button>
                                </div>
                                {{--  --}}
                            </form>
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection
