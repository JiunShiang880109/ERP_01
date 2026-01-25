
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
                            <li class="breadcrumb-item"><a href="{{route('accountant.voucher_temp')}}">常用傳票管理</a></li>
                            <li class="breadcrumb-item active" aria-current="page">新增常用</li>
                       </ol>
                   </nav>
               </div>
           </div>
           <!-- -------------------------- -->
       </div>
       {{--  --}}
        <div class="content-wrap mt-3">
            <div class="main">
                <div class="card alert ">
                        <div class="card-body">
                            {{--{{dd(session()->all())}}--}}
                            <form action="{{route('accountant.store_voucher')}}" method="POST">
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
                                        <label class="form-label">模板日期</label>
                                        <input type="date" name="voucher_date" class="form-control">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">傳票類別</label>
                                        <select class="form-select" name="voucher_type">
                                            <option value="">選擇傳票類別</option>
                                            <option value="0">現金收入</option>
                                            <option value="1">現金支出</option>
                                            <option value="2">轉帳</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">傳票摘要</label>
                                        <input type="text" name="note" class="form-control">
                                    </div>
                                </div>
                                <hr>
                                <h6>分錄明細</h6>

                                <div id="items-wrapper">

                                    <div class="row g-3 item-row">

                                        <div class="col-md-2">
                                            <label>借 / 貸</label>
                                            <select class="form-select" name="items[0][dc]">
                                                <option value="借">借</option>
                                                <option value="貸">貸</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label>科目</label>
                                            <select class="form-select subject-select" name="items[0][subject_key]">
                                                <option value="">選擇科目</option>
                                                    @foreach($items->groupBy(fn($r) => $r->main_code.'-'.$r->sub_code.'-'.$r->code) as $group)
                                                        @php
                                                            $item = $group->first();
                                                            $hasLedger = $group->whereNotNull('ledger_code')->count() > 0;
                                                        @endphp

                                                        <optgroup label="{{ $item->main_code }}{{ $item->sub_code }}{{ $item->code }} {{ $item->name }}">
                                                            
                                                            @if($hasLedger)
                                                                {{-- 有子科目 → 只能選子科目 --}}
                                                                @foreach($group as $row)
                                                                    @if($row->ledger_code)
                                                                        <option
                                                                            value="{{ $row->ledger_code }}"
                                                                            data-main="{{ $row->main_code }}"
                                                                            data-sub="{{ $row->sub_code }}"
                                                                            data-item="{{ $row->code }}">
                                                                            .{{ $row->ledger_code }} {{ $row->ledger_name }}
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            @else
                                                                {{-- 沒有子科目 → 允許選主科目 --}}
                                                                <option
                                                                    value="{{ $item->main_code }}-{{ $item->sub_code }}-{{ $item->code }}"
                                                                    data-main="{{ $item->main_code }}"
                                                                    data-sub="{{ $item->sub_code }}"
                                                                    data-item="{{ $item->code }}">
                                                                    {{ $item->name }}
                                                                </option>
                                                            @endif

                                                        </optgroup>
                                                    @endforeach
                                            </select>
                                            <input type="hidden" name="items[0][main_code]">
                                            <input type="hidden" name="items[0][sub_code]">
                                            <input type="hidden" name="items[0][item_code]">
                                            <input type="hidden" name="items[0][ledger_code]">
                                        </div>

                                        <div class="col-md-2">
                                            <label>金額</label>
                                            <input type="number" name="items[0][amount]" class="form-control">
                                        </div>

                                        <div class="col-md-12">
                                            <label>分錄摘要</label>
                                            <input type="text" name="items[0][note]" class="form-control">
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
                                    <button type="button"
                                        id="save-temp-btn"
                                        class="btn btn-outline-secondary">
                                        儲存常用分錄
                                    </button>
                                    <button type="button"
                                        class="btn btn-outline-primary"
                                        id="open-template-modal">
                                        套用常用分錄
                                    </button>
                                    <button type="reset" class="btn btn-secondary">取消</button>
                                </div>

                            </form>
                            
                            <!--常用分錄-->
                            <div class="modal fade" id="templateModal" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">套用常用分錄</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <ul class="list-group" id="template-list"></ul>
                                    </div>

                                    </div>
                                </div>
                            </div>

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
    //傳票類型控制借/貸方
    function applyDcRule(){
        const type = $('select[name = "voucher_type"]').val();

        $('.item-row').each(function() {
            const $dcSelect = $(this).find('select[name$="[dc]"]');

            $dcSelect.find('option').prop('disabled', false);

            if(type === '0'){
                //收入->借
                $dcSelect.val('借');
                $dcSelect.find('option[value="貸"]').prop('disabled', true);
            }else if (type === '1'){
                //支出->貸
                $dcSelect.val('貸');
                $dcSelect.find('option[value="借"]').prop('disabled', true);
            }

        });

        recalcVoucherSum();
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

        return $template;
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
    $(document).ready(function () {
        initSubjectSelect();
    });
    
    $(document).on('change', 'select[name="voucher_type"]', function(){
        applyDcRule();
    })

    $(document).on('change', 'select[name$="[subject_key]"]', function () {

        const opt = $(this).find(':selected');
        const row = $(this).closest('.item-row');

        row.find('input[name$="[main_code]"]').val(opt.data('main'));
        row.find('input[name$="[sub_code]"]').val(opt.data('sub'));
        row.find('input[name$="[item_code]"]').val(opt.data('item'));
        row.find('input[name$="[ledger_code]"]').val(opt.data('ledger') || null);
    });


    let index=1;

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

    
    //綁定事件
    $(document).on('change keyup', 'select[name$="[dc]"], input[name$="[amount]"]', function(){
        recalcVoucherSum();
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
                alert('已儲存為常用分錄');
            },
            error: function(xhr){
                alert(xhr.responseJSON?.message ?? '儲存失敗');
            }
        });
    });
