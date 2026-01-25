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
        <div class="breadcrumb-title pe-3">商品資料</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">商品管理</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-12">
            <div class="d-flex  align-items-center">
                <div class=" m-b-10 mx-1">
                    <a href="{{ route('AddProducts') }}" class="btn btn-primary mb-3 mb-lg-0">
                        <i class="bx bxs-plus-square"></i>新增商品</a>
                </div>
                <div class=" m-b-10 mx-1">
                    <a href="{{route('ProductExportCSV')}}" class="btn btn-warning mb-3 mb-lg-0">匯出商品CSV</a>
                </div>
                <div class=" m-b-10 mx-1">
                    <a href="{{route('ProductExportXLSX')}}" class="btn btn-warning mb-3 mb-lg-0">匯出商品XLSX</a>
                </div>
                <div class=" m-b-10 mx-1">
                    <form action="{{route('ProductImportCSV')}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label class="btn btn-warning">
                            商品匯入 <input type="file" name="file" onchange="this.form.submit()" hidden>
                        </label>
                    </form>
                </div>
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
                            <td>規格</td>
                            <td>價格</td>
                            <td>配方/成分</td>
                            <td>稅別</td>
                            <td>操作</td>
                        </tr>
                    </thead>
                    <tbody style="vertical-align:middle;">
                        @foreach($product as $key=>$value)
                        <tr>
                            <td style="width:50px;">
                                <?=$key+1?>
                            </td>
                            <td style="width:220px;">
                                <div style="position:relative;">
                                    @if($value->enable==0)
                                    <div class="layer">
                                        <span class="badge bg-danger">下架</span>
                                    </div>
                                    @endif
                                    <img src="@if($value->imageUrl==null) {{ asset('assets/images/avatars/avatar-0.png') }} @else {{ asset('assets/images/products/'.$value->imageUrl.'') }} @endif "
                                        class="rounded p-1 border" width="160" alt="...">
                                </div>
                            </td>
                            <td>
                                <h6 class="mt-2 font-14 text-default">{{ $value->product_title }}</h6>
                            </td>
                            <td>{{ $value->category_title }}</td>
                            <td>
                                @foreach ($value->pd_taste as $key2=>$value2)
                                    {{ $value2->customCateTitle }}
                                @endforeach

                            </td>
                            <td>
                                <h6>{{ $value->price }}</h6>
                            </td>
                            <td>
                                @if($value->recipe->count() > 0)
                                    @foreach($value->recipe as $r)
                                        <span class="badge bg-info text-dark">
                                            {{ $r->ingredient->name ?? '' }} {{ $r->usageQty }}{{ $r->unit }}
                                        </span><br>
                                    @endforeach
                                @else
                                    <span class="text-secondary">無配方</span>
                                @endif
                            </td>

                            <td>{{ ($value->taxType==1)?("有"):("無"); }}
                            </td>
                            <!------------------------------------------------------------------------------------------------------------------------>
                            <td>
                                <a class="btn btn-success fw-bold m-1 btn-sm"
                                    href="{{ route('ProductsDetail',['productId'=>$value->productId]) }}">編輯</a>
                                <!-- 帶商品id-->
                                <button type="button" class="btn btn-danger fw-bold m-1 btn-sm"
                                    onclick="javascript:if(confirm('確定要刪除嗎?'))location='{{ route('product_del',['productId'=>$value->productId]) }}'">刪除</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>


</div>


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