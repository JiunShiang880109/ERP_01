@extends('layout')
<!-- 引用模板 -->
@section('head')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<style>
    .layer {
        background-color: rgba(0, 0, 0, 0.5);
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        z-index: 1;
        width: 160px;
    }
</style>
@endsection
@section('content')
<div class="bg-white p-3">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">庫存成本管理</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">清單管理</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-12">
            <div class="d-flex  align-items-center">
                <div class=" m-b-10 mx-1">
                    <a href="{{route('inventory.add_checklist')}}" class="btn btn-primary mb-3 mb-lg-0">
                        <i class="bx bxs-plus-square"></i>新增清單</a>
                </div>
                <!-- <div class=" m-b-10 mx-1">
                    <a href="" class="btn btn-warning mb-3 mb-lg-0">匯出商品CSV</a>
                </div>
                <div class=" m-b-10 mx-1">
                    <a href="" class="btn btn-warning mb-3 mb-lg-0">匯出商品XLSX</a>
                </div>
                <div class=" m-b-10 mx-1">
                    <form action="" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label class="btn btn-warning">
                            商品匯入 <input type="file" name="file" onchange="this.form.submit()" hidden>
                        </label>
                    </form>
                </div> -->
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="card alert">
            <div class="table-responsive invoice_list">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            
                            <td>#</td>
                            
                            <td>操作</td>
                            <td>狀態</td>
                            <td>訂單ID</td>
                            <td>廠商</td>
                            <td>訂貨日期</td>
                            <td>總金額</td>
                            <td>訂購人</td>
                            <td>登記人</td>
                            <td>訂貨商品 × 數量 × 單價</td>
                            <td>操作</td>          
                            
                        </tr>
                    </thead>
                    <tbody style="vertical-align:middle;">
                        @foreach($oreders as $oreder)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                {{-- 操作 --}}
                                <td>
                                    <div class="btn-group" role="group">
                                        @if($oreder->status == 0)
                                            <form action="{{ route('inventory.checklist.arrival', $oreder->id) }}" method="POST" 
                                                onsubmit="return confirm('確認入庫？');">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="mdi mdi-checkbox-marked-circle-outline"></i> 入庫
                                                </button>
                                            </form>

                                            <form action="{{ route('inventory.checklist.cancel', $oreder->id) }}" method="POST"
                                                onsubmit="return confirm('確定要取消？將標記此單為取消');">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="mdi mdi-eye"></i> 取消
                                                </button>
                                            </form>
                                        @elseif($oreder->status == 1)
                                            <button class="btn btn-secondary btn-sm" disabled>已入庫</button>
                                        @elseif($oreder->status == 2)
                                            <button class="btn btn-outline-dark btn-sm" disabled>已取消</button>
                                        @endif
                                    </div>
                                </td>
                                {{-- 狀態 --}}
                                <td>
                                    @if($oreder->status == 0)
                                        <span class="badge bg-warning">待入庫</span>
                                    @elseif($oreder->status == 1)
                                        <span class="badge bg-success">已入庫</span>
                                    @elseif($oreder->status == 2)
                                        <span class="badge bg-danger">此單已取消</span>
                                    @endif
                                </td>

                                <td>{{ $oreder->id }}</td> {{-- 訂單ID = 主key --}}
                                <td>{{ $oreder->supplier ?? '-' }}</td>
                                <td>{{ $oreder->purchaseDate ?? '-' }}</td>
                                <td>{{ $oreder->total }}</td>
                                <td>{{ $oreder->buyer ?? '-' }}</td>
                                <td>{{ $oreder->employeeName ?? '-' }}</td>

                                {{-- 顯示商品名稱+數量 --}}
                                <td>
                                    @foreach($oreder->details as $d)
                                        {{ $d->ingredient->name ?? '已刪除食材' }} × {{number_format($d->quantity,0,'.',',')}} × 
                                        @if($d->unitPrice)
                                            ${{ number_format($d->unitPrice,2) }}   
                                        @endif<br>
                                    @endforeach
                                </td>

                                {{-- 操作按鈕 --}}
                                <td>
                                    <div class="d-flex order-actions">
                                        <!-- <a href="{{--{{route('category_edit',['cateId'=>$value->id])}}--}}" class=""><i class="bx bxs-edit"></i></a> -->
                                        <!-- <a href="" class="me-3 text-primary"><i class="bx bxs-edit"></i></a> -->
                                        
                                        <form action="{{route('inventory.checklistDelete', $oreder->id)}}" method="POST" onsubmit="return confirm('確定要刪除該筆資料嗎?');" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link p-0 text-danger">
                                                <i class="bx bxs-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    
                                </td>
                            </tr>
                        @endforeach


                    </tbody>

                </table>
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
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}">
</script>
<script>
    $(document).ready(function () {
        $('#example').DataTable();
    });

    // $("input[name='file']").on('change',function(){
    //     $(this).submit()
    // })

</script>
@endsection