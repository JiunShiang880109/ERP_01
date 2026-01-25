@extends('layout')
<!-- 引用模板 -->

@section('head')
<style>
    /* 啟用的樣式 */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 30px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 24px;
        width: 24px;
        left: 5px;
        bottom: 3px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked+.slider {
        background-color: #1bc20b;
    }

    /* input:focus+.slider {
    box-shadow: 0 0 1px #2196F3;
    } */

    input:checked+.slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }
</style>

@endsection

@section('content')


<div class="bg-white p-3">
    <!-----------------------breadcrumb----------------------------->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3 col-md-6">
        <div class="breadcrumb-title pe-3">商品管理</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{route('ProductsCatergory')}}">主分類管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">新增主分類</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr/>
    <!-------------------------------------------------------------->    
    <form action="{{route('CateMainInsert')}}" method="POST" class="p-2">
    {{ csrf_field() }}
        <div class="p-2">
        <div class="p-2">
        <div class="my-4 row  align-items-center g-0">
            <h3 class="col-xxl-1 col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">分類名稱</h3>
            <div class="col-md-10 col-12">
                <input class="form-control " type="text" placeholder="分類名稱" name="cateMainName" required>
            </div>
        </div>
        <div class="my-4 row   align-items-center g-0">
            <h3 class="col-xxl-1 col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">啟用</h3>
            <label class="switch">
                <input type="checkbox" checked  name="enable" value="1">
                <span class="slider round"></span>
            </label>
        </div>
        <div class="my-4 row   align-items-center g-0">
            <h3 class="col-xxl-1 col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">排序</h3>
            <div class="col-md-10 col-12">
                <select class="form-select" aria-label="Default select example" name="sort">
                    <option selected value="0">選擇排序</option>
                    <?for($i=1;$i<=50;$i++){?>
                        <option value="<?=$i?>"><?=$i?></option>
                    <?}?>
                </select>
            </div>
        </div>
        <div class="text-end">
            <a type="submit" href="{{route('ProductsCatergory')}}" class="btn btn-secondary">取消</a>
            <button type="submit" class="btn btn-primary">送出</button>
        </div>
        </div>
        </div>
    </form>
    



</div>



@endsection