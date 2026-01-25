@extends('layout')
<link href="{{asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet" />
<link href="{{asset('assets/plugins/select2/css/select2-bootstrap4.css')}}" rel="stylesheet" />
<!-- 引用模板 -->
@section('head')
@endsection
@section('content')
    <div class="bg-white p-3">
        <div class="row p-2 justify-content-between align-items-center border-bottom">
            <!--breadcrumb-->
           <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3 col-md-6">
               <div class="breadcrumb-title pe-3">庫存成本管理</div>
               <div class="ps-3">
                   <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{route('inventory.checklist')}}">清單管理</a></li>
                            <li class="breadcrumb-item active" aria-current="page">新增清單</li>
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
                            <form action="{{route('inventory.store_checklist')}}" method="POST" class="row g-3">
                                @csrf
                                
                                {{-- Title --}}
                                <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bxs-edit me-1 font-22 text-primary"></i></div>
                                    <h5 class="mb-0 text-primary">新增進貨紀錄</h5>
                                </div>
                                <hr>

                                <div class="col-md-4">
                                    <label for="inputFirstName" class="form-label">員工編號</label>
                                    <input type="text" name="employeeId" class="form-control" value="{{session('employeeId')}}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">姓名</label>
                                    <input type="text" name="employeeName" class="form-control" value="{{session('employeeName')}}" readonly>
                                </div>
                                <h6 class="mb-0 text-primary">*員工編號和姓名為當前帳戶之編號及姓名，請注意，勿使用他人帳戶進行操作。</h6>
                                <hr>

                                <div class="row item-row mb-2 border p-2 rounded">
                                    {{-- 原料 --}}
                                    <div class="col-md-4">
                                        <label class="form-label">原物料</label>
                                        <select class="form-select select2 ing-select" name="ingredientId[]" required>
                                            <option value="">選擇原物料</option>
                                            @foreach($ingredients as $ing)
                                                <option value="{{ $ing->id }}" data-category="{{ $ing->categoryMainId }}">
                                                    [{{ optional($ing->ingredientsCateMain)->name }}] 
                                                    {{ $ing->name }}（{{ $ing->unit }}）
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- 類別 --}}
                                    <div class="col-md-6">
                                        <label class="form-label">類別</label>
                                        <input type="text" class="form-control categoryName" readonly>
                                        <input type="hidden" name="categoryMainId[]" class="categoryMainId">         
                                    </div>
                                
                                    {{-- 數量 --}}
                                    <div class="col-md-4">
                                        <label class="form-label">進貨數量</label>
                                        <input type="number" name="quantity[]" step="1" min="0"
                                            class="form-control quantity" required>
                                    </div>

                                    {{-- 單價 --}}
                                    <div class="col-md-4">
                                        <label class="form-label">單價</label>
                                        <input type="number" name="unitPrice[]" step="0.01" min="0"
                                            class="form-control unitPrice" required>
                                    </div>

                                    {{-- 小計--}}
                                    <div class="col-md-2">
                                        <label class="form-label">小計</label>
                                        <input type="text" class="form-control subtotal" readonly>
                                    </div>
                                </div>

                                {{-- 新增/刪除 --}}
                                <div class="col-12 mb-3">
                                    <button type="button" class="btn btn-secondary" id="add-item">＋ 新增品項</button>
                                    <button type="button" class="btn btn-danger remove-item">－ 刪除品項</button>
                                </div>
                                

                                {{-- 廠商 --}}
                                <div class="col-md-4">
                                    <label class="form-label">廠商 / 供應商 (登記已同一間廠商 / 供應商為準方便日後追蹤)</label>
                                    <input type="text" name="supplier" class="form-control">
                                </div>

                                {{-- 訂購 / 採買 --}}
                                <div class="col-md-4">
                                    <label class="form-label">訂購 / 採買</label>
                                    <input type="text" name="buyer" class="form-control">
                                </div>

                                {{-- 發票號碼 --}}
                                <div class="col-md-4">
                                    <label class="form-label">發票 / 單號 (登記已同一張發票 / 單號為準方便日後追蹤)</label>
                                    <input type="text" name="invoiceNumber" class="form-control">
                                </div>

                                {{-- 訂貨日期 --}}
                                <div class="col-md-4">
                                    <label class="form-label">訂貨日期</label>
                                    <input type="date" name="purchaseDate" class="form-control"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>

                                {{-- 備註 --}}
                                <div class="col-md-12">
                                    <label class="form-label">備註</label>
                                    <textarea name="note" rows="3" class="form-control"></textarea>
                                </div>

                                {{-- 確認表單 --}}
                                <div class="col-md-12">
                                    <label class="form-label">表單確認</label>
                                    <textarea id="checkform" rows="3" class="form-control" readonly></textarea>
                                </div>

                                {{-- 送出 --}}
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary px-5">送出</button>
                                </div>
                            </form>

                        </div>
                </div>
            </div>
        </div>
    </div>

