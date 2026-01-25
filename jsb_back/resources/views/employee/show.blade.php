@extends('layout')
<!-- 引用模板 -->
@section('head')
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />    
@endsection
@section('content')
    <div class="bg-white p-3">
        <div class="row p-2 justify-content-between align-items-center border-bottom">
             <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3 col-md-6">
                <div class="breadcrumb-title pe-3">供應商與員工</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item" aria-current="page">員工資料</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- -------------------------- -->
            <div class="col-md-2 col-12 text-end addProduct">
                <a href="{{route('employeeAdd')}}" class="btn btn-primary ">新增員工</a>
            </div>
        </div>
        {{--  --}}
        <div class="content-wrap mt-3">
            <div class="main">
                <div class="col-lg-12">
                    <div class="card alert">
                        <div class="table-responsive invoice_list">
                            <table id="example" style="text-align:center;"
                                class="center table table-hover js-basic-example dataTable table-custom spacing8">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">相片</th>
                                        <th scope="col">員工編號</th>
                                        <th scope="col">手機</th>
                                        {{-- <th scope="col">部門</th>
                                        <th scope="col">職稱</th>
                                        <th scope="col">班別</th> --}}
                                        <th scope="col">操作</th>
                                    </tr>
                                </thead>
                                <tbody style="vertical-align:middle;text-align: center;">
                                    @foreach ($result as $key=>$item)
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>
                                            <a href="{{route('employee_detail',['employeeId'=>$item->employeeId])}}">
                                            <img id="preview" src="@if($item->headImg==null) {{asset('assets/images/avatars/avatar-0.png')}} @else {{asset('assets/images/avatars/'.$item->headImg.'')}} @endif " class="rounded-circle p-1 border" width="65" height="65" alt="...">
                                            <h6 class="mt-2 font-14 text-primary">{{$item->name}}</h6></a>
                                        </td>
                                        <td>{{$item->employeeId}}</td>

                                        <td>{{$item->phone}}</td>
                                        {{-- <td></td>
                                        <td></td>
                                        <td></td> --}}
                                        <td>
                                                    <form class="col-12" action="{{route('employee_disable')}}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="employeeId" value="{{$item->employeeId}}">

                                                        @if($item->isResign==0) 
                                                            <input type="hidden" name="isResign" value="1">
                                                            <input type="submit" class="btn btn-primary" value="在職">
                                                        @else
                                                            <input type="hidden" name="isResign" value="0">
                                                            <input type="submit" class="btn btn-danger" value="離職">
                                                       @endif
                                                    </form>  
                                        </td>
                                    </tr>
                                    @endforeach  
                                </tbody>
                            </table>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
@section('script')
<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
<script>$(document).ready(function() {$('#example').DataTable();} );</script>
@endsection
