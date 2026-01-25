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
        <div class="breadcrumb-title pe-3">傳票管理</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">科目類別管理</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-12">
            <div class="d-flex gap-2">

                <button type="button"
                        class="btn btn-primary addProduct"
                        data-bs-toggle="modal"
                        data-bs-target="#AddUnit">
                    新增子科目
                </button>

                <a href="{{route('accountant.open_account')}}"
                class="btn btn-primary">
                    期初開帳設定
                </a>
                <!-- <a href="{{route('accountant.edit_open_account')}}"
                class="btn btn-primary">
                    期初微調設定
                </a> -->
                <a href="{{route('accountant.close_account')}}"
                class="btn btn-primary">
                    關帳設定
                </a>
            </div>
        </div>
    </div>


    {{-- add彈窗 --}}
    <!-- target需要互相對應 -->
    <div class="modal fade" id="AddUnit" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">新增子科目</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('accountant.category_add') }}" method="POST">
                    @csrf
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">員工編號</h3>
                        <div class="col-md-10 col-12">
                            <input type="text" name="employeeId" class="form-control" value="{{session('employeeId')}}" readonly>
                        </div>
                    </div>
                    {{-- 主類別 --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">主類別</h3>
                        <div class="col-md-10 col-12">
                            <select class="form-select select2 ing-select" id="mainSelect" name="main_code" required>
                                <option value="">選擇主類別</option>
                                    @foreach ($categories as $main)
                                        <option value="{{ $main->code }}">{{ $main->name }}</option>
                                    @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- 子類別 --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">子類別</h3>
                        <div class="col-md-10 col-12">
                            <select class="form-select select2 ing-select" id="subSelect" name="sub_code" required>
                                <option value="">選擇子類別</option>
                                    @foreach ($categories as $main)
                                        @foreach ($main->subCates as $sub)
                                            <option value="{{ $main->code }}-{{ $sub->code }}" 
                                            data-main="{{$main->code}}">
                                                {{$main->code}}{{$sub->code}}-{{ $sub->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- 主科目 --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">主科目</h3>
                        <div class="col-md-10 col-12">
                            <select class="form-select select2 ing-select" id="itemSelect" name="item_code" required>
                                <option value="">選擇主科目</option>
                                    @foreach ($categories as $main)
                                        @foreach ($main->subCates as $sub)
                                            @foreach ($sub->accountItems as $item)
                                                <option
                                                value="{{ $item->main_code }}-{{ $item->sub_code }}-{{ $item->code }}"
                                                data-main="{{ $item->main_code }}"
                                                data-sub="{{ $item->sub_code }}"
                                                >
                                                    {{ $item->main_code }}{{ $item->sub_code }}{{ str_pad($item->code, 2, '0', STR_PAD_LEFT) }}                                 
                                                    - {{ $item->name }}
                                                </option>
                                            @endforeach 
                                        @endforeach
                                    @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- 子科目編號 --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">子科目編號</h3>
                        <div class="col-md-10 col-12">
                            <input name="code" class="form-control " type="text" placeholder="請填入編號1-4位數,ex:1、2、3...9999">
                        </div>
                    </div>
                    {{-- 子科目名稱 --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">子科目名稱</h3>
                        <div class="col-md-10 col-12">
                            <input name="name" class="form-control " type="text">
                        </div>
                    </div>
                    
                    {{-- --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">是否啟用科目</h3>
                        <div class="col-md-10 col-12">
                            <select class="form-select select2 ing-select" name="enable" required>
                                <option value="">選擇</option>
                                <option value="1">Y</option>
                                <option value="0">N</option>
                               
                            </select>
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
                <table class="table table-hover" id="accountCate">
                    <thead>
                        <tr>
                            <td>操作</td>
                            <td>科目編號</td>
                            <td>科目名稱</td>
                            <td>科目類別</td>
                            <td>借/貸</td>
                            <td>期初金額</td>
                            <td>是否立沖</td>
                            <td>立沖起始日</td>
                            <td>員工編號</td>
                        </tr>
                    </thead>
                    <tbody style="vertical-align:middle;">

                        @foreach ($categories as $main)
                            @foreach ($main->subCates as $sub)
                                @foreach ($sub->accountItems as $item)

                                    {{-- 主科目 --}}
                                    <tr class="table-secondary">
                                        <td></td>
                                        <td>{{ $item->main_code }}{{ $item->sub_code }}{{ str_pad($item->code, 2, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $sub->name ?? '-' }}</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                    </tr>

                                    {{-- 子科目（ledger） --}}
                                    @foreach ($item->accountLedgers as $ledger)
                                        @php
                                            $key = "{$ledger->main_code}-{$ledger->sub_code}-{$ledger->item_code}-{$ledger->code}";
                                            $opening = $openingBalance[$key] ?? null;
                                        @endphp
                                        <tr>
                                            <td>
                                                <button type="button" class="btn btn-link p-0 text-primary editBtn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editUnit"
                                                data-main_code="{{$ledger->main_code}}"
                                                data-sub_code="{{$ledger->sub_code}}"
                                                data-item_code="{{$ledger->item_code}}"
                                                data-code="{{$ledger->code}}"
                                                data-name="{{$ledger->name}}"
                                                data-enable="{{$ledger->enable}}">
                                                    <i class="bx bxs-edit"></i>
                                                </button>
                                                <form method="POST" action="{{ route('accountant.category_delete') }}">
                                                    @csrf
                                                    @method('DELETE')

                                                    <input type="hidden" name="main_code" value="{{ $ledger->main_code }}">
                                                    <input type="hidden" name="sub_code" value="{{ $ledger->sub_code }}">
                                                    <input type="hidden" name="item_code" value="{{ $ledger->item_code }}">
                                                    <input type="hidden" name="code" value="{{ $ledger->code }}">

                                                    <button type="submit" class="btn btn-link p-0 text-danger"
                                                        onclick="return confirm('確定要刪除此子科目？')">
                                                        <i class="bx bxs-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                {{ $ledger->main_code }}{{ $ledger->sub_code }}{{ str_pad($ledger->item_code, 2, '0', STR_PAD_LEFT) }}.{{$ledger->code}}
                                                
                                            </td>
                                            <td>{{ $item->name }}-{{ $ledger->name }}- 
                                                    @if ($ledger->enable)
                                                        <span class="text-success">啟用</span>
                                                    @else
                                                        <span class="text-danger">停用</span>
                                                    @endif
                                            </td>
                                            <td>{{ $sub->name ?? '-' }}</td>
                                            <td>{{ $opening->dc ?? '-' }}</td>
                                            <td>{{ $opening->opening_amount ?? '-' }}</td>
                                            <td>
                                                @if(isset($opening))
                                                    {{ $opening->is_offset ? '是' : '否' }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $opening->offset_start_date ?? '-' }}</td>
                                            <td>{{$ledger->employeeId}}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endforeach
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
                <form id="editForm" action="{{route('accountant.category_update')}}" method="POST">
                    @csrf
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">員工編號</h3>
                        <div class="col-md-10 col-12">
                            <input type="text" name="employeeId" class="form-control" value="{{session('employeeId')}}" readonly>
                        </div>
                    </div>
                    {{-- 主類別 --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">主類別</h3>
                        <div class="col-md-10 col-12">
                            <select class="form-select select2 ing-select" id="mainEditSelect" name="main_code" required disabled>
                                <option value="">選擇主類別</option>
                                    @foreach ($categories as $main)
                                        <option value="{{ $main->code }}">{{ $main->name }}</option>
                                    @endforeach
                            </select>
                            <input type="hidden" name="main_code" id="mainEditHidden">
                        </div>
                    </div>
                    {{-- 子類別 --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">子類別</h3>
                        <div class="col-md-10 col-12">
                            <select class="form-select select2 ing-select" id="subEditSelect" name="sub_code" required disabled>
                                <option value="">選擇子類別</option>
                                    @foreach ($categories as $main)
                                        @foreach ($main->subCates as $sub)
                                            <option
                                            value="{{ $sub->main_code }}-{{ $sub->code }}"
                                            data-main="{{ $sub->main_code }}"
                                            >
                                            {{ $sub->main_code }}{{ $sub->code }} - {{ $sub->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                            </select>
                            <input type="hidden" name="sub_code" id="subEditHidden">
                        </div>
                    </div>
                    {{-- 主科目 --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">主科目</h3>
                        <div class="col-md-10 col-12">
                            <select class="form-select select2 ing-select" id="itemEditSelect" name="item_code" required disabled>
                                <option value="">選擇主科目</option>
                                    @foreach ($categories as $main)
                                        @foreach ($main->subCates as $sub)
                                            @foreach ($sub->accountItems as $item)
                                                <option
                                                value="{{ $item->main_code }}-{{ $item->sub_code }}-{{ $item->code }}"
                                                data-main="{{ $item->main_code }}"
                                                data-sub="{{ $item->sub_code }}"
                                                >
                                                    {{ $item->main_code }}{{ $item->sub_code }}{{ str_pad($item->code, 2, '0', STR_PAD_LEFT) }}
                                                    - {{ $item->name }}
                                                </option>
                                            @endforeach 
                                        @endforeach
                                    @endforeach
                            </select>
                            <input type="hidden" name="item_code" id="itemEditHidden">
                        </div>
                    </div>
                    {{-- 子科目編號 --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">子科目編號</h3>
                        <div class="col-md-10 col-12">
                            <input id="ledgerEditCode" name="code" class="form-control " type="text" placeholder="請填入編號1-4位數,ex:1、2、3...9999" readonly>
                        </div>
                    </div>
                    {{-- 子科目名稱 --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">子科目名稱</h3>
                        <div class="col-md-10 col-12">
                            <input id="nameEdit" name="name" class="form-control " type="text">
                        </div>
                    </div>
                    
                    {{-- --}}
                    <div class="m-4 row align-items-center g-0">
                        <h3 class="col-md-2 col-12 fs-6 text-center text-start m-md-0 mb-1">是否啟用科目</h3>
                        <div class="col-md-10 col-12">
                            <select class="form-select select2 ing-select" id="enableEdit" name="enable" required>
                                <option value="">選擇</option>
                                <option value="1">Y</option>
                                <option value="0">N</option>
                               
                            </select>
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
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>

<script>
    $(document).ready(function () {
        $('#example').DataTable();
    });

</script>

<script>
    function initCategoryCascade($main, $sub, $item) {

    const subOptions  = $sub.find('option[data-main]').clone();
    const itemOptions = $item.find('option[data-main]').clone();

    // 主類別 → 子類別
    $main.on('change', function () {

        const mainCode = this.value;

        $sub.html('<option value="">選擇子類別</option>');
        $item.html('<option value="">選擇主科目</option>');

        if (!mainCode) return;

        subOptions
            .filter(function () {
                return $(this).data('main') == mainCode;
            })
            .each(function () {
                $sub.append($(this).clone());
            });

        $sub.trigger('change.select2');
    });

    // 子類別 → 主科目
    $sub.on('change', function () {

        const mainCode = $main.val();
        const subKey   = this.value;

        $item.html('<option value="">選擇主科目</option>');

        if (!mainCode || !subKey) return;

        itemOptions
            .filter(function () {
                return $(this).data('main') == mainCode
                    && `${$(this).data('main')}-${$(this).data('sub')}` == subKey;
            })
            .each(function () {
                $item.append($(this).clone());
            });

        $item.trigger('change.select2');
    });
    
}

</script>

<!-- edit ctrl-->
<script>
$(document).on('click', '.editBtn', function () {

    const data = $(this).data();

    const mainCode = data.main_code;
    const subKey = `${data.main_code}-${data.sub_code}`;
    const itemKey  = `${data.main_code}-${data.sub_code}-${data.item_code}`;

    // 主類別
    $('#mainEditSelect')
        .val(mainCode)
        .trigger('change');
    $('#mainEditHidden').val(mainCode);
    // 子類別
    setTimeout(() => {
        $('#subEditSelect')
            .val(subKey)
            .trigger('change');
        $('#subEditHidden').val(subKey);
        // 主科目（用組合 key）
        setTimeout(() => {
            $('#itemEditSelect')
                .val(itemKey)
                .trigger('change');

            $('#itemEditHidden').val(itemKey);
        }, 1);

    }, 1);

    $('#ledgerEditCode').val(data.code);
    $('#nameEdit').val(data.name);
    $('#enableEdit').val(data.enable).trigger('change');
});
</script>

<script>
$(function () {
    initCategoryCascade(
        $('#mainSelect'),
        $('#subSelect'),
        $('#itemSelect')
        
    );

    initCategoryCascade(
        $('#mainEditSelect'),
        $('#subEditSelect'),
        $('#itemEditSelect')
    );
});
</script>

<script>
$(document).ready(function () {
    $('#accountCate').DataTable({
        //不使用預設排序
        order: [],

        // 指定欄位不能排序
        columnDefs: [
            {
                targets: 0,      // 第 0 欄
                orderable: false
            }
        ],

        //保留原本功能
        paging: true,
        searching: true,
        info: true
    });
});
</script>

@endsection
