@extends('layouts.app')

@section('title', 'Production | Edit Order')

@section('content')
  <div class="row">
    <form id="productionForm" action="{{ route('production.update', $production->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

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
        <!-- Master Details -->
        <div class="col-12 col-md-12 mb-3">
          <section class="card">
            <header class="card-header d-flex justify-content-between">
              <h2 class="card-title">Edit Production</h2>
            </header>
            <div class="card-body">
              <div class="row">
                <div class="col-12 col-md-2 mb-3">
                  <label>Production #</label>
                  <input type="text" class="form-control" value="{{ $production->production_code }}" disabled/>
                </div>

                <div class="col-12 col-md-2 mb-3">
                  <label>Vendor Name</label>
                  <select data-plugin-selecttwo class="form-control select2-js" name="vendor_id" id="vendor_name" required>
                    <option value="" disabled>Select Vendor</option>
                    @foreach($vendors as $item)
                      <option value="{{ $item->id }}" {{ $item->id == $production->vendor_id ? 'selected' : '' }}>
                        {{ $item->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-12 col-md-2 mb-3">
                  <label>Order Date</label>
                  <input type="date" name="order_date" class="form-control" value="{{ $production->order_date->format('Y-m-d') }}" required/>
                </div>
              </div>
            </div>
          </section>
        </div>

        <!-- Raw Material Details -->
        <div class="col-12 col-md-12 mb-3">
          <section class="card">
            <header class="card-header d-flex justify-content-between">
              <h2 class="card-title">Raw Details</h2>
            </header>
            <div class="card-body">
              <table class="table table-bordered" id="myTable">
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
                <tbody id="PurPOTbleBody">
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
                          <option value="{{ $item->invoice_id }}" data-rate="{{ $item->invoice->rate }}" selected>
                            INV-{{ $item->invoice->id }}
                          </option>
                        @else
                          <option value="">Select Invoice</option>
                        @endif
                      </select>
                    </td>
                    <td><input type="text" name="item_details[{{ $k }}][desc]" value="{{ $item->desc }}" class="form-control"/></td>
                    <td><input type="number" name="item_details[{{ $k }}][item_rate]" id="item_rate_{{ $k }}" value="{{ $item->item_rate }}" step="any" class="form-control" onchange="rowTotal({{ $k }})"/></td>
                    <td><input type="number" name="item_details[{{ $k }}][qty]" id="item_qty_{{ $k }}" value="{{ $item->qty }}" step="any" class="form-control" onchange="rowTotal({{ $k }})"/></td>
                    <td>
                      <select id="item_unit_{{ $k }}" class="form-control" name="item_details[{{ $k }}][item_unit]" required>
                        @foreach ($units as $unit)
                          <option value="{{ $unit->id }}" {{ $unit->id == $item->item_unit ? 'selected' : '' }}>
                            {{ $unit->name }}
                          </option>
                        @endforeach
                      </select>
                    </td>
                    <td><input type="number" id="item_total_{{ $k }}" value="{{ $item->item_rate * $item->qty }}" class="form-control" readonly/></td>
                    <td><button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button></td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
              <button type="button" class="btn btn-success btn-sm mt-2" onclick="addNewRow()">+ Add Row</button>
            </div>
          </section>
        </div>

        <!-- Product Details -->
        <div class="col-12 mb-4">
          <section class="card">
            <header class="card-header">
              <h2 class="card-title">Product Details</h2>
            </header>
            <div class="card-body">
              <table class="table table-bordered" id="itemTable">
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
                          <option value="{{ $item->id }}" {{ $item->id == $pd->product_id ? 'selected' : '' }}>
                            {{ $item->name }}
                          </option>
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
                    <td><input type="number" class="form-control" value="{{ $pd->order_qty * $pd->manufacturing_cost }}" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row-btn"><i class="fas fa-times"></i></button></td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
              <button type="button" class="btn btn-success btn-sm mt-2" id="addRowBtn">+ Add Row</button>
            </div>
          </section>
        </div>

        <!-- Summary -->
        <div class="col-12">
          <section class="card">
            <header class="card-header d-flex justify-content-between">
              <h2 class="card-title">Summary</h2>
            </header>
            <div class="card-body">
              <div class="row pb-4">
                <div class="col-12 col-md-2">
                  <label>Total Raw Qty</label>
                  <input type="number" class="form-control" id="total_fab" disabled/>
                </div>
                <div class="col-12 col-md-2">
                  <label>Total Raw Amount</label>
                  <input type="number" class="form-control" id="total_fab_amt" disabled/>
                </div>
                <div class="col-12 col-md-2">
                  <label>Total Raw Use</label>
                  <input type="number" class="form-control" id="total_raw_use" disabled/>
                </div>
                <div class="col-12 col-md-2">
                  <label>Remaining Raw</label>
                  <input type="number" class="form-control" id="remaining_raw" disabled/>
                </div>
                <div class="col-12 col-md-2">
                  <label>Products Total Amount</label>
                  <input type="number" class="form-control" id="product_total_amt" disabled/>
                </div>
                <div class="col-12 text-end mt-3">
                  <h3 class="font-weight-bold mb-0 text-5 text-primary">Net Amount</h3>
                  <span><strong class="text-4 text-primary">PKR <span id="netTotal" class="text-4 text-danger">0.00</span></strong></span>
                  <input type="hidden" name="total_amount" id="net_amount">
                </div>
              </div>
            </div>
            <footer class="card-footer text-end">
              <a class="btn btn-danger" href="{{ route('production.index') }}">Discard</a>
              <button type="submit" class="btn btn-primary">Update</button>
            </footer>
          </section>
        </div>
      </div>
    </form>
  </div>

  <script>
      var index = {{ isset($production) && $production->productDetails ? $production->productDetails->count() : 1 }};
      const allProducts = @json($allProducts);

      $(document).ready(function () {
          // initialize select2
          $('.select2-js').select2({ width: '100%', dropdownAutoWidth: true });

          // loop through existing rows and reload variations + invoices
          @if(isset($production) && $production->productDetails)
              @foreach($production->productDetails as $i => $detail)
                  loadVariations(
                      $(`#PurPOTbleBody tr`).eq({{ $i }}),
                      {{ $detail->product_id }},
                      {{ $detail->variation_id ?? 'null' }},
                      {{ $detail->invoice_id ?? 'null' }}
                  );
              @endforeach
          @endif

          tableTotal();
      });

      // 🔹 add new row
      function addNewRow() {
          const table = document.getElementById('myTable').getElementsByTagName('tbody')[0];
          const newRow = table.insertRow();
          newRow.classList.add('item-row');

          const options = allProducts.map(p =>
              `<option value="${p.id}" data-unit="${p.unit ?? ''}">${p.name}</option>`
          ).join('');

          newRow.innerHTML = `
            <td>
              <select data-plugin-selecttwo name="item_details[${index}][product_id]" required id="productSelect_${index}" 
                  class="form-control select2-js" onchange="onItemChange(this)">
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
              <select name="item_details[${index}][invoice_id]" id="invoiceSelect${index}" 
                  class="form-control" onchange="onInvoiceChange(this)">
                <option value="" selected>Select Invoice</option>
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
          $('#myTable select[data-plugin-selecttwo]').select2({ width: '100%' });
      }

      // 🔹 load variations + invoices for existing rows
      function loadVariations($row, productId, preselectVariationId = null, preselectInvoiceId = null) {
          const variationSelect = $row.find('select[id^="variationSelect"]');
          const invoiceSelect = $row.find('select[id^="invoiceSelect"]');

          // load variations
          $.get(`/product/${productId}/variations`, function (data) {
              variationSelect.html('<option value="">Select Variation</option>');
              if (data.variation && data.variation.length) {
                  data.variation.forEach(v => {
                      variationSelect.append(`<option value="${v.id}">${v.sku}</option>`);
                  });
              } else {
                  variationSelect.html('<option value="">No Variations</option>');
              }

              if (preselectVariationId) {
                  variationSelect.val(preselectVariationId).trigger('change');
              }

              variationSelect.select2({ width: '100%' });
          });

          // load invoices
          $.get(`/product/${productId}/invoices`, function (data) {
              invoiceSelect.html('<option value="">Select Invoice</option>');
              if (Array.isArray(data) && data.length > 0) {
                  data.forEach(inv => {
                      invoiceSelect.append(`<option value="${inv.id}" data-rate="${inv.rate}">INV-${inv.id}</option>`);
                  });
              } else {
                  invoiceSelect.html('<option value="">No Invoices Found</option>');
              }

              if (preselectInvoiceId) {
                  invoiceSelect.val(preselectInvoiceId).trigger('change');
              }

              invoiceSelect.select2({ width: '100%' });
          });
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
      // keep your rowTotal(), tableTotal(), onItemChange(), onInvoiceChange() as before...
  </script>

@endsection
