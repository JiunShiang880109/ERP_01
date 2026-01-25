@extends('layout')
<!-- 引用模板 -->
@section('head')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}"
    rel="stylesheet" />
<style>
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
                    <li class="breadcrumb-item"><a href="{{url()->previous()}}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{route('accountant.category')}}">科目類別管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">期初開帳</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    {{--  --}}
    <div class="col-lg-12">
        <div class="card alert">
            <div class="table-responsive invoice_list">
                <form method="POST" action="{{route('accountant.store_open_account')}}" >
                    @csrf
                    <input type="hidden" name="employeeId" class="form-control" value="{{session('employeeId')}}" readonly>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <label class="mb-0 fw-bold">會計年度</label>
                        <select id="fiscal_year" name="fiscal_year" class="form-select w-auto">
                            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ (int)old('fiscal_year', now()->year) === (int)$y ? 'selected' : '' }}>
                                {{ $y }}
                                </option>
                            @endfor
                        </select>

                        <label class="fw-bold mb-0">期初月份</label>
                        <select id="fiscal_month" name="fiscal_month" class="form-select w-auto">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ (int)old('fiscal_month', now()->month) === (int)$m ? 'selected' : '' }}>
                                {{ $m }} 月
                                </option>
                            @endfor
                        </select>
                    </div>
                    <table class="table table-hover" id="openAccount">
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
                            @php $i = 0; @endphp

                            @foreach ($categories as $main)
                                @foreach ($main->subCates as $sub)
                                    @foreach ($sub->accountItems as $item)
                                        @php $hasLedger = $item->accountLedgers->count()>0; @endphp
                                        @if($hasLedger)
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
                                        @else
                                                <tr>
                                                <td>-</td>
                                                <td>
                                                    {{ $item->main_code }}{{ $item->sub_code }}{{ str_pad($item->code, 2, '0', STR_PAD_LEFT) }}

                                                    <input type="hidden" name="items[{{ $i }}][main_code]" value="{{ $item->main_code }}">
                                                    <input type="hidden" name="items[{{ $i }}][sub_code]" value="{{ $item->sub_code }}">
                                                    <input type="hidden" name="items[{{ $i }}][item_code]" value="{{ $item->code }}">
                                                    {{-- 沒有 ledger → 送空字串（後端要轉成 null） --}}
                                                    <input type="hidden" name="items[{{ $i }}][ledger_code]" value="">
                                                </td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $sub->name ?? '-' }}</td>
                                                <td>
                                                    <select name="items[{{ $i }}][dc]" class="form-select form-select-sm">
                                                        <option value="借" {{ old("items.$i.dc") === '借' ? 'selected' : '' }}>借</option>
                                                        <option value="貸" {{ old("items.$i.dc") === '貸' ? 'selected' : '' }}>貸</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number"
                                                        name="items[{{ $i }}][opening_amount]"
                                                        class="form-control form-control-sm"
                                                        step="0.01"
                                                        value="{{ old("items.$i.opening_amount", 0) }}">
                                                </td>
                                                <td>
                                                    <select name="items[{{ $i }}][is_offset]" class="form-select form-select-sm">
                                                        <option value="0" {{ old("items.$i.is_offset") === '0' ? 'selected' : '' }}>否</option>
                                                        <option value="1" {{ old("items.$i.is_offset") === '1' ? 'selected' : '' }}>是</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="date"
                                                        name="items[{{ $i }}][offset_start_date]"
                                                        class="form-control form-control-sm offset-date"
                                                        value="{{ old("items.$i.offset_start_date") }}">
                                                </td>
                                                <td>{{ session('employeeId') }}</td>
                                            </tr>

                                            @php $i++; @endphp
                                        @endif

                                        {{-- 子科目（ledger） --}}
                                        @foreach ($item->accountLedgers as $ledger)
                                            <tr>
                                                <td>-</td>
                                                <td>
                                                    {{ $ledger->main_code }}{{ $ledger->sub_code }}{{ str_pad($ledger->item_code, 2, '0', STR_PAD_LEFT) }}.{{$ledger->code}}
                                                    
                                                    <input type="hidden" name="items[{{ $i }}][main_code]" value="{{$ledger->main_code}}">
                                                    <input type="hidden" name="items[{{ $i }}][sub_code]" value="{{$ledger->sub_code}}">
                                                    <input type="hidden" name="items[{{ $i }}][item_code]" value="{{$ledger->item_code}}">
                                                    <input type="hidden" name="items[{{ $i }}][ledger_code]" value="{{$ledger->code}}">
                                                </td>
                                                <td>{{ $item->name }}-{{ $ledger->name }}- 
                                                        @if ($ledger->enable)
                                                            <span class="text-success">啟用</span>
                                                        @else
                                                            <span class="text-danger">停用</span>
                                                        @endif
                                                </td>
                                                <td>{{ $sub->name ?? '-' }}</td>
                                                <td>
                                                    <select name="items[{{ $i }}][dc]" class="form-select form-select-sm">
                                                            <option value="借" {{ old("items.$i.dc") === '借' ? 'selected' : '' }}>借</option>
                                                            <option value="貸" {{ old("items.$i.dc") === '貸' ? 'selected' : '' }}>貸</option>
                                                    </select> 
                                                </td>
                                                <td>
                                                    <input type="number"
                                                        name="items[{{ $i }}][opening_amount]"
                                                        class="form-control form-control-sm"
                                                        step="0.01"
                                                        value="{{ old("items.$i.opening_amount", 0) }}">         
                                                </td>
                                                <td>
                                                    <select name="items[{{ $i }}][is_offset]" class="form-select form-select-sm">
                                                        <option value="0" {{ old("items.$i.is_offset") === '0' ? 'selected' : '' }}>否</option>
                                                        <option value="1" {{ old("items.$i.is_offset") === '1' ? 'selected' : '' }}>是</option>
                                                    </select> 
                                                </td>
                                                <td>
                                                    <input type="date"
                                                        name="items[{{ $i }}][offset_start_date]"
                                                        class="form-control form-control-sm offset-date"
                                                        value="{{ old("items.$i.offset_start_date") }}">
                                                </td>
                                                <td>{{ session('employeeId') }}</td>
                                            </tr>
                                            @php $i++; @endphp
                                        @endforeach
                                    @endforeach
                                @endforeach
                            @endforeach
     
                        </tbody>
                    
                    </table>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            儲存期初開帳
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


