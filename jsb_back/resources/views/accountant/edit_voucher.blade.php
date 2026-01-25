
@extends('layout')
<!-- 引用模板 -->
@section('head')
@endsection
@section('content')
    <div class="bg-white p-3">
        <div class="row p-2 justify-content-between align-items-center border-bottom">
            <!--breadcrumb-->
           <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3 col-md-6">
               <div class="breadcrumb-title pe-3">傳票管理</div>
               <div class="ps-3">
                   <nav aria-label="breadcrumb">
                       <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url()->previous()}}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{route('accountant.voucher')}}">傳票登錄作業</a></li>
                            <li class="breadcrumb-item active" aria-current="page">修改傳票</li>
                       </ol>
                   </nav>
               </div>
           </div>
           
       </div>
       {{--  --}}
        <div class="content-wrap mt-3">
            <div class="main">
                <div class="card alert ">
                        <div class="card-body">
                            {{--{{dd(session()->all())}}--}}
                            <form action="{{route('accountant.update_voucher', $voucher->id)}}" method="POST">
                                @csrf

                                {{-- 傳票主檔 --}}
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">員工編號</label>
                                        <input type="text" name="employeeId"
                                            class="form-control"
                                            value="{{ session('employeeId') }}" readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">傳票日期</label>
                                        <input type="date" name="voucher_date" 
                                        class="form-control"
                                        value="{{old('voucher_date', optional($voucher->voucher_date)->format('Y-m-d'))}}" readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">傳票類別</label>
                                        <select class="form-select" name="voucher_type" disabled>
                                            <option value="0"
                                                {{ old('voucher_type', $voucher->voucher_type) == 0 ? 'selected' : '' }}>
                                                現金收入
                                            </option>
                                            <option value="1"
                                                {{ old('voucher_type', $voucher->voucher_type) == 1 ? 'selected' : '' }}>
                                                現金支出
                                            </option>
                                            <option value="2"
                                                {{ old('voucher_type', $voucher->voucher_type) == 2 ? 'selected' : '' }}>
                                                轉帳
                                            </option>
                                        </select>
                                        <input type="hidden" name="voucher_type" value="{{ $voucher->voucher_type }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">傳票性質</label>
                                        <select class="form-select" name="voucher_kind" disabled>
                                            <option value="0" 
                                                {{old('voucher_kind', $voucher->voucher_kind == 0 ? 'selected' : '')}}>
                                                一般
                                            </option>
                                            <option value="1"
                                                {{old('voucher_kind', $voucher->voucher_kind == 1 ? 'selected' : '')}}>
                                                調整
                                            </option>
                                        </select>
                                        <input type="hidden" name="voucher_kind" value="{{ $voucher->voucher_kind }}">
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">傳票摘要</label>
                                        <input type="text" name="note" class="form-control"
                                                value="{{ old('note', $voucher->note) }}">
                                    </div>
                                </div>
                                <hr>
                                <h6>分錄明細</h6>

                                <div id="items-wrapper">
                                @foreach($voucher->items as $i => $row)
                                    <div class="row g-3 item-row">

                                        {{-- 借 / 貸 --}}
                                        <div class="col-md-2">
                                            <select class="form-select" name="items[{{ $i }}][dc]">
                                                <option value="借" {{ $row->dc === '借' ? 'selected' : '' }}>借</option>
                                                <option value="貸" {{ $row->dc === '貸' ? 'selected' : '' }}>貸</option>
                                            </select>
                                        </div>

                                        {{-- 科目 --}}
                                        <div class="col-md-4">
                                            <select class="form-select subject-select" name="items[{{ $i }}][subject_key]">

                                                @foreach(
                                                    $items->groupBy(fn($r) => $r->main_code.'-'.$r->sub_code.'-'.$r->code)
                                                    as $group
                                                )
                                                    @php
                                                        $item = $group->first();
                                                        $hasLedger = $group->whereNotNull('ledger_code')->count() > 0;

                                                        // 是否為目前這筆分錄的 item
                                                        $isCurrentItem =
                                                            $row->main_code == $item->main_code &&
                                                            $row->sub_code  == $item->sub_code &&
                                                            $row->item_code == $item->code;
                                                    @endphp

                                                    <optgroup label="{{ $item->main_code }}{{ $item->sub_code }}{{ $item->code }} {{ $item->name }}">

                                                        {{-- 沒有子科目 → 只能選主科目 --}}
                                                        @if(!$hasLedger)
                                                            <option value="{{ $item->main_code }}-{{ $item->sub_code }}-{{ $item->code }}"
                                                                data-main="{{ $item->main_code }}"
                                                                data-sub="{{ $item->sub_code }}"
                                                                data-item="{{ $item->code }}"
                                                                {{ $isCurrentItem ? 'selected' : '' }}>
                                                                {{ $item->name }}
                                                            </option>

                                                        @else

                                                            {{-- ledger_code = NULL，補一個主科目選項 --}}
                                                            <option value=""
                                                                data-main="{{ $item->main_code }}"
                                                                data-sub="{{ $item->sub_code }}"
                                                                data-item="{{ $item->code }}"
                                                                data-ledger=""
                                                                {{ $isCurrentItem && is_null($row->ledger_code) ? 'selected' : '' }}>
                                                                {{ $item->name }}
                                                            </option>

                                                            {{-- 子科目 --}}
                                                            @foreach($group as $opt)
                                                                @if($opt->ledger_code)
                                                                    <option value="{{ $opt->ledger_code }}"
                                                                        data-main="{{ $opt->main_code }}"
                                                                        data-sub="{{ $opt->sub_code }}"
                                                                        data-item="{{ $opt->code }}"
                                                                        data-ledger="{{$opt->ledger_code}}"
                                                                        {{ 
                                                                            $isCurrentItem &&
                                                                            $row->ledger_code == $opt->ledger_code
                                                                                ? 'selected'
                                                                                : ''
                                                                        }}>
                                                                        .{{ $opt->ledger_code }} {{ $opt->ledger_name }}
                                                                    </option>
                                                                @endif
                                                            @endforeach

                                                        @endif

                                                    </optgroup>
                                                @endforeach
                                            </select>

                                            {{-- hidden 欄位 --}}
                                            <input type="hidden" name="items[{{ $i }}][id]" value="{{ $row->id }}">
                                            <input type="hidden" name="items[{{ $i }}][main_code]" value="{{ $row->main_code }}">
                                            <input type="hidden" name="items[{{ $i }}][sub_code]" value="{{ $row->sub_code }}">
                                            <input type="hidden" name="items[{{ $i }}][item_code]" value="{{ $row->item_code }}">
                                            <input type="hidden" name="items[{{ $i }}][ledger_code]" value="{{ $row->ledger_code }}">
                                        </div>


                                        {{-- 金額 --}}
                                        <div class="col-md-2">
                                            <input type="number"
                                                name="items[{{ $i }}][amount]"
                                                class="form-control"
                                                value="{{ $row->amount }}">
                                        </div>

                                        {{-- 摘要 --}}
                                        <div class="col-md-12">
                                            <input type="text"
                                                name="items[{{ $i }}][note]"
                                                class="form-control"
                                                value="{{ $row->note }}">
                                        </div>

                                        <div class="col-md-12 d-flex justify-content-end gap-1 mt-2">
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm insert-item-row"
                                                title="在下方插入一筆">
                                                +插入
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-outline-danger btn-sm remove-item-row"
                                                title="刪除此筆">
                                                -刪除
                                            </button>
                                        </div>

                                    </div>
                                @endforeach
                                </div>

                                <hr>
                                <div class="row g-3 align-items-center bg-light p-3 rounded" id="voucher-summary">

                                    <div class="col-md-3">
                                        <label class="form-label">借方筆數</label>
                                        <input type="text" class="form-control text-end" id="debit-count" readonly value="0">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">借方金額</label>
                                        <input type="text" class="form-control text-end" id="debit-amount" readonly value="0.00">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">貸方筆數</label>
                                        <input type="text" class="form-control text-end" id="credit-count" readonly value="0">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">貸方金額</label>
                                        <input type="text" class="form-control text-end" id="credit-amount" readonly value="0.00">
                                    </div>

                                    <div class="col-md-12 mt-2">
                                        <span id="balance-status" class="fw-bold text-danger">
                                            借貸尚未平衡
                                        </span>
                                    </div>

                                </div>

                                <div class="mt-4 text-end">
                                    <div class="mt-4 text-end">
                                    <button type="button"
                                        id="save-temp-btn"
                                        class="btn btn-outline-secondary">
                                        儲存常用分錄
                                    </button>
                                    <button type="button" class="btn btn-secondary" 
                                        onclick="if(confirm('確定要取消修改此筆傳單？')){window.history.back();}">
                                        取消
                                    </button>
                                    <button type="submit" name="action" value="store" class="btn btn-danger">送出</button>
                                </div>

                            </form>

                        </div>
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
            setTimeout(()=> document.getElementById('flashMsg1')?.remove(), 5000);
        </script>
    @endif

    @if(session('success') || session('error'))
        <div id="flashMsg"
            class="alert {{ session('success') ? 'alert-success' : 'alert-danger' }}"
            style="position: fixed; top:20px; left:50%; transform:translateX(-50%); z-index:9999;">
            {{ session('success') ?? session('error') }}
        </div>

        <script>
            setTimeout(()=> document.getElementById('flashMsg')?.remove(), 5000);
        </script>
    @endif

