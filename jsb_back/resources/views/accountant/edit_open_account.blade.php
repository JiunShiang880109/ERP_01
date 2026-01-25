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
                    <li class="breadcrumb-item active" aria-current="page">期初微調設定</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    {{--  --}}
    <div class="col-lg-12">
        <div class="card alert">
            <div class="table-responsive invoice_list">
                <form method="POST" action="{{route('accountant.update_open_account')}}" >
                    @csrf
                    <input type="hidden" name="employeeId" class="form-control" value="{{session('employeeId')}}" readonly>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <label class="mb-0 fw-bold">會計年度</label>
                        <select  name="fiscal_year" class="form-select w-auto" disabled>
                            <option>{{$fiscalYear}}</option>
                        </select>
                        <input type="hidden" id="fiscal_year" name="fiscal_year" value="{{$fiscalYear}}">

                        <label class="fw-bold mb-0">期初月份</label>
                        <select  name="fiscal_month" class="form-select w-auto" disabled>
                            <option>{{$fiscalMonth}}</option>
                        </select>
                        <input type="hidden" id="fiscal_month" name="fiscal_month" value="{{$fiscalMonth}}">
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
                                        @php
                                            $itemKey = "{$item->main_code}-{$item->sub_code}-{$item->code}-0";
                                            $openingItem = $openingBalance[$itemKey] ?? null;
                                        @endphp
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
                                            @if(!$openingItem)
                                                {{-- 無 ledger，但也沒有期初帳：顯示提示列，不送出 items --}}
                                                <tr class="text-muted">
                                                    <td>-</td>
                                                    <td>{{ $item->main_code }}{{ $item->sub_code }}{{ str_pad($item->code, 2, '0', STR_PAD_LEFT) }}</td>
                                                    <td>{{ $item->name }}</td>
                                                    <td>{{ $sub->name ?? '-' }}</td>
                                                    <td colspan="4">未建立期初帳</td>
                                                    <td>-</td>
                                                </tr>
                                            @else
                                                {{-- 無 ledger，有期初帳：允許調整 is_offset / offset_start_date --}}
                                                <tr>
                                                    <td>-</td>
                                                    <td>
                                                        {{ $item->main_code }}{{ $item->sub_code }}{{ str_pad($item->code, 2, '0', STR_PAD_LEFT) }}

                                                        <input type="hidden" name="items[{{ $i }}][main_code]" value="{{ $openingItem->main_code }}">
                                                        <input type="hidden" name="items[{{ $i }}][sub_code]" value="{{ $openingItem->sub_code }}">
                                                        <input type="hidden" name="items[{{ $i }}][item_code]" value="{{ $openingItem->item_code }}">
                                                        {{-- 無 ledger：送空字串，後端轉 null --}}
                                                        <input type="hidden" name="items[{{ $i }}][ledger_code]" value="">
                                                    </td>
                                                    <td>{{ $item->name }}</td>
                                                    <td>{{ $sub->name ?? '-' }}</td>

                                                    <td>
                                                        {{ $openingItem->dc }}
                                                        <input type="hidden" name="items[{{ $i }}][dc]" value="{{ $openingItem->dc }}">
                                                    </td>
                                                    <td>
                                                        {{ number_format($openingItem->opening_amount, 2) }}
                                                        <input type="hidden" name="items[{{ $i }}][opening_amount]" value="{{ $openingItem->opening_amount }}">
                                                    </td>

                                                    <td>
                                                        <select name="items[{{ $i }}][is_offset]" class="form-select form-select-sm">
                                                            <option value="0" {{ $openingItem->is_offset ? '' : 'selected' }}>否</option>
                                                            <option value="1" {{ $openingItem->is_offset ? 'selected' : '' }}>是</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="date"
                                                            name="items[{{ $i }}][offset_start_date]"
                                                            class="form-control form-control-sm offset-date"
                                                            value="{{ optional($openingItem->offset_start_date)->format('Y-m-d') }}">
                                                    </td>
                                                    <td>{{ $openingItem->employeeId }}</td>
                                                </tr>

                                                @php $i++; @endphp
                                            @endif
                                        @endif
                                        {{-- 子科目（ledger） --}}
                                        @foreach ($item->accountLedgers as $ledger)
                                            @php
                                                $key = "{$ledger->main_code}-{$ledger->sub_code}-{$ledger->item_code}-{$ledger->code}";
                                                $opening = $openingBalance[$key] ?? null;
                                            @endphp
                                            @if(!$opening)
                                                <tr class="text-muted">
                                                    <td>-</td>
                                                    <td>{{ $ledger->main_code }}{{ $ledger->sub_code }}{{ str_pad($ledger->item_code, 2, '0', STR_PAD_LEFT) }}.{{$ledger->code}}</td>
                                                    <td>{{ $item->name }}-{{ $ledger->name }}</td>
                                                    <td>{{ $sub->name ?? '-' }}</td>
                                                    <td colspan="4">未建立期初帳</td>
                                                    <td>-</td>
                                                </tr>
                                                @continue
                                            @endif
                                            <tr>
                                                <td>-</td>
                                                <td>
                                                    {{ $ledger->main_code }}{{ $ledger->sub_code }}{{ str_pad($ledger->item_code, 2, '0', STR_PAD_LEFT) }}.{{$ledger->code}}
                                                </td>
                                                <td>{{ $item->name }}-{{ $ledger->name }}</td>
                                                <td>{{ $sub->name ?? '-' }}</td>
                                                <td>
                                                    {{$opening->dc}}
                                                    <input type="hidden" name="items[{{ $i }}][dc]" value="{{$opening->dc}}">
                                                </td>
                                                <td>
                                                    {{number_format($opening->opening_amount, 2)}}
                                                    <input type="hidden" name="items[{{ $i }}][opening_amount]" value="{{$opening->opening_amount}}">
                                                </td>
                                                <td>
                                                    <select name="items[{{ $i }}][is_offset]" class="form-select form-select-sm">
                                                        <option value="0" {{$opening->is_offset ? '' : 'selected'}}>否</option>
                                                        <option value="1" {{$opening->is_offset? 'selected' : ''}}>是</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    
                                                    <input type="date"
                                                        name="items[{{ $i }}][offset_start_date]"
                                                        class="form-control form-control-sm offset-date"
                                                        value="{{ optional($opening->offset_start_date)->format('Y-m-d') }}">
                                                </td>

                                                <td>{{$opening->employeeId}}</td>
                                                <td style="display:none">
                                                    <input type="hidden" name="items[{{ $i }}][main_code]" value="{{ $opening->main_code }}">
                                                    <input type="hidden" name="items[{{ $i }}][sub_code]" value="{{ $opening->sub_code }}">
                                                    <input type="hidden" name="items[{{ $i }}][item_code]" value="{{ $opening->item_code }}">
                                                    <input type="hidden" name="items[{{ $i }}][ledger_code]" value="{{ $opening->ledger_code }}">
                                                </td>
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
                            儲存開帳微調
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
            info: false,
        });
    });
</script>
<script>
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
    $(document).ready(function(){
        updateDateRange();
    });
</script>

@endsection
