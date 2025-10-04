@extends('layouts.app')

@section('title', 'Production | Edit Order')

@section('content')
<div class="row">
    <form id="productionForm" action="{{ route('production.update', $production->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="row">

            <!-- Master Details -->
            <div class="col-12 mb-3">
                <section class="card">
                    <header class="card-header d-flex justify-content-between">
                        <h2 class="card-title">Edit Production</h2>
                    </header>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label>Production #</label>
                                <input type="text" class="form-control" value="{{ $production->production_code }}" disabled/>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label>Vendor Name</label>
                                <select data-plugin-selecttwo class="form-control select2-js" name="vendor_id" required>
                                    <option value="" disabled>Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ $vendor->id == $production->vendor_id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label>Order Date</label>
                                <input type="date" name="order_date" class="form-control" value="{{ $production->order_date->format('Y-m-d') }}" required/>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Raw Material Details -->
            <div class="col-12 mb-3">
                <section class="card">
                    <header class="card-header d-flex justify-content-between">
                        <h2 class="card-title">Raw Material Details</h2>
                    </header>
                    <div class="card-body">
                        <table class="table table-bordered" id="rawTable">
                            <thead>
                                <tr>
                                    <th>Raw</th>
                                    <th>Variation</th>
                                    <th>Purchase #</th>
                                    <th>Description</th>
                                    <th>Rate</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="rawTableBody">
                                @foreach($production->details as $k => $item)
                                <tr>
                                    <td>
                                        <input type="hidden" name="item_details[{{ $k }}][id]" value="{{ $item->id }}">
                                        <select name="item_details[{{ $k }}][product_id]" id="productSelect_{{ $k }}" class="form-control select2-js" onchange="onItemChange(this)" required>
                                            <option value="">Select Raw</option>
                                            @foreach($allProducts as $product)
                                                <option value="{{ $product->id }}" data-unit="{{ $product->unit }}" {{ $product->id == $item->product_id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="item_details[{{ $k }}][variation_id]" id="variationSelect{{ $k }}" class="form-control select2-js">
                                            @if($item->variation)
                                                <option value="{{ $item->variation_id }}" selected>{{ $item->variation->sku }}</option>
                                            @else
                                                <option value="">Select Variation</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td>
                                        <select name="item_details[{{ $k }}][invoice_id]" id="invoiceSelect{{ $k }}" class="form-control" onchange="onInvoiceChange(this)">
                                            @if($item->invoice)
                                                <option value="{{ $item->invoice_id }}" data-rate="{{ $item->invoice->rate }}" selected>INV-{{ $item->invoice->id }}</option>
                                            @else
                                                <option value="">Select Invoice</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td><input type="text" name="item_details[{{ $k }}][desc]" value="{{ $item->desc }}" class="form-control"/></td>
                                    <td><input type="number" name="item_details[{{ $k }}][item_rate]" id="item_rate_{{ $k }}" value="{{ $item->rate }}" step="any" class="form-control" onchange="rowTotal({{ $k }})"/></td>
                                    <td><input type="number" name="item_details[{{ $k }}][qty]" id="item_qty_{{ $k }}" value="{{ $item->qty }}" step="any" class="form-control" onchange="rowTotal({{ $k }})"/></td>
                                    <td>
                                        <select id="item_unit_{{ $k }}" class="form-control" name="item_details[{{ $k }}][item_unit]" required>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}" {{ $unit->id == $item->item_unit ? 'selected' : '' }}>{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" id="item_total_{{ $k }}" value="{{ $item->rate * $item->qty }}" class="form-control" readonly/></td>
                                    <td><button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-success btn-sm mt-2" onclick="addRawRow()">+ Add Row</button>
                    </div>
                </section>
            </div>

            <!-- Product Details -->
            <div class="col-12 mb-3">
                <section class="card">
                    <header class="card-header"><h2 class="card-title">Product Details</h2></header>
                    <div class="card-body">
                        <table class="table table-bordered" id="productTable">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Variation</th>
                                    <th>Consumption</th>
                                    <th>M.Cost</th>
                                    <th>Order Qty</th>
                                    <th>Remarks</th>
                                    <th>Raw Use</th>
                                    <th>Amount</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($production->productDetails as $k => $pd)
                                <tr>
                                    <td>
                                        <input type="hidden" name="product_details[{{ $k }}][id]" value="{{ $pd->id }}">
                                        <select name="product_details[{{ $k }}][product_id]" class="form-control select2-js product-select" data-preselectVariationId="{{ $pd->variation_id }}" required>
                                            <option value="">Select Item</option>
                                            @foreach($allProducts as $item)
                                                <option value="{{ $item->id }}" {{ $item->id == $pd->product_id ? 'selected' : '' }}>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="product_details[{{ $k }}][variation_id]" class="form-control select2-js variation-select">
                                            @if($pd->variation)
                                                <option value="{{ $pd->variation_id }}" selected>{{ $pd->variation->sku }}</option>
                                            @else
                                                <option value="">Select Variation</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td><input type="number" class="form-control consumption" name="product_details[{{ $k }}][consumption]" step="any" value="{{ $pd->consumption }}"></td>
                                    <td><input type="number" class="form-control manufacturing_cost" name="product_details[{{ $k }}][manufacturing_cost]" step="any" value="{{ $pd->manufacturing_cost }}"></td>
                                    <td><input type="number" class="form-control order-qty" name="product_details[{{ $k }}][order_qty]" step="any" value="{{ $pd->order_qty }}"></td>
                                    <td><input type="text" class="form-control" name="product_details[{{ $k }}][remarks]" value="{{ $pd->remarks }}"></td>
                                    <td><input type="number" class="form-control raw_use" value="{{ $pd->order_qty * $pd->consumption }}" readonly></td>
                                    <td><input type="number" class="form-control amount" value="{{ $pd->order_qty * $pd->manufacturing_cost }}" readonly></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row-btn"><i class="fas fa-times"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-success btn-sm mt-2" id="addProductRow">+ Add Row</button>
                    </div>
                </section>
            </div>

            <!-- Summary -->
            <div class="col-12">
                <section class="card">
                    <header class="card-header"><h2 class="card-title">Summary</h2></header>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2"><label>Total Raw Qty</label><input type="number" id="totalRawQty" class="form-control" readonly></div>
                            <div class="col-md-2"><label>Total Raw Amount</label><input type="number" id="totalRawAmount" class="form-control" readonly></div>
                            <div class="col-md-2"><label>Total Raw Use</label><input type="number" id="totalRawUse" class="form-control" readonly></div>
                            <div class="col-md-2"><label>Remaining Raw</label><input type="number" id="remainingRaw" class="form-control" readonly></div>
                            <div class="col-md-2"><label>Products Total Amount</label><input type="number" id="productsTotalAmt" class="form-control" readonly></div>
                            <div class="col-md-12 text-end mt-3">
                                <h3 class="font-weight-bold text-primary">Net Amount: <span id="netTotal" class="text-danger">0.00</span></h3>
                                <input type="hidden" name="total_amount" id="net_amount">
                            </div>
                        </div>
                    </div>
                    <footer class="card-footer text-end">
                        <a href="{{ route('production.index') }}" class="btn btn-danger">Discard</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </footer>
                </section>
            </div>

        </div>
    </form>
</div>

<script>
let rawIndex = {{ $production->details->count() }};
let productIndex = {{ $production->productDetails->count() }};
const allProducts = @json($allProducts);

$(document).ready(function () {
    $('.select2-js').select2({ width: '100%' });

    // Initial total calculation
    calcRawTotals();
    calcProductTotals();

    // Recalculate summary on product input change
    $(document).on('input', '.consumption, .manufacturing_cost, .order-qty', function() {
        calcProductTotals();
    });

    // Remove row buttons
    $(document).on('click', '.remove-row-btn', function() {
        $(this).closest('tr').remove();
        calcRawTotals();
        calcProductTotals();
    });
});

// 🔹 Add raw row
function addRawRow() {
    const tbody = $('#rawTableBody');
    const options = allProducts.map(p => `<option value="${p.id}" data-unit="${p.unit ?? ''}">${p.name}</option>`).join('');
    tbody.append(`
        <tr>
            <td><select name="item_details[${rawIndex}][product_id]" id="productSelect_${rawIndex}" class="form-control select2-js" onchange="onItemChange(this)" required>
                <option value="" disabled selected>Select Raw</option>${options}</select></td>
            <td><select name="item_details[${rawIndex}][variation_id]" id="variationSelect${rawIndex}" class="form-control select2-js">
                <option value="" disabled selected>Select Variation</option></select></td>
            <td><select name="item_details[${rawIndex}][invoice_id]" id="invoiceSelect${rawIndex}" class="form-control" onchange="onInvoiceChange(this)">
                <option value="" selected>Select Invoice</option></select></td>
            <td><input type="text" name="item_details[${rawIndex}][desc]" class="form-control"></td>
            <td><input type="number" name="item_details[${rawIndex}][item_rate]" id="item_rate_${rawIndex}" class="form-control" step="any" value="0" onchange="rowTotal(${rawIndex})"></td>
            <td><input type="number" name="item_details[${rawIndex}][qty]" id="item_qty_${rawIndex}" class="form-control" step="any" value="0" onchange="rowTotal(${rawIndex})"></td>
            <td><select id="item_unit_${rawIndex}" name="item_details[${rawIndex}][item_unit]" class="form-control" required>
                <option value="" disabled selected>Select Unit</option>@foreach($units as $unit)<option value="{{ $unit->id }}">{{ $unit->name }}</option>@endforeach
            </select></td>
            <td><input type="number" id="item_total_${rawIndex}" class="form-control" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>
        </tr>
    `);
    $(`#productSelect_${rawIndex}`).select2({ width: '100%' });
    rawIndex++;
}

// 🔹 Calculate raw totals
function rowTotal(i) {
    const rate = parseFloat($(`#item_rate_${i}`).val()) || 0;
    const qty = parseFloat($(`#item_qty_${i}`).val()) || 0;
    $(`#item_total_${i}`).val((rate * qty).toFixed(2));
    calcRawTotals();
}

function calcRawTotals() {
    let totalQty = 0, totalAmt = 0;
    $('#rawTableBody tr').each(function() {
        const rate = parseFloat($(this).find('input[id^="item_rate_"]').val()) || 0;
        const qty = parseFloat($(this).find('input[id^="item_qty_"]').val()) || 0;
        totalQty += qty;
        totalAmt += rate * qty;
    });
    $('#totalRawQty').val(totalQty);
    $('#totalRawAmount').val(totalAmt.toFixed(2));
    calcProductTotals();
}

// 🔹 Add product row
$('#addProductRow').click(function() {
    const tbody = $('#productTable tbody');
    const options = allProducts.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
    tbody.append(`
        <tr>
            <td><select name="product_details[${productIndex}][product_id]" class="form-control select2-js" required>${options}</select></td>
            <td><select name="product_details[${productIndex}][variation_id]" class="form-control select2-js"><option value="" disabled selected>Select Variation</option></select></td>
            <td><input type="number" name="product_details[${productIndex}][consumption]" class="form-control consumption" step="any" value="0"></td>
            <td><input type="number" name="product_details[${productIndex}][manufacturing_cost]" class="form-control manufacturing_cost" step="any" value="0"></td>
            <td><input type="number" name="product_details[${productIndex}][order_qty]" class="form-control order-qty" step="any" value="0"></td>
            <td><input type="text" name="product_details[${productIndex}][remarks]" class="form-control"></td>
            <td><input type="number" class="form-control raw_use" readonly></td>
            <td><input type="number" class="form-control amount" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row-btn"><i class="fas fa-times"></i></button></td>
        </tr>
    `);
    $('.select2-js').select2({ width: '100%' });
    productIndex++;
});

// 🔹 Calculate product totals & summary
function calcProductTotals() {
    let totalRawUse = 0, totalProdAmt = 0;
    $('#productTable tbody tr').each(function() {
        const consumption = parseFloat($(this).find('.consumption').val()) || 0;
        const mCost = parseFloat($(this).find('.manufacturing_cost').val()) || 0;
        const orderQty = parseFloat($(this).find('.order-qty').val()) || 0;
        const rawUse = consumption * orderQty;
        const amount = mCost * orderQty;
        totalRawUse += rawUse;
        totalProdAmt += amount;
        $(this).find('.raw_use').val(rawUse.toFixed(2));
        $(this).find('.amount').val(amount.toFixed(2));
    });
    $('#totalRawUse').val(totalRawUse.toFixed(2));
    $('#productsTotalAmt').val(totalProdAmt.toFixed(2));
    const remaining = parseFloat($('#totalRawAmount').val() || 0) - totalProdAmt;
    $('#remainingRaw').val(remaining.toFixed(2));
    $('#netTotal').text(totalProdAmt.toFixed(2));
    $('#net_amount').val(totalProdAmt.toFixed(2));
}

// 🔹 Remove raw row
function removeRow(btn) {
    $(btn).closest('tr').remove();
    calcRawTotals();
}

// 🔹 On item change (raw) → fetch variations & invoices
function onItemChange(select) {
    const row = $(select).closest('tr');
    const itemId = select.value;
    if (!itemId) return;

    // Fetch variations
    $.get(`/product/${itemId}/variations`, function(data){
        const variationSelect = row.find('select[id^="variationSelect"]');
        variationSelect.html('<option value="">Select Variation</option>');
        if (data.variation && data.variation.length) {
            data.variation.forEach(v => variationSelect.append(`<option value="${v.id}">${v.sku}</option>`));
        } else variationSelect.html('<option value="">No Variations</option>');
        variationSelect.select2({ width: '100%' });
    });

    // Fetch invoices
    const invoiceSelect = row.find('select[id^="invoiceSelect"]');
    $.get(`/product/${itemId}/invoices`, function(data){
        invoiceSelect.html('<option value="">Select Invoice</option>');
        if (data.length > 0) data.forEach(inv => invoiceSelect.append(`<option value="${inv.id}" data-rate="${inv.rate}">INV-${inv.id}</option>`));
        invoiceSelect.select2({ width: '100%' });
    });

    // Auto-fill unit
    const unitSelect = row.find('select[id^="item_unit_"]');
    const unitId = $(select.selectedOptions[0]).data('unit');
    if (unitId) unitSelect.val(unitId).trigger('change');
    row.find('input[id^="item_rate_"], input[id^="item_qty_"], input[id^="item_total_"]').val('');
    calcRawTotals();
}

// 🔹 On invoice change → fill rate
function onInvoiceChange(select) {
    const row = $(select).closest('tr');
    const rate = parseFloat(select.selectedOptions[0]?.dataset.rate || 0);
    row.find('input[id^="item_rate_"]').val(rate);
    const qty = parseFloat(row.find('input[id^="item_qty_"]').val() || 0);
    row.find('input[id^="item_total_"]').val((rate * qty).toFixed(2));
    calcRawTotals();
}
</script>
@endsection
