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
        <div class="breadcrumb-title pe-3">支出管理</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">支出類別</li>
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
                    data-bs-target="#AddUnit">新增類別</button>
                </div>
            </div>
        </div>
    </div>
    {{-- add彈窗 --}}
    <!-- target需要互相對應 -->
    <div class="modal fade" id="AddUnit" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">新增類別</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('expenses.category_add') }}" method="POST">
                    @csrf
                    {{--  --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">類別名稱</h3>
                        <div class="col-md-10 col-12">
                            <input name="name" class="form-control " type="text" placeholder="ex:薪資、水電、其他...">
                        </div>
                    </div>
                    {{-- 排序 --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">排序</h3>
                        <div class="col-md-10 col-12">
                            <input name="sort" class="form-control " type="text" placeholder="ex:1~10 由小至大排序">
                        </div>
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
                            <td>排序</td>
                            <td>類別</td>
                            <td>操作</td>
                        </tr>
                    </thead>
                    <tbody style="vertical-align:middle;">
                        @foreach($categoryMain as $c)
                            <tr>
                                <td>{{ $c->sort }}</td>
                                <td>{{ $c->name }}</td>  
                                <td>
                                    <div class="d-flex order-actions">
                                        <button type="button" class="btn btn-link p-0 text-primary editBtn"
                                         data-bs-toggle="modal"
                                         data-bs-target="#editUnit"
                                         data-id="{{$c->id}}"
                                         data-sort="{{$c->sort}}"
                                         data-name="{{$c->name}}">
                                            <i class="bx bxs-edit"></i>
                                        </button>
                                        
                                        <form action="{{ route('expenses.category_delete', $c->id) }}" method="POST" onsubmit="return confirm('確定要刪除該筆資料嗎?');" class="m-0">
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

    {{-- edit彈窗 --}}
    <!-- target需要互相對應 -->
    <div class="modal fade" id="editUnit" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">修改類別</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    {{--  --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">類別名稱</h3>
                        <div class="col-md-10 col-12">
                            <input id="edit_name" name="name" class="form-control " type="text" placeholder="ex:薪資、水電、其他...">
                        </div>
                    </div>
                    {{-- 排序 --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">排序</h3>
                        <div class="col-md-10 col-12">
                            <input id="edit_sort" name="sort" class="form-control " type="text" placeholder="ex:1~10 由小至大排序">
                        </div>
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

</div>

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

</script>

<!-- edit ctrl-->
<script>
$(document).on('click', '.editBtn', function () {

    let id = $(this).data('id');
    let name = $(this).data('name');
    let sort = $(this).data('sort');

    // 填入彈窗欄位
    $('#edit_name').val(name);
    $('#edit_sort').val(sort);

    // 設定 form 的 action
    $('#editForm').attr('action', '/expenses/category/' + id + '/update');
});
</script>
@endsection
