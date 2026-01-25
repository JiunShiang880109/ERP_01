@extends('layout')
<!-- 引用模板 -->
@section('head')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}"
    rel="stylesheet" />
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
                    <li class="breadcrumb-item active" aria-current="page">商品規格</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-12">
            <div class="row align-items-center">
                <div class="col-lg-3 col-xl-2 m-b-10">
                    <button type="button" class="btn btn-primary addProduct" data-bs-toggle="modal"
                    data-bs-target="#AddUnit">新增規格</button>
                </div>
            </div>
        </div>
    </div>
    {{-- 彈窗 --}}
    <!-- target需要互相對應 -->
    <div class="modal fade" id="AddUnit" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">新增規格</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('spec_insert')}}" method="POST">
                    @csrf
                    {{-- --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">類別</h3>
                        <div class="col-md-10 col-12">
                            <select class="form-select" name="cateId">
                                @foreach ($cate as $item)
                                    <option value="{{$item->id}}">{{$item->category_title}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{--  --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">規格</h3>
                        <div class="col-md-10 col-12">
                            <input name="customCateTitle" class="form-control " type="text" placeholder="ex:口味、糖份、冰塊">
                        </div>
                    </div>
                    {{-- --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">必填</h3>
                        <div class="col-md-10 col-12">
                            <label class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" value="1" name="require" required>
                                <div class="form-check-label">是</div>
                            </label>
                            <label class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" value="0" name="require">
                                <div class="form-check-label">否</div>
                            </label>
                        </div>
                    </div>
                    {{-- --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">選單</h3>
                        <div class="col-md-10 col-12">
                            <label class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" value="1" name="single" required>
                                <div class="form-check-label">單選</div>
                            </label>
                            <label class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" value="0" name="single">
                                <div class="form-check-label">多選</div>
                            </label>
                        </div>
                    </div>
                    {{--  --}}
                    <div class="m-4 row align-items-center g-0">
                        {{--  --}}
                        <div class="row">
                            <div class="col-md-7 col-7">
                                <label for="spec1" class="form-label">選單內容1..</label>
                                <input name="specification[]" class="form-control " type="text" id="spec1" placeholder="ex:加料、無糖" required>
                            </div>
                            <div class="col-md-5 col-5">
                                <label for="spec1" class="form-label">選單1價格</label>
                                <input name="price[]" class="form-control " type="tel" placeholder="免費不填">
                            </div>
                        </div>
                        {{--  --}}
                        <div class="row mt-2">
                            <div class="col-md-7 col-7">
                                <label for="spec2" class="form-label">選單內容2..</label>
                                <input name="specification[]" class="form-control " type="text" id="spec1" placeholder="ex:加料、3分糖">
                            </div>
                            <div class="col-md-5 col-5">
                                <label for="spec2" class="form-label">選單2價格</label>
                                <input name="price[]" class="form-control " type="tel" placeholder="免費不填">
                            </div>
                        </div>
                        {{--  --}}
                         <div class="row mt-2">
                            <div class="col-md-7 col-7">
                                <label for="spec3" class="form-label">選單內容3..</label>
                                <input name="specification[]" class="form-control " type="text" id="spec1" placeholder="ex:加料、半糖">
                            </div>
                            <div class="col-md-5 col-5">
                                <label for="spec3" class="form-label">選單3價格</label>
                                <input name="price[]" class="form-control " type="tel" placeholder="免費不填">
                            </div>
                        </div>
                         {{--  --}}
                         <div class="row mt-2">
                            <div class="col-md-7 col-7">
                                <label for="spec4" class="form-label">選單內容4..</label>
                                <input name="specification[]" class="form-control " type="text" id="spec1" placeholder="ex:加料、7分糖">
                            </div>
                            <div class="col-md-5 col-5">
                                <label for="spec4" class="form-label">選單4價格</label>
                                <input name="price[]" class="form-control " type="tel" placeholder="免費不填">
                            </div>
                        </div>
                         {{--  --}}
                         <div class="row mt-2">
                            <div class="col-md-7 col-7">
                                <label for="spec5" class="form-label">選單內容5..</label>
                                <input name="specification[]" class="form-control " type="text" id="spec1" placeholder="ex:加料、正常糖">
                            </div>
                            <div class="col-md-5 col-5">
                                <label for="spec5" class="form-label">選單5價格</label>
                                <input name="price[]" class="form-control " type="tel" placeholder="免費不填">
                            </div>
                        </div>
                       {{--  --}}
                        
                       
                    </div>
                    {{-- --}}
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-secondary" id="cancel"
                            data-bs-dismiss="modal">取消</button>
                        <input type="submit" class="btn btn-danger" id="send" value="送出">
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{--  --}}
    <div class="col-lg-12">
        <div class="card alert">
            <div class="table-responsive invoice_list">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <td>#</td>
                            <td>類別</td>
                            <td>規格<span class="text-danger font-14"> (是否必填)</span></td>
                            <td>選單<span class="text-danger font-14"> (單選/多選)</span></td>
                           
                            <td>編輯選單</td>
                        </tr>
                    </thead>
                    <tbody style="vertical-align:middle;">
                        @foreach($spec as $key=>$value)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$value->category_title}}</td>
                                <td>
                                    {{$value->customCateTitle}}
                                    @if ($value->require==1)
                                        <span class="font-14 text-success"><i class="bx bxs-check-circle"></i>必填</span>
                                    @else
                                        <span class="font-14 text-danger"><i class="bx bxs-message-square-x"></i>非必填</span>
                                    @endif
                                    
                                </td>
                                <td>
                                    {{-- 單選 --}}
                                    @if ($value->single==1) 
                                    @foreach($value->opetion as $key2=>$value2)
                                    <div class="form-check">
                                        <input class="form-check-input" name="single" type="radio" id="flexRadioDisabled">
                                        <label class="form-check-label" for="flexRadioDisabled">{{$value2->custom_option_title}} - $ {{$value2->price}} </label>
                                    </div>
                                    @endforeach    
                                    @else
                                    {{-- 多選 --}}
                                    @foreach($value->opetion as $key2=>$value2)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                        <label class="form-check-label" for="flexCheckDefault">{{$value2->custom_option_title}} - $ {{$value2->price}}</label>
                                    </div>
                                    @endforeach 
                                    @endif
                                </td>
                     
                                <td>
                                    <div class="d-flex order-actions">
                                        <a href="{{route('spec_edit',['customCateId'=>$value->id])}}" class=""><i class="bx bxs-edit"></i></a>
                                        <a href="javascript:;" class="ms-3"><i class="bx bxs-trash" onclick="javascript:if(confirm('確定要刪除嗎?'))location='{{route('spec_delete',['id'=>$value->id])}}'"></i></a>
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


@endsection
@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}">
</script>
<script>
    $(document).ready(function () {
        $('#example').DataTable();
    });

</script>
@endsection