</div>

@if ($errors->any())
        <div id="flashMsg1"
            class="alert alert-danger" 
            style="position: fixed; top:20px; left:50%; transform:translateX(-50%); z-index:9999;">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>

        <script>
            setTimeout(()=> document.getElementById('flashMsg1')?.remove(), 10000);
        </script>
    @endif

    @if(session('success') || session('error'))
        <div id="flashMsg"
            class="alert {{ session('success') ? 'alert-success' : 'alert-danger' }}"
            style="position: fixed; top:20px; left:50%; transform:translateX(-50%); z-index:9999;">
            {{ session('success') ?? session('error') }}
        </div>

        <script>
            setTimeout(()=> document.getElementById('flashMsg')?.remove(), 10000);
        </script>
    @endif

@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function () {
        $('#openAccount').DataTable({
            paging: false,
            searching: false,
            ordering: false,
            info: false
        });

    });
</script>
<script>
    //年月份設定
    //年月份設定
    function updateDateRange(){
        const year = $('#fiscal_year').val();
        if(!year) return;

        const start = `${year}-01-01`;
        const end   = `${year}-12-31`;

        $('.offset-date').each(function(){
            this.min = start;
            this.max = end;

            if(this.value && (this.value < start || this.value > end)){
                this.value = '';
            }
        });
    }
</script>
<script>
    $(document).on('change', '#fiscal_year, #fiscal_month', function(){
        updateDateRange();
    });
</script>

@endsection
