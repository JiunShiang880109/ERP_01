@extends('layout')
<!-- 引用模板 -->
@section('head')
@endsection
@section('content')
    <div class="bg-white p-3">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">商品資料</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><a href="{{route('spec')}}">商品規格</a></li>
                        <li class="breadcrumb-item active" aria-current="page">規格編輯</li>
                    </ol>
                </nav>
            </div>
        </div>
        <hr />
       {{--  --}}
        <div class="content-wrap mt-3">
            <div class="main">
                <div class="card alert ">
                        <div class="card-body">
                            <form action="{{route('spec_update')}}" method="POST">
                                @csrf
                                {{-- --}}
                                <div class="m-4 row align-items-center g-0">
                                    <label for="inputFirstName" class="form-label">類別</label>
                                    <div class="col-md-10 col-12">
                                        <select class="form-select" name="cateId">
                                            @foreach ($cate as $item)
                                                <option value="{{$item->id}}" @if($item->id==$spec[0]->cateId)selected @endif>{{$item->category_title}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{--  --}}

                                <input name="customCateId" class="form-control " type="hidden" value="{{$spec[0]->customCateId}}">
                                   
                                <div class="m-4 row align-items-center g-0">
                                    <label for="inputFirstName" class="form-label">規格</label>
                                    <div class="col-md-10 col-12">
                                        <input name="customCateTitle" class="form-control " type="text" value="{{$spec[0]->customCateTitle}}">
                                    </div>
                                </div>
                                {{-- --}}
                                <div class="m-4 row align-items-center g-0">
                                    <label for="inputFirstName" class="form-label">必填</label>
                                    <div class="col-md-10 col-12">
                                        <label class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input" value="1" name="require" @if($spec[0]->require==1)checked @endif>
                                            <div class="form-check-label">是</div>
                                        </label>
                                        <label class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input" value="0" name="require" @if($spec[0]->require==0)checked @endif>
                                            <div class="form-check-label">否</div>
                                        </label>
                                    </div>
                                </div>
                                {{-- --}}
                                <div class="m-4 row align-items-center g-0">
                                    <label for="inputFirstName" class="form-label">類別</label>
                                    <div class="col-md-10 col-12">
                                        <label class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input" value="1" name="single" @if($spec[0]->single==1)checked @endif>
                                            <div class="form-check-label">單選</div>
                                        </label>
                                        <label class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input" value="0" name="single" @if($spec[0]->single==0)checked @endif>
                                            <div class="form-check-label">多選</div>
                                        </label>
                                    </div>
                                </div>
                                 {{-- --}}
                                 <div class="modal-footer">
                                    <button type="reset" class="btn btn-secondary" id="cancel" data-bs-dismiss="modal">取消</button>
                                    <input type="submit" class="btn btn-danger" id="send" value="更新">
                                </div>
                                </form>
                                {{--  --}}
                        </div>
                </div>
                {{--  --}}
                <div class="card alert ">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="row align-items-center">
                                    <div class="col-lg-3 col-xl-2 m-b-10">
                                        <button type="button" class="btn btn-primary addProduct" data-bs-toggle="modal"
                                        data-bs-target="#AddUnit">新增項目</button>
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
                                        <h5 class="modal-title" id="exampleModalLabel">新增項目</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{route('spec_option_insert')}}" method="POST">
                                        @csrf

                                        {{--  --}}
                                        <div class="m-4 row align-items-center g-0">
                                            {{--  --}}
                                            <div class="row">
                                                <div class="col-md-6 col-6">
                                                    <input name="customCateId" class="form-control " type="hidden" value="{{$spec[0]->customCateId}}">
                                                    <label for="spec1" class="form-label">選單內容1..</label>
                                                    <input name="custom_option_title" class="form-control " type="text" id="spec1" placeholder="ex:加料、無糖">
                                                </div>
                                                <div class="col-md-3 col-3">
                                                    <label for="spec1" class="form-label">選單1價格</label>
                                                    <input name="price" class="form-control " type="tel" placeholder="免費不填">
                                                </div>
                                                <div class="col-md-3 col-3">
                                                    <label for="sort" class="form-label">排序</label>
                                                    <input name="sort" class="form-control " type="text" placeholder="由小至大排序">
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
                           
                            <div class="m-4 row align-items-center g-0">
                                @foreach ($spec as $key=>$value)
                                <form action="{{route('spec_option_update')}}" method="POST">
                                    @csrf
                                <div class="row mt-2">
                                    
                                    <input name="optionId" class="form-control " type="hidden" value="{{$value->id}}">
                                    <div class="col-md-4 col-7">
                                        <label for="spec1" class="form-label">選單內容{{$key+1}}..</label>
                                        <input name="custom_option_title" class="form-control " type="text" id="spec1" value="{{$value->custom_option_title}}">
                                    </div>
                                    <div class="col-md-3 col-3">
                                        <label for="spec1" class="form-label">選單{{$key+1}}價格</label>
                                        <input name="price" class="form-control " type="tel" value="{{$value->price}}">
                                    </div>
                                    <div class="col-md-3 col-3">
                                        <label for="spec1" class="form-label">排序</label>
                                        <input name="sort" class="form-control " type="tel" value="{{$value->sort}}">
                                    </div>
                                    <div class="col-md-2 col-2 mt-4">
                                    <input type="submit" class="btn btn-info" id="send" value="更新項目">
                                    </form>
                                    <button type="button" class="btn btn-danger"
                                        onclick="javascript:if(confirm('確定要刪除嗎?'))location='{{ route('spec_option_del',['optionId'=>$value->id]) }}'">刪除</button>
                                    </div>
                                </div>
                                
                                @endforeach
                            </div>
                            
                           
                       
                        </div>
                </div>
                {{--  --}}
            </div>
        </div>
    </div>
@endsection
