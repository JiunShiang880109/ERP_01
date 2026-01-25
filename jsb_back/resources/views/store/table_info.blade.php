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
            <div class="breadcrumb-title pe-3">桌邊點餐</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">桌號管理</li>
                    </ol>
                </nav>
            </div>
        </div>
        <hr />

        <div class="col-lg-12">
            <div class="card alert">
                <div class="table-responsive invoice_list">

                    <div class="text-end">
                        <button class="btn btn-primary fw-bolder" data-bs-toggle="modal"
                            data-bs-target="#addTableInfoModal">新增桌號</button>
                        <!-- 新增Modal -->
                        <div class="modal fade" id="addTableInfoModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('add_table') }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">新增桌號</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h6 for="tableNumber" class="text-start">輸入桌號</h6>
                                            <input type="text" id="tableNumber" name="tableNumber" class="form-control">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">新增</button>
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">取消</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <BR>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-4">
                    @foreach ($table_info as $key => $table)
					<div class="col">
						<div class="card">
                        <div class="tableCode{{ $table->code }}"></div>
							<!-- <img src="assets/images/gallery/01.png" class="card-img-top" alt="..."> -->
							<div class="card-body">
								<h5 class="card-title">桌號：{{ $table->tableNumber }}</h5>
								<!-- <a href="javascript:;" class="btn btn-primary">Go somewhere</a> -->
                                
                                <button class="btn btn-sm m-1 btn-danger"  data-bs-toggle="modal"
                                        data-bs-target="#deleteTableInfoModal{{$table->code}}">刪除</button>
                                <!--  -->
                                 <!-- 刪除Modal -->
                                 <div class="modal fade" id="deleteTableInfoModal{{$table->code}}" tabindex="-1"
                                            aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form action="{{ route('delete_table') }}" method="POST">
                                                        @csrf
                                                        @method('delete')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLabel">刪除桌號</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <h5 for="tableNumber" class="text-center my-2">確定刪除桌號 {{$table->tableNumber}}?</h5>
                                                            <input type="text" name="code" hidden value="{{$table->code}}">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-danger">刪除</button>
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">取消</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                <!--  -->
							</div>
						</div>
					</div>
                    @endforeach
					<!-- <div class="col">
						<div class="card">
							<img src="assets/images/gallery/02.png" class="card-img-top" alt="...">
							<div class="card-body">
								<h5 class="card-title">Card title</h5>
								<p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>	<a href="javascript:;" class="btn btn-danger">Go somewhere</a>
							</div>
						</div>
					</div>
					<div class="col">
						<div class="card">
							<img src="assets/images/gallery/03.png" class="card-img-top" alt="...">
							<div class="card-body">
								<h5 class="card-title">Card title</h5>
								<p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>	<a href="javascript:;" class="btn btn-success">Go somewhere</a>
							</div>
						</div>
					</div>
					<div class="col">
						<div class="card">
							<img src="assets/images/gallery/04.png" class="card-img-top" alt="...">
							<div class="card-body">
								<h5 class="card-title">Card title</h5>
								<p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>	<a href="javascript:;" class="btn btn-warning">Go somewhere</a>
							</div>
						</div>
					</div> -->
				</div>
                    <!-- <table class="table table-hover" id="example">
                        <thead>
                            <tr>
                                <td width="25%" class="text-center">#</td>
                                <td width="25%" class="text-center">桌號</td>
                                <td width="25%" class="text-center">加密編碼</td>
                                <td width="25%" class="text-center">操作</td>
                            </tr>
                        </thead>
                        <tbody style="vertical-align:middle;">
                            @foreach ($table_info as $key => $table)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td class="text-center">{{ $table->tableNumber }}</td>
                                    <td class="text-center"><div class="tableCode{{ $table->code }}"></div></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm m-1 btn-danger"  data-bs-toggle="modal"
                                        data-bs-target="#deleteTableInfoModal{{$table->code}}">刪除</button>
                                        
                                       
                                        <div class="modal fade" id="deleteTableInfoModal{{$table->code}}" tabindex="-1"
                                            aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form action="{{ route('delete_table') }}" method="POST">
                                                        @csrf
                                                        @method('delete')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLabel">刪除桌號</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <h5 for="tableNumber" class="text-center my-2">確定刪除桌號 {{$table->tableNumber}}?</h5>
                                                            <input type="text" name="code" hidden value="{{$table->code}}">
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-danger">刪除</button>
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">取消</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table> -->
                </div>
            </div>
        </div>

    </div>
    @if ($errors->has('RepeatError'))
        <script>
            alert("{{ $errors->first('RepeatError') }}")
        </script>
    @endif
@endsection
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
<script>
    @foreach ($table_info as $key => $table)
    jQuery(function(){
        var url =`${window.location.protocol}//${window.location.host}/${window.TABLE}/jsb/phone/index.html?storeId={{$table->storeId}}&seatId={{$table->code}}`;
        jQuery('.tableCode{{$table->code}}').qrcode(url);
                var canvas = document.querySelector(".tableCode{{$table->code}} canvas");
                var img = canvas.toDataURL("image/png");
                $(canvas).on('click', function() {
                    // Create an anchor, and set its href and download.
                    var dl = document.createElement('a');
                    dl.setAttribute('href', img);
                    dl.setAttribute('download', 'qrcode{{ $table->tableNumber }}.png');
                    dl.click();
                });
                

                })

    @endforeach
</script>
@endsection