@endsection
@section('script')
<script>
    //初始化科目選單
    function initSubjectSelect(context = document) {
        $(context).find('.subject-select').select2({
            placeholder: '輸入科目代碼或名稱',
            allowClear: true,
            width: '100%',
            matcher: function (params, data) {

                // 全顯示
                if ($.trim(params.term) === '') {
                    return data;
                }

                if (!data.text) {
                    return null;
                }

                const term = params.term.toLowerCase();
                const text = data.text.toLowerCase();

                // option
                if (text.indexOf(term) > -1) {
                    return data;
                }

                // optgroup label
                if (data.element && data.element.parentElement) {
                    const groupLabel =
                        data.element.parentElement.label?.toLowerCase() || '';

                    if (groupLabel.indexOf(term) > -1) {
                        return data;
                    }
                }

                return null;
            }
        });
    }
    //複製分錄
    function cloneCleanRow(index){
        const $template = $('#items-wrapper .item-row').first().clone();

        //移除殘留
        $template.find('.subject-select')
            .removeClass('select2-hidden-accessible')
            .removeAttr('data-select2-id')
            .next('.select2').remove();

        //重設欄位
        $template.find('input, select').each(function(){
            const name = $(this).attr('name');
            if(name){
                $(this).attr(
                    'name',
                    name.replace(/\[\d+]/, `[${index}]`)
                );
            }

            if(this.tagName === 'INPUT'){
                $(this).val('');
            }
            if(this.tagName === 'SELECT'){
                $(this).val('借');
            }
        });

        $template.find('input[name$="[id]"]').val('');

        return $template;
    }
    //傳票類型控制借/貸方
    function applyDcRule(){
        const type = $('select[name = "voucher_type"]').val();

        $('.item-row').each(function() {
            const $dcSelect = $(this).find('select[name$="[dc]"]');

            $dcSelect.find('option').prop('disabled', false);

            if(type === '0'){
                //收入->借
                $dcSelect.val('貸');
                $dcSelect.find('option[value="借"]').prop('disabled', true);
            }else if (type === '1'){
                //支出->貸
                $dcSelect.val('借');
                $dcSelect.find('option[value="貸"]').prop('disabled', true);
            }

        });

        recalcVoucherSum();
    }
    //計算借貸
    function recalcVoucherSum(){
        let debitCount = 0;
        let creditCount = 0;
        let debitAmount = 0;
        let creditAmount = 0;

        $('.item-row').each(function(){
            const dc = $(this).find('select[name$="[dc]"]').val();
            const amount = parseFloat(
                $(this).find('input[name$="[amount]"]').val()
            ) || 0;

            if(dc === '借'){
                debitCount++;
                debitAmount += amount;
            }
            if(dc === '貸'){
                creditCount++;
                creditAmount += amount;
            }
        });

        $('#debit-count').val(debitCount);
        $('#credit-count').val(creditCount);
        $('#debit-amount').val(debitAmount.toFixed(2));
        $('#credit-amount').val(creditAmount.toFixed(2));

        if(debitAmount === creditAmount && debitAmount > 0){
            $('#balance-status')
                .text('借貸平衡')
                .removeClass('text-danger')
                .addClass('text-success');
        }else{
            $('#balance-status')
                .text('借貸尚未平衡')
                .removeClass('text-success')
                .addClass('text-danger');
        }
    }
    