{{-- --}}
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
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>

<script>
//類別欄位自動帶入值
function bindSelectEvent(row){
    row.querySelector('.ing-select').addEventListener('change', function(){
        let cate = this.selectedOptions[0].dataset.category;
        let text = this.selectedOptions[0].text;

        row.querySelector('.categoryMainId').value = cate;
        row.querySelector('.categoryName').value = text.match(/^\[(.*?)\]/)?.[1] ?? '';
        updateCheckForm();
    });
}

// 計算小計
function calcTotal(){
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row=>{
        let qty = parseFloat(row.querySelector('.quantity')?.value) || 0;
        let price = parseFloat(row.querySelector('.unitPrice')?.value) || 0;
        let subtotal = qty * price;
        row.querySelector('.subtotal').value = subtotal.toFixed(2);
        total += subtotal;
    });
    return total;
}
//表單確認
function updateCheckForm(){
    let text = "明細：\n";
    let total = 0;

    document.querySelectorAll('.item-row').forEach((row, idx)=>{
        let ing   = row.querySelector('select[name="ingredientId[]"]');
        let name  = ing?.selectedOptions[0]?.text.trim() || "未選擇原料";
        let qty   = row.querySelector('.quantity')?.value || 0;
        let price = row.querySelector('.unitPrice')?.value || 0;
        let subtotal = qty * price;

        total += subtotal;

        text += `${idx+1}. ${name} × ${qty}  單價 ${price} → 小計 ${subtotal.toFixed(2)}\n`;
    });

    text += "--------------------------------------\n";
    text += `總金額：${total.toFixed(2)} 元\n\n`;

    // 其他欄位寫入
    let supplier = document.querySelector('[name="supplier"]').value || "-";
    let buyer = document.querySelector('[name="buyer"]').value || "-";
    let invoice = document.querySelector('[name="invoiceNumber"]').value || "-";
    let date = document.querySelector('[name="purchaseDate"]').value || "-";
    let note = document.querySelector('[name="note"]').value || "-";

    text += `供應商：${supplier}\n`;
    text += `採買人：${buyer}\n`;
    text += `發票/單號：${invoice}\n`;
    text += `日期：${date}\n`;
    text += `備註：${note}\n`;

    document.querySelector("#checkform").value = text;
}

let firstRow = document.querySelector('.item-row');
let template = firstRow.cloneNode(true);
template.querySelectorAll('input').forEach(i=> i.value="");
template.querySelector('.subtotal').value="0.00";

document.getElementById('add-item').addEventListener('click',function(){
    let newRow = template.cloneNode(true);
    
    let rows = document.querySelectorAll('.item-row');
    let last = rows[rows.length-1];

    last.insertAdjacentElement('afterend', newRow);

    bindSelectEvent(newRow);
    bindRowEvents(newRow);
    updateCheckForm();
});

document.querySelector('.remove-item').addEventListener('click',function(){
    let rows=document.querySelectorAll('.item-row');
    if(rows.length>1){
        rows[rows.length-1].remove();
        updateCheckForm();
    }
});

function bindRowEvents(row){
    row.querySelector('.quantity').addEventListener('input', ()=>{ calcTotal(); updateCheckForm(); });
    row.querySelector('.unitPrice').addEventListener('input', ()=>{ calcTotal(); updateCheckForm(); });
}

document.querySelectorAll('.item-row').forEach(row=>{
    bindRowEvents(row);
    bindSelectEvent(row);
});

//監聽原物料以外欄位
document.addEventListener('input', function(e){
    if(e.target.matches('[name="supplier"],[name="buyer"],[name="invoiceNumber"],[name="purchaseDate"],[name="note"]')){
        updateCheckForm();
    }
});

// 原料下拉改變也更新(含初始)
document.querySelectorAll('select[name="ingredientId[]"]').forEach(sel=>{
    sel.addEventListener('change', updateCheckForm);
});


updateCheckForm();

</script>

@endsection