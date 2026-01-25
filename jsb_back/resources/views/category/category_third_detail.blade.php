@extends('layout')
<!-- 引用模板 -->

@section('head')
<style>
    table tr:first-child td {
        font-size: 1.2rem !important;
    }

    table tr td:nth-child(-n+5) {
        border-right: 1px solid rgb(202, 202, 202);
        vertical-align: middle;
    }

    @media all and (min-width:1200px) {
        a.addProduct {
            max-width: 150px;
        }
    }

    @media all and (max-width:768px) {
        table tr:first-child td {
            font-size: 1rem !important;
        }
    }

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

    .searchBtn {
        text-align: right;
        margin: 1vh 0;
    }

    .searchBtn input {
        padding: 5px 10px;
        font-size: 1.2rem;
        border-radius: 3px;
        border: 1px solid rgb(204, 204, 204);
    }

    .searchBtn button {
        border: none;
        font-size: 1.2rem;
        font-weight: 600;
        background: white;
        color: rgb(24, 175, 24);
        border: 1px solid rgb(24, 175, 24);
        padding: 5px 10px;
        border-radius: 3px;
        box-sizing: content-box;


    }

    .searchBtn button:hover {
        background: rgb(24, 175, 24);
        color: white;
        border: 1px solid rgb(24, 175, 24);

    }
</style>

@endsection

@section('content')

<div class="bg-white p-2">
    <div class="row  p-2 justify-content-between align-items-center border-bottom">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3 col-md-6">
            <div class="breadcrumb-title pe-3">商品管理</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{route('ProductsCatergory')}}">主分類管理</a></li>
                        <li class="breadcrumb-item"><a href="{{route('ProductsCatergoryDetail',['cateMainId'=>$cateMainId])}}">次分類管理</a></li>
                        <li class="breadcrumb-item" aria-current="page">子分類管理</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- -------------------------- -->
        <div class="col-md-2 col-12 text-end addProduct">
            <a href="{{route('AddProductsCatergoryThree',['cateMainId'=>$cateMainId,'cateMidId'=>$cateMidId])}}" class="btn btn-primary ">新增子分類</a>
        </div>
    </div>
    <!-- <div class="searchBtn">
        <input type="text" placeholder="搜尋次分類">
        <button class="">搜尋</button>
    </div> -->
    <table class="table my-4 table-hover border">
        <tr>
            <td width="15%" class="text-center  fw-bold">主分類</td>
            <td width="15%" class="text-center  fw-bold">次分類</td>
            <td width="15%" class="text-center  fw-bold">分類</td>
            <td width="15%" class="text-center  fw-bold">啟用</td>
            <td width="15%" class="text-center  fw-bold">排序</td>
            <td width="15%" class="text-center  fw-bold">操作</td>
        </tr>
        @foreach ($cateKid as $cateKid)
        <tr>
            <td class="text-center">{{$cateKid->cateMainName}}</td>
            <td class="text-center">{{$cateKid->cateMidName}}</td>
            <td class="text-center">{{$cateKid->cateKidName}}</td>
            <td class="text-center">
                <form action="">
                    <label class="switch">
                        @if($cateKid->enable == 1)
                        <input type="checkbox" checked onchange="changeEnable(<?=$cateKid->id?>,0)">
                        @else
                        <input type="checkbox" onchange="changeEnable(<?=$cateKid->id?>,1)">
                        @endif 
                        <span class="slider round"></span>
                    </label>
                </form>
            </td>
            <td class="text-center">{{$cateKid->sort}}</td>
            <td class="text-center">
                <a class="btn btn-warning fw-bold m-1 btn-sm" href="{{route('editKidCategory',['cateMainId'=>$cateMainId,'cateMidId'=>$cateMidId,'cateKidId'=>$cateKid->id])}}">編輯</a>
                <button class="btn btn-danger fw-bold m-1 btn-sm" data-bs-toggle="modal" data-bs-target="#DeleteControl" onclick="checkDel('<?=$cateKid->id?>')">刪除</button>
            </td>
        </tr>
        @endforeach
    </table>
    
</div>
<script>
    function changeEnable(id,enable){
        $.ajax({
            headers:{
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
		    type:'POST',
		    url:"{{url('category/updateKidCateEnable')}}",
		    data:{
            id : id,
			enable : enable,                    
		    },
		    dataType:'html',
		    success:function(msg){      
                //alert(msg);
                console.log(msg);
                window.location.reload();                      
		    }
		});
    }

    function checkDel(cateKidId){
        if(window.confirm("確認刪除分類嗎?")){
            cateKidDelete(cateKidId);
        }else{
            return false;
        }
    }

    function cateKidDelete(cateKidId){
        $.ajax({
            headers:{
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
		    type:'POST',
		    url:"{{url('category/deleteKidCategory')}}",
		    data:{
                cateKidId : cateKidId                 
		    },
		    dataType:'html',
		    success:function(msg){      
                window.location.reload();                      
		    }
		});
    }
</script>






@endsection