</script>
<script>
    //套用分錄
    let itemRowTemplate = null;

    $(document).ready(function(){
        const $row = $('#items-wrapper .item-row').first().clone();

        $row.find('.subject-select')
            .removeClass('select2-hidden-accessible')
            .removeAttr('data-select2-id')
            .next('.select2').remove();

        itemRowTemplate = $row;
    });

    $('#open-template-modal').on('click', function(){
        $.get('/accountant/voucher_temp', function(list){
            const $list = $('#template-list');
            $list.empty();

            list.forEach(t=>{
                $list.append(`
                    <li class="list-group-item d-flex justify-content-between">
                        <span>${t.note ?? '(無摘要)'}</span>
                        <button class="btn btn-sm btn-primary apply-template" data-id="${t.id}">套用</button>
                    </li>
                `);
            });
            $('#templateModal').modal('show');
        });
    });

    $(document).on('click', '.apply-template', function(){
        const id = $(this).data('id');

        $.get(`/accountant/voucher_temp_items/${id}`, function(tpl){

            $('select[name="voucher_type"]').val(tpl.voucher_type);
            $('input[name="note"]').val(tpl.note);

            const $wrapper =$('#items-wrapper');
            $wrapper.empty();

            tpl.items.forEach((item, index) => {

                const $row = itemRowTemplate.clone();

                // 修正 name index
                $row.find('input, select').each(function(){
                    const name = $(this).attr('name');
                    if(name){
                        $(this).attr('name', name.replace(/\[\d+]/, `[${index}]`));
                    }
                });

                // 借 / 貸
                $row.find('select[name$="[dc]"]').val(item.dc);

                // 金額
                $row.find('input[name$="[amount]"]').val(item.amount);

                // 分錄摘要
                $row.find('input[name$="[note]"]').val(item.note);

                // 直接回填 hidden 欄位（關鍵）
                $row.find('input[name$="[main_code]"]').val(item.main_code);
                $row.find('input[name$="[sub_code]"]').val(item.sub_code);
                $row.find('input[name$="[item_code]"]').val(item.item_code);
                $row.find('input[name$="[ledger_code]"]').val(item.ledger_code);

                $wrapper.append($row);
                
                initSubjectSelect($row);

                const $subject = $row.find('select[name$="[subject_key]"]');

                // 設定 select（顯示用）
                if(item.ledger_code){
                    $subject.val(item.ledger_code).trigger('change.select2');
                }else{
                    $subject.val(`${item.main_code}-${item.sub_code}-${item.item_code}`).trigger('change.select2');
                }

            });

            applyDcRule();       // 限制借/貸
            recalcVoucherSum();  // 借貸平衡

            $('#templateModal').modal('hide');
        });
        
    });

</script>
@endsection
