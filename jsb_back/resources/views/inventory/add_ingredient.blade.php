@extends('layout')
<link href="{{asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet" />
<link href="{{asset('assets/plugins/select2/css/select2-bootstrap4.css')}}" rel="stylesheet" />
<!-- 引用模板 -->
@section('head')
@endsection
@section('content')
    <div class="bg-white p-3">
        <div class="row p-2 justify-content-between align-items-center border-bottom">
            <!--breadcrumb-->
           <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3 col-md-6">
               <div class="breadcrumb-title pe-3">庫存成本管理</div>
               <div class="ps-3">
                   <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{route('inventory.index')}}">項目管理</a></li>
                            <li class="breadcrumb-item active" aria-current="page">新增項目</li>
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
                            <form action="{{route('inventory.store')}}" enctype="multipart/form-data" method="POST" class="row g-3">
                                @csrf

                                {{-- Title --}}
                                <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bxs-edit me-1 font-22 text-primary"></i></div>
                                    <h5 class="mb-0 text-primary">新增原物料</h5>
                                </div>
                                <hr>

                                {{-- 圖片 --}}
                                <div class="col-lg-12">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <img id="preview" src="{{ asset('assets/images/avatars/avatar-0.png') }}"
                                                class="rounded p-1 border" width="160" height="160">
                                        </div>
                                        <div class="col-lg-8">
                                            <label class="form-label">照片上傳</label>
                                            <div class="input-group">
                                                <input type="file" name="imageUrl" class="form-control"
                                                    onchange="readURL(this)" accept="image/*">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 名稱 --}}
                                <div class="col-md-4">
                                    <label class="form-label">名稱</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>

                                {{-- 類別 --}}
                                <div class="col-md-4">
                                    <label class="form-label">類別</label>
                                    <select class="form-select" name="categoryMainId" required>
                                        <option value="">選擇類別</option>
                                        @foreach($ingredientsCateMain as $ic)
                                            <option value="{{$ic->id}}">
                                                {{$ic->name}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- 單位 --}}
                                <div class="col-md-4">
                                    <label class="form-label">單位（如 g / ml / 個）</label>
                                    <input type="text" name="unit" class="form-control" value="g" required>
                                </div>

                                {{-- 安全庫存 --}}
                                <div class="col-md-4">
                                    <label class="form-label">安全庫存</label>
                                    <input type="number" step="1" name="safeAmount" class="form-control" required>
                                </div>

                                {{-- 目前庫存 --}}
                                <div class="col-md-4">
                                    <label class="form-label">目前庫存（預設 0）</label>
                                    <input type="number" step="1" name="stockAmount" class="form-control" value="0">
                                </div>

                                {{-- 送出 --}}
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary px-5">送出</button>
                                </div>
                            </form>

                        </div>
                </div>
            </div>
        </div>
    </div>

{{-- --}}
 @if(session('success') || session('error'))
    <div id="flashMsg"
        class="alert {{ session('success') ? 'alert-success' : 'alert-danger' }}"
        style="position: fixed; top:20px; left:50%; transform:translateX(-50%); z-index:9999;">
        {{ session('success') ?? session('error') }}
    </div>

    <script>
        setTimeout(()=> document.getElementById('flashMsg')?.remove(), 2000);
    </script>
@endif


@endsection
@section('script')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>

@endsection