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
                            <li class="breadcrumb-item"><a href="{{route('category')}}">商品類別</a></li>
                            <li class="breadcrumb-item active" aria-current="page">編輯類別</li>
                        </ol>
                   </nav>
               </div>
           </div>
           <!-- -------------------------- -->
       </div>
       {{--  --}}
        <div class="content-wrap mt-3">
            <div class="main">
                <div class="card alert">
                        <div class="card-body">
                            <form action="{{route('category_update')}}"  method="POST" class="row g-3">
                                 @csrf
                                 <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bxs-edit me-1 font-22 text-primary"></i>
                                    </div>
                                    <h5 class="mb-0 text-primary">編輯類別</h5>
                                </div>
                                <hr>
                                {{--  --}}
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">類別名稱</label>
                                    <input type="hidden" name="cateId" class="form-control" value="{{$cate[0]->id}}" required>
                                    <input type="text" name="category_title" class="form-control" value="{{$cate[0]->category_title}}" required>
                                </div>
                                 {{--  --}}
                                 <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">排序</label>
                                    <input type="text" name="sort" class="form-control" value="{{$cate[0]->sort}}" required>
                                </div>
                                {{--  --}}
                                <div class="col-md-3">
                                    <label for="inputLastName" class="form-label">上架/下架</label>
                                    <select class="form-select" name="enable">
                                            <option value="1" @if($cate[0]->enable==1) selected @endif>上架</option> 
                                            <option value="0" @if($cate[0]->enable==0) selected @endif>下架</option> 
                                    </select>
                                </div>
                                 {{--  --}}
                              
                                 {{--  --}}
                                 <div class="col-12">
                                    <button type="submit" class="btn btn-primary px-5">送出</button>
                                    <div onclick="javascript:if(confirm('注意，類別底下有商品不能被刪除?'))
                                    location='{{route('category_delete',['id'=>$cate[0]->id])}}'" class="btn btn-danger px-5">刪除</div>
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
@endsection