</script>
<script>
    //頁面載入觸發
    $(document).ready(function () {
        initSubjectSelect();
        recalcVoucherSum(); 
        applyDcRule();
    });

    $(document).on('change', 'select[name$="[subject_key]"]', function () {
        
        const opt = $(this).find(':selected');
        const row = $(this).closest('.item-row');
        
        // console.log('selected option:', opt.get(0));
        // console.log('data-main:', opt.data('main'));
        // console.log('data-sub:', opt.data('sub'));
        // console.log('data-item:', opt.data('item'));

        // console.log('target row:', row.get(0));

        row.find('input[name$="[main_code]"]').val(opt.data('main'));
        row.find('input[name$="[sub_code]"]').val(opt.data('sub'));
        row.find('input[name$="[item_code]"]').val(opt.data('item'));
        row.find('input[name$="[ledger_code]"]').val(opt.data('ledger') || null);
    });

    let index= {{ $voucher->items->count() }};

    $(document).on('click', '.insert-item-row', function(){
        const $currRow = $(this).closest('.item-row');
        const $newRow = cloneCleanRow(index);
        
        //插入
        $currRow.after($newRow);

        initSubjectSelect($newRow);
        recalcVoucherSum();
        applyDcRule();
    });

    //刪除分錄
    $(document).on('click', '.remove-item-row', function(){
        const $row = $(this).closest('.item-row');

        if($('#items-wrapper .item-row').length > 1){
            $row.remove();
            recalcVoucherSum();
        }else{
            alert('至少需要一筆分錄');
        }
    });

    //儲存常用分錄
    $('#save-temp-btn').on('click', function(){
        const form = $('form');
        const formData = form.serialize() + '&action=save_temp';

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(res){
                alert('已存為常用分錄');
            },
            error: function(xhr){
                alert(xhr.responseJSON?.message ?? '儲存失敗');
            }
        });
    });
    
    //綁定事件
    $(document).on('change keyup', 'select[name$="[dc]"], input[name$="[amount]"]', function(){
        recalcVoucherSum();
    });
</script>
@endsection
