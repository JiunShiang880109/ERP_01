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
                    <li class="breadcrumb-item active" aria-current="page">項目管理</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-12">
            <div class="d-flex  align-items-center">
                <div class=" m-b-10 mx-1">
                    <a href="{{ route('inventory.add_ingredient') }}" class="btn btn-primary mb-3 mb-lg-0">
                        <i class="bx bxs-plus-square"></i>新增項目</a>
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
                            <td>商品</td>
                            <td>名稱</td>
                            <td>類別</td>
                            <td>單位</td>
                            <td>目前庫存</td>
                            <td>安全庫存</td>
                            <td>最近成本</td>
                            <td>操作</td>
                        </tr>
                    </thead>
                    <tbody style="vertical-align:middle;">
                        @foreach($ingredients as $ig)
                            <tr>
                                <td>{{$ig->id}}</td>
                                <td>
                                    @if($ig->imageUrl)
                                        <img src="{{ asset('assets/images/ingredients/'.$ig->imageUrl) }}"
                                            width="50" height="50" class="rounded">
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{$ig->name}}</td>
                                <td>{{$ig->ingredientsCateMain->name ?? '未設定類別'}}</td>
                                <td>{{$ig->unit}}</td>
                                <td>{{$ig->stockAmount}}</td>
                                <td>{{$ig->safeAmount}}</td>
                                <td>{{optional($ig->lastPurchase)->unitPrice ?? '-'}}</td>
                                <td>
                                    <div class="d-flex order-actions">
                                        <!-- <a href="{{--{{route('category_edit',['cateId'=>$value->id])}}--}}" class=""><i class="bx bxs-edit"></i></a> -->
                                        <a href="{{route('inventory.edit_ingredient', ['id' => $ig->id])}}" class="me-3 text-primary"><i class="bx bxs-edit"></i></a>
                                        
                                        <form action="{{route('inventory.delete', $ig->id)}}" method="POST" onsubmit="return confirm('確定要刪除該筆資料嗎?');" class="m-0">
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