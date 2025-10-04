@extends('layouts.app')

@section('title', 'Production | New Order')

@section('content')
  <div class="row">
    <form id="productionForm" action="{{ route('production.store') }}" method="POST" enctype="multipart/form-data">
      @csrf

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
     
      <div class="row">
        <div class="col-12 col-md-12 mb-3">
          <section class="card">
            <header class="card-header">
              <div style="display: flex;justify-content: space-between;">
                <h2 class="card-title">New Production</h2>
              </div>
            </header>
            <div class="card-body">
              <div class="row">
                <div class="col-12 col-md-2 mb-3">
                  <label>Production #</label>
                  <input type="text" class="form-control" value="{{ $nextProductionCode ?? '' }}" disabled/>
                </div>

                <div class="col-12 col-md-2 mb-3">
                  <label>Vendor Name</label>
                  <select data-plugin-selecttwo class="form-control select2-js" name="vendor_id" id="vendor_name" required>
                    <option value="" selected disabled>Select Vendor</option>
                      @foreach($vendors as $item)  
                        <option value="{{$item->id}}">{{$item->name}}</option>
                      @endforeach
                  </select>
                </div>

                <div class="col-12 col-md-2 mb-3">
                  <label>Order Date</label>
                  <input type="date" name="order_date" class="form-control" id="order_date" value="{{ date('Y-m-d') }}" required/>
                </div>
              </div>
            </div>
          </section>
        </div>

        <!-- Raw Material Details -->
        <div class="col-12 col-md-12 mb-3">
          <section class="card">
            <header class="card-header" style="display: flex;justify-content: space-between;">
              <h2 class="card-title">Raw Details</h2>
            </header>
            <div class="card-body">
              <table class="table table-bordered" id="myTable">
                <thead>
                  <tr>
                    <th width="15%">Raw</th>
                    <th width="15%">Variation</th>
                    <th width="12%">Purchase #</th>
                    <th width="15%">Description</th>
                    <th width="7%">Rate</th>
                    <th width="7%">Qty</th>
                    <th width="9%">Unit</th>
                    <th width="8%">Total</th>
                    <th width="5%"></th>
                  </tr>
                </thead>
                <tbody id="PurPOTbleBody">
                  <tr class="item-row">
                    <td>
                      <select name="item_details[0][product_id]" id="productSelect_0" class="form-control select2-js" onchange="onItemChange(this)" required>
                        <option value="" selected disabled>Select Raw</option>
                        @foreach($allProducts as $product)
                          <option value="{{ $product->id }}" data-unit="{{ $product->unit }}">{{ $product->name }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td>
                      <select name="item_details[0][variation_id]" id="variationSelect0" class="form-control select2-js">
                        <option value="" selected disabled>Select Variation</option>
                      </select>
                    </td>
                    <td>
                      <select name="item_details[0][invoice_id]" id="invoiceSelect0" class="form-control" onchange="onInvoiceChange(this)">
                        <option value="" selected>Select Invoice</option>
                      </select>
                    </td>
                    <td><input type="text" name="item_details[0][desc]" id="item_desc_0" class="form-control" placeholder="Description"/></td>
                    <td><input type="number" name="item_details[0][item_rate]" id="item_rate_0" onchange="rowTotal(0)" step="any" value="0" class="form-control" placeholder="Rate" required/></td>
                    <td><input type="number" name="item_details[0][qty]" id="item_qty_0" onchange="rowTotal(0)" step="any" value="0" class="form-control" placeholder="Quantity" required/></td>
                    <td>
                      <select id="item_unit_0" class="form-control" name="item_details[0][item_unit]" required>
                        <option value="" disabled selected>Select Unit</option>
                         @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                         @endforeach          
                      </select>
                    </td>
                    <td><input type="number" id="item_total_0" class="form-control" placeholder="Total" disabled/></td>
                    <td width="5%"><button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button></td>
                  </tr>
                </tbody>
              </table>
              <button type="button" class="btn btn-success btn-sm mt-2" onclick="addNewRow()">+ Add Row</button>
            </div>
          </section>
        </div>

        <!-- Finish Good Details -->
        <div class="col-12 mb-4">
          <section class="card">
            <header class="card-header">
              <h2 class="card-title">Product Details</h2>
            </header>
            <div class="card-body">
              <table class="table table-bordered" id="itemTable">
                <thead>
                  <tr>
                    <th width="15%">Item</th>
                    <th width="15%">Variation</th>
                    <th width="10%">Consumption</th>
                    <th width="8%">M.Cost</th>
                    <th width="8%">Order Qty</th>
                    <th width="15%">Remarks</th>
                    <th width="10%">Raw Use</th>
                    <th width="10%">Amount</th>
                    <th width="5%"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <select name="product_details[0][product_id]" class="form-control select2-js product-select" required>
                        <option value="">Select Item</option>
                        @foreach($allProducts as $item)
                          <option value="{{ $item->id }}" data-consumption="{{ $item->consumption }}" data-mfg-cost="{{ $item->manufacturing_cost }}">{{ $item->name }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td>
                      <select name="product_details[0][variation_id]" class="form-control select2-js variation-select">
                        <option value="">Select Variation</option>
                      </select>
                    </td>
                    <td><input type="number" class="form-control consumption" name="product_details[0][consumption]" step="any" value="0" required></td>
                    <td><input type="number" class="form-control manufacturing_cost" name="product_details[0][manufacturing_cost]" step="any" value="0"></td>
                    <td><input type="number" class="form-control order-qty" name="product_details[0][order_qty]" step="any" value="0" required></td>
                    <td><input type="text" class="form-control" name="product_details[0][remarks]"></td>
                    <td><input type="number" class="form-control raw_use" name="product_details[0][raw_use]" step="any" value="0" readonly></td>
                    <td><input type="number" class="form-control total_amount" name="product_details[0][total]" step="any" value="0" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row-btn"><i class="fas fa-times"></i></button></td>
                  </tr>
                </tbody>
              </table>
              <button type="button" class="btn btn-success btn-sm mt-2" id="addRowBtn">+ Add Row</button>

            </div>
          </section>
        </div>

        <div class="col-12">
          <section class="card">
            <header class="card-header d-flex justify-content-between">
              <h2 class="card-title">Summary</h2>
            </header>
            <div class="card-body">
              <div class="row pb-4">
                <div class="col-12 col-md-2">
                  <label>Total Raw Qty</label>
                  <input type="number" class="form-control" id="total_fab" placeholder="Total Qty" disabled/>
                </div>

                <div class="col-12 col-md-2">
                  <label>Total Raw Amount</label>
                  <input type="number" class="form-control" id="total_fab_amt" placeholder="Total Amount" disabled />
                </div>

                <div class="col-12 col-md-2">
                  <label>Total Raw Use</label>
                  <input type="number" class="form-control" id="total_raw_use" placeholder="Total Raw Use" disabled />
                </div>

                <div class="col-12 col-md-2">
                  <label>Remaining Raw</label>
                  <input type="number" class="form-control" id="remaining_raw" placeholder="Remaining Raw" disabled />
                </div>

                <div class="col-12 col-md-2">
                  <label>Products Total Amount</label>
                  <input type="number" class="form-control" id="product_total_amt" placeholder="Total Amount" disabled />
                </div>

                <div class="col-12 text-end mt-3">
                  <h3 class="font-weight-bold mb-0 text-5 text-primary">Net Amount</h3>
                  <span>
                    <strong class="text-4 text-primary">PKR 
                      <span id="netTotal" class="text-4 text-danger">0.00</span>
                    </strong>
                  </span>
                  <input type="hidden" name="total_amount" id="net_amount">
                </div>
              </div>
            </div>
            <footer class="card-footer text-end">
              <a class="btn btn-danger" href="{{ route('production.index') }}">Discard</a>
              <button type="submit" class="btn btn-primary">Create</button>
            </footer>
          </section>
        </div>

      </div>
    </form>
  </div>
  <script>
    var index = 1;
    const allProducts = @json($allProducts);

    function removeRow(button) {
      const tableRows = $("#PurPOTbleBody tr").length;
      if (tableRows > 1) {
        const row = button.closest('tr');
        row.remove();
        index--;
        tableTotal();
      }
    }

    function addNewRow() {
      const lastRow = $('#PurPOTbleBody tr:last');
      const latestValue = lastRow.find('select').val();

      if (latestValue !== "") {
        const table = document.getElementById('myTable').getElementsByTagName('tbody')[0];
        const newRow = table.insertRow();
        newRow.classList.add('item-row');

        const options = allProducts.map(p =>
          `<option value="${p.id}" data-unit="${p.unit ?? ''}">${p.name}</option>`
        ).join('');


        newRow.innerHTML = `
          <td>
            <select data-plugin-selecttwo name="item_details[${index}][product_id]" required id="productSelect_${index}" class="form-control select2-js" onchange="onItemChange(this)">
              <option value="" disabled selected>Select Raw</option>
              ${options}
            </select>
          </td>
          <td>
            <select name="item_details[${index}][variation_id]" id="variationSelect${index}" class="form-control select2-js">
              <option value="" selected disabled>Select Variation</option>
            </select>
          </td>
          <td>
            <select name="item_details[${index}][invoice_id]" id="invoiceSelect${index}" class="form-control" onchange="onInvoiceChange(this)">
              <option value=""  selected>Select Invoice</option>
            </select>
          </td>
          <td><input type="text" name="item_details[${index}][desc]" id="item_desc_${index}" class="form-control" placeholder="Description"/></td>
          <td><input type="number" name="item_details[${index}][item_rate]" id="item_rate_${index}" step="any" value="0" onchange="rowTotal(${index})" class="form-control" required/></td>
          <td><input type="number" name="item_details[${index}][qty]" id="item_qty_${index}" step="any" value="0" onchange="rowTotal(${index})" class="form-control" required/></td>
          <td>
            <select id="item_unit_${index}" class="form-control" name="item_details[${index}][item_unit]" required>
              <option value="" disabled selected>Select Unit</option>
              @foreach($units as $unit)
                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
              @endforeach              
            </select>
          </td>
          <td><input type="number" id="item_total_${index}" class="form-control" placeholder="Total" disabled/></td>
          <td width="5%"><button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button></td>
        `;

        index++;
        $('#myTable select[data-plugin-selecttwo]').select2();
      }
    }

    function rowTotal(i) {
      const rate = parseFloat($(`#item_rate_${i}`).val()) || 0;
      const qty = parseFloat($(`#item_qty_${i}`).val()) || 0;
      const total = rate * qty;

      $(`#item_total_${i}`).val(total.toFixed(2));
      tableTotal();
    }

    function tableTotal() {
      let totalQty = 0;
      let totalAmt = 0;

      $('#PurPOTbleBody tr').each(function () {
        const rate = parseFloat($(this).find('input[id^="item_rate_"]').val()) || 0;
        const qty = parseFloat($(this).find('input[id^="item_qty_"]').val()) || 0;
        totalQty += qty;
        totalAmt += rate * qty;
      });

      $('#total_fab').val(totalQty);
      $('#total_fab_amt').val(totalAmt.toFixed(2));

      recalcSummary(); // call summary again
    }

    function updateNetTotal(total) {
      const net = parseFloat(total) || 0;
      $('#netTotal').text(formatNumberWithCommas(net.toFixed(0)));
    }

    function formatNumberWithCommas(x) {
      return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    // 🔹 When product changes
    function onItemChange(select) {
        const row = select.closest('tr');
        const itemId = select.value;
        if (!row || !itemId) return;

        // --- Reset variation dropdown ---
        const variationSelect = row.querySelector(`select[id^="variationSelect"]`);
        variationSelect.innerHTML = `<option value="" disabled selected>Loading...</option>`;

        // --- Reset invoice dropdown ---
        const invoiceSelect = row.querySelector(`select[id^="invoiceSelect"]`);
        invoiceSelect.innerHTML = `<option value="" selected>Select Invoice</option>`;

        // --- Reset qty, rate, total ---
        row.querySelector(`input[id^="item_qty_"]`).value = '';
        row.querySelector(`input[id^="item_rate_"]`).value = '';
        row.querySelector(`input[id^="item_total_"]`).value = '';

        // --- Auto-fill unit dropdown ---
        const unitSelect = row.querySelector(`select[id^="item_unit_"]`);
        if (unitSelect) {
            const selectedOption = select.options[select.selectedIndex];
            const unitId = selectedOption.getAttribute("data-unit");

            if (unitId) {
                unitSelect.value = unitId;
                $(unitSelect).select2({ width: '100%' });
            }
        }

        // --- Fetch variations for this product ---
        fetch(`/product/${itemId}/variations`)
            .then(res => res.json())
            .then(data => {
                variationSelect.innerHTML = `<option value="" disabled selected>Select Variation</option>`;
                if (data.success && data.variation.length) {
                    data.variation.forEach(v => {
                        variationSelect.innerHTML += `<option value="${v.id}" data-product-id="${itemId}">${v.sku}</option>`;
                    });
                } else {
                    variationSelect.innerHTML = `<option value="">No Variations</option>`;
                }

                $(variationSelect).select2({ width: '100%' });
            })
            .catch(() => {
                variationSelect.innerHTML = `<option value="">Error loading variations</option>`;
            });

        // --- Fetch invoices for this product ---
        fetchInvoices(itemId, row);
    }

    // 🔹 When variation changes
    function onVariationChange(select) {
      const row = select.closest('tr');
      const variationId = select.value;
      const productId = select.selectedOptions[0]?.getAttribute('data-product-id');

      if (!variationId || !productId) return;

      // Fetch invoices using variation id if selected, else product id
      fetchInvoices(variationId, row, true);
    }

    function fetchInvoices(productId, row, variationId = null) {
        const invoiceSelect = row.querySelector(`select[id^="invoiceSelect"]`);
        invoiceSelect.innerHTML = `<option value="" disabled selected>Loading...</option>`;

        let url = `/product/${productId}/invoices`;
        if (variationId) url += `/${variationId}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                invoiceSelect.innerHTML = `<option value="" selected>Select Invoice</option>`;

                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(inv => {
                        invoiceSelect.innerHTML += `<option value="${inv.id}" data-rate="${inv.rate}">INV-${inv.id}</option>`;
                    });
                } else {
                    invoiceSelect.innerHTML = `<option value="">No Invoices Found</option>`;
                }

                $(invoiceSelect).select2({ width: '100%' });
            })
            .catch(() => {
                invoiceSelect.innerHTML = `<option value="">Error loading invoices</option>`;
            });
    }

    // 🔹 When invoice changes
    function onInvoiceChange(select) {
      const row = select.closest('tr');
      const option = select.selectedOptions[0];
      if (!row || !option) return;

      const rate = option.getAttribute('data-rate') || 0;

      const rateInput = row.querySelector(`input[id^="item_rate_"]`);
      const qtyInput = row.querySelector(`input[id^="item_qty_"]`);
      const totalInput = row.querySelector(`input[id^="item_total_"]`);

      if (rateInput) rateInput.value = rate;
      if (qtyInput && totalInput) {
        totalInput.value = ((parseFloat(qtyInput.value) || 0) * (parseFloat(rate) || 0)).toFixed(2);
      }

      tableTotal();
    }

    $(document).on("click", ".delete-row", function () {
      $(this).closest("tr").remove();
    });

    $(document).ready(function () {
      $('.select2-js').select2();
    });

    $(document).ready(function () {

      // Initialize Select2
      $('.select2-js').select2({ width: '100%', dropdownAutoWidth: true });

      // 🔹 Manual Product selection
      $(document).on('change', '.product-select', function () {
          const row = $(this).closest('tr');
          const productId = $(this).val();
          const preselectVariationId = $(this).data('preselectVariationId') || null;
          $(this).removeData('preselectVariationId');

          if (productId) {
              loadVariations(row, productId, preselectVariationId);
          } else {
              row.find('.variation-select')
                .html('<option value="">Select Variation</option>')
                .prop('disabled', false)
                .trigger('change');
          }
      });

      // 🔹 Recalc row on quantity input
      $(document).on('input', '.order-qty, .consumption, .manufacturing_cost', function () {
          const row = $(this).closest('tr');
          recalcRow(row);
          recalcSummary();
      });

      // 🔹 Auto-add row on Enter key
      $(document).on('keypress', '.order-qty', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            const qty = $(this).val().trim();
            if (qty !== '') {
                addRow();
            } else {
                alert('Enter quantity first');
                $(this).focus();
            }
        }
      });

      // 🔹 Remove row button
      $(document).on('click', '.remove-row-btn', function () {
          $(this).closest('tr').remove();
          recalcSummary();
      });

      // 🔹 Add row button
      $('#addRowBtn').on('click', addRow);
    });

    // 🔹 Add new Finished Goods row
    function addRow() {
        const rowCount = $('#itemTable tbody tr').length;

        const productOptions = `
            <option value="">Select Item</option>
            @foreach($allProducts as $item)
              <option value="{{ $item->id }}" 
                      data-consumption="{{ $item->consumption }}"
                      data-mfg-cost="{{ $item->manufacturing_cost }}">
                {{ $item->name }}
              </option>
            @endforeach
        `;

        const $newRow = $(`
            <tr>
                <td>
                    <select name="product_details[${rowCount}][product_id]" class="form-control select2-js product-select" required>
                        ${productOptions}
                    </select>
                </td>
                <td>
                    <select name="product_details[${rowCount}][variation_id]" class="form-control select2-js variation-select">
                        <option value="">Select Variation</option>
                    </select>
                </td>
                <td><input type="number" class="form-control consumption" name="product_details[${rowCount}][consumption]" step="any" value="0" required></td>
                <td><input type="number" class="form-control manufacturing_cost" name="product_details[${rowCount}][manufacturing_cost]" step="any" value="0"></td>
                <td><input type="number" class="form-control order-qty" name="product_details[${rowCount}][order_qty]" step="any" value="0" required></td>
                <td><input type="text" class="form-control" name="product_details[${rowCount}][remarks]"></td>
                <td><input type="number" class="form-control raw_use" name="product_details[${rowCount}][raw_use]" step="any" value="0" readonly></td>
                <td><input type="number" class="form-control total_amount" name="product_details[${rowCount}][total]" step="any" value="0" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row-btn"><i class="fas fa-times"></i></button></td>
            </tr>
        `);

        $('#itemTable tbody').append($newRow);

        // Reinitialize Select2
        $newRow.find('.select2-js').select2({ width: '100%', dropdownAutoWidth: true });
    }

    // 🔹 Bind variation change to recalc / invoices
    $(document).on('change', '.variation-select', function() {
        onVariationChange(this); // existing function
        const row = $(this).closest('tr');
        recalcRow(row);
        recalcSummary();
    });

    // 🔹 Recalc row totals on input
    $(document).on('input', '.order-qty, .consumption, .manufacturing_cost', function () {
        const row = $(this).closest('tr');
        recalcRow(row);
        recalcSummary();
    });

    // 🔹 Remove Finished Goods row
    $(document).on('click', '.remove-row-btn', function () {
        $(this).closest('tr').remove();
        recalcSummary();
    });

    // 🔹 Load variations for product, set manufacturing cost and consumption
    function loadVariations(row, productId, preselectVariationId = null) {
        const $variationSelect = row.find('.variation-select');
        const $mCostInput = row.find('.manufacturing_cost');
        const $consumption = row.find('.consumption');

        $variationSelect.html('<option value="">Loading...</option>').prop('disabled', false);

        $.get(`/product/${productId}/variations`, function (data) {
            let options = '<option value="">Select Variation</option>';

            (data.variation || []).forEach(v => {
                options += `<option value="${v.id}">${v.sku}</option>`;
            });

            $variationSelect.html(options).prop('disabled', false);

            if ($variationSelect.hasClass('select2-hidden-accessible')) {
                $variationSelect.select2('destroy');
            }
            $variationSelect.select2({ width: '100%', dropdownAutoWidth: true });

            // Set manufacturing cost and consumption
            if (data.product && data.product.manufacturing_cost !== undefined) {
                $mCostInput.val(parseFloat(data.product.manufacturing_cost).toFixed(2));
            }

            if (data.product && data.product.consumption !== undefined) {
                $consumption.val(parseFloat(data.product.consumption).toFixed(2));
            }

            // Preselect variation if provided
            if (preselectVariationId) {
                $variationSelect.val(String(preselectVariationId)).trigger('change');
            }

            recalcRow(row);
            recalcSummary();
        });
    }

    // 🔹 Recalculate Finished Goods row totals
    function recalcRow(row) {
        const orderQty = parseFloat(row.find('.order-qty').val()) || 0;
        const consumption = parseFloat(row.find('.consumption').val()) || 0;
        const mCost = parseFloat(row.find('.manufacturing_cost').val()) || 0;

        const rawUse = orderQty * consumption;
        const total = orderQty * mCost;

        row.find('.raw_use').val(rawUse.toFixed(2));
        row.find('.total_amount').val(total.toFixed(2));
    }

    // 🔹 Recalculate summary totals
    function recalcSummary() {
        let totalRawUse = 0, productTotalAmt = 0;

        $('#itemTable tbody tr').each(function () {
            const rawUse = parseFloat($(this).find('.raw_use').val()) || 0;
            const total = parseFloat($(this).find('.total_amount').val()) || 0;

            totalRawUse += rawUse;
            productTotalAmt += total;
        });

        // Fill values in summary
        $('#total_raw_use').val(totalRawUse.toFixed(2));
        $('#product_total_amt').val(productTotalAmt.toFixed(2));

        // Remaining raw = Total raw qty - Total raw use
        const totalRawQty = parseFloat($('#total_fab').val()) || 0;
        const remainingRaw = totalRawQty - totalRawUse;
        $('#remaining_raw').val(remainingRaw.toFixed(2));

        // Net amount = Product total amount
        $('#netTotal').text(formatNumberWithCommas(productTotalAmt.toFixed(0)));
        $('#net_amount').val(productTotalAmt.toFixed(2));
    }
  </script>

@endsection
