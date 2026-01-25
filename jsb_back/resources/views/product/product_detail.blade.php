@extends('layout')
<link href="{{asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet" />
<link href="{{asset('assets/plugins/select2/css/select2-bootstrap4.css')}}" rel="stylesheet" />
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
<!-- 引用模板 -->
@section('head')
@endsection
@section('content')
    <div class="bg-white p-3">
        <div class="row p-2 justify-content-between align-items-center border-bottom">
            <!--breadcrumb-->
           <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3 col-md-6">
               <div class="breadcrumb-title pe-3">商品資料</div>
               <div class="ps-3">
                   <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{route('Products')}}">商品管理</a></li>
                            <li class="breadcrumb-item active" aria-current="page">編輯商品</li>
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
                            <form action="{{route('product_update')}}" enctype="multipart/form-data"  method="POST" class="row g-3">
                                 @csrf
                                 <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bxs-edit me-1 font-22 text-primary"></i>
                                    </div>
                                    <h5 class="mb-0 text-primary">編輯商品</h5>
                                </div>
                                <hr>
                                {{--  --}}
                                <div class="col-lg-12">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            @if($pd[0]->enable==0)
                                            <div style="position:relative;">
                                            <div class="layer">
                                                <span class="badge bg-danger">下架</span>
                                            </div>
                                                <img src="@if($pd[0]->imageUrl==null) {{ asset('assets/images/avatars/avatar-0.png') }} @else {{ asset('assets/images/products/'.$pd[0]->imageUrl.'') }} @endif "
                                                class="rounded p-1 border" width="160" alt="...">
                                            </div>
                                            @else
                                                <img src="@if($pd[0]->imageUrl==null) {{ asset('assets/images/avatars/avatar-0.png') }} @else {{ asset('assets/images/products/'.$pd[0]->imageUrl.'') }} @endif "
                                                class="rounded p-1 border" width="160" alt="...">
                                            @endif
                                            
                                            
                                        </div>
                                        <div class="col-lg-8">
                                            <label for="inputFirstName" class="form-label">照片上傳 <span class="text-danger">*建議尺寸 700 x 465</span></label>
                                                <input type="hidden" name="imageUrl" value="{{$pd[0]->imageUrl}}">
                                                <div class="input-group">
                                                    <input type="file" name="inputfile" class="form-control" onchange="readURL(this)" accept="image/gif, image/jpeg, image/png" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                {{--  --}}
                                <div class="col-md-4">
                                    <label for="inputFirstName" class="form-label">商品編號</label>
                                    <input type="text" name="productId" class="form-control" value="{{$pd[0]->productId}}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">商品名稱</label>
                                    <input type="text" name="product_title" class="form-control" value="{{$pd[0]->product_title}}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputEmail" class="form-label">類別</label>
                                    <select class="form-select" name="categoryId">
                                        @foreach ($cate as $item)
                                            <option value="{{$item->id}}" @if($item->id==$pd[0]->categoryId) selected @endif>{{$item->category_title}}</option> 
                                        @endforeach
                                    </select>
                                </div>
                                {{--  --}}
                                {{-- <div class="col-md-3">
                                    <label for="inputLastName" class="form-label">單位 </label>
                                    <input type="text" name="unit" class="form-control" value="{{$pd[0]->unit}}" required>
                                </div> --}}
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label la">價格</label>
                                    <input type="tel" name="price" class="form-control" value="{{$pd[0]->price}}" required>
                                </div>
                                {{-- 配方/成分 --}}
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">配方設定</label>
                                    
                                    <table class="table table-bordered" id="recipeTable">
                                        <thead>
                                            <tr>
                                                <td>原料</td>
                                                <td width="120">用量</td>
                                                <td width="80">單位</td>
                                                <td width="60"></td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- 回填資料 --}}
                                            @if(isset($recipe) && count($recipe)>0)
                                                @foreach($recipe as $r)
                                                <tr>
                                                    <td>
                                                        <select name="ingredientId[]" class="form-select">
                                                            @foreach($ingredients as $ig)
                                                                <option value="{{$ig->id}}" @if($ig->id==$r->ingredientId) selected @endif>
                                                                    {{$ig->name}}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>

                                                    <td>
                                                        <input type="number" step="0.01" name="usageQty[]" value="{{$r->usageQty}}" class="form-control">
                                                    </td>

                                                    <td>
                                                        <input type="text" name="unit[]" value="{{$r->unit}}" class="form-control">
                                                    </td>

                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>

                                    <button type="button" class="btn btn-success" onclick="addRow()">+ 新增原料</button>
                                </div>
                                {{--  --}}
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">稅別</label>
                                    <select class="form-select" name="taxType">
                                            <option value="1" @if($pd[0]->taxType==1) selected @endif>含稅</option> 
                                            <option value="0" @if($pd[0]->taxType==0) selected @endif>不含稅</option> 
                                    </select>
                                </div>
                                {{--  --}}
                                <div class="col-md-2">
                                    <label for="inputLastName" class="form-label">上架/下架</label>
                                    <select class="form-select" name="enable">
                                            <option value="1" @if($pd[0]->enable==1) selected @endif>上架</option> 
                                            <option value="0" @if($pd[0]->enable==0) selected @endif>下架</option> 
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="inputLastName" class="form-label">回饋點數(選填)</label>
                                    <input type="number" name="feedback_point" class="form-control" value="{{$pd[0]->feedback_point}}">
                                </div>
                                 {{--  --}}
                                 <div class="card-title d-flex align-items-center mt-4">
                                    <div><i class="bx bxs-edit me-1 font-22 text-primary"></i>
                                    </div>
                                    <h5 class="mb-0 text-primary">口味</h5>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <label class="form-label">餐點口味</label>
                                    <select class="multiple-select" name="customCateId[]" data-placeholder="選擇餐點口誤" multiple="multiple">
                                       
                                        @foreach ($pd_taste as $item)
                                        <option value="{{$item->id}}" selected>{{$item->customCateTitle}}</option>
                                        @endforeach
                                        @foreach ($taste as $item)
                                        <option value="{{$item->id}}">{{$item->customCateTitle}}</option>
                                        @endforeach
                                       
                                     
                                    </select>
                                </div>
                                 {{--  --}}
                                 <div class="col-12">
                                    <button type="submit" class="btn btn-primary px-5">送出</button>
                                </div>
                            </form>
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>

<script>
    $('.multiple-select').select2({
			theme: 'bootstrap4',
			width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
			placeholder: $(this).data('placeholder'),
			allowClear: Boolean($(this).data('allow-clear')),
		});

</script>
<script>
    function addRow(){
        let row = `
        <tr>
            <td>
                <select name="ingredientId[]" class="form-select">
                    @foreach($ingredients as $ig)
                        <option value="{{$ig->id}}">{{$ig->name}}</option>
                    @endforeach
                </select>
            </td>

            <td><input type="number" step="0.01" name="usageQty[]" class="form-control"></td>
            <td><input type="text" name="unit[]" class="form-control"></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
        </tr>
        `;
        $('#recipeTable tbody').append(row);
    }

    $(document).on("click",".removeRow",function(){
        $(this).closest("tr").remove();
    });

</script>
@endsection