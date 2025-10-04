@extends('layouts.app')

@section('title', 'Purchases | All Invoices')

@section('content')
  <div class="row">
    <div class="col">
      <section class="card">
        @if (session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @elseif (session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <header class="card-header d-flex justify-content-between align-items-center">
          <h2 class="card-title">All Purchase Invoices</h2>
          <a href="{{ route('purchase_invoices.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Purchase Invoice
          </a>
        </header>

        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped" id="purchaseInvoiceTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Invoice Date</th>
                  <th>Vendor</th>
                  <th>Bill No</th>
                  <th>Ref No</th>
                  <th>Attachments</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($invoices as $index => $invoice)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-M-Y') }}</td>
                  <td>{{ $invoice->vendor->name ?? 'N/A' }}</td>
                  <td>{{ $invoice->bill_no }}</td>
                  <td>{{ $invoice->ref_no }}</td>
                  <td style="vertical-align: middle;">
                    <a class="mb-1 mt-1 me-1 modal-with-zoom-anim ws-normal text-dark" 
                      onclick="getAttachments({{ $invoice->id }})" href="#attModal">
                      <i class="fa fa-eye"></i>
                    </a>
                    <span class="separator"> | </span>
                    <a class="mb-1 mt-1 me-1 modal-with-zoom-anim ws-normal text-danger" 
                      onclick="setAttId({{ $invoice->id }})" href="#addAttModal">
                      <i class="fas fa-paperclip"></i>
                    </a>
                  </td>
                  <td>
                    <a href="{{ route('purchase_invoices.edit', $invoice->id) }}" class="text-primary"><i class="fas fa-edit"></i></a>
                    <a href="{{ route('purchase_invoices.print', $invoice->id) }}" target="_blank" class="text-success"><i class="fas fa-print"></i></a>
                    <form action="{{ route('purchase_invoices.destroy', $invoice->id) }}" method="POST" style="display:inline;">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-link p-0 m-0 text-danger" onclick="return confirm('Are you sure?')">
                        <i class="fa fa-trash-alt"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>
  </div>

  {{-- Attachments Modal --}}
  <div id="attModal" class="zoom-anim-dialog modal-block modal-block-danger mfp-hide">
    <section class="card">
      <header class="card-header">
        <h2 class="card-title">All Attachments</h2>
      </header>
      <div class="card-body">
        <table class="table table-bordered table-striped mb-0">
          <thead>
            <tr>
              <th>Attachment Path</th>
              <th>Download</th>
              <th>View</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody id="invoice_attachments">
          </tbody>
        </table>
      </div>
      <footer class="card-footer">
        <div class="row">
          <div class="col-md-12 text-end">
            <button class="btn btn-default modal-dismiss">Cancel</button>
          </div>
        </div>
      </footer>
    </section>
  </div>

  {{-- Upload Attachment Modal --}}
  <div id="addAttModal" class="zoom-anim-dialog modal-block modal-block-danger mfp-hide">
    <form method="POST" action="#" enctype="multipart/form-data" id="addAttachmentForm">
      @csrf  
      <section class="card">
        <header class="card-header">
          <h2 class="card-title">Upload Attachments</h2>
        </header>
        <div class="card-body">
          <div class="modal-wrapper">
            <div class="col-lg-12 mb-2">
              <input type="file" class="form-control" name="attachments[]" multiple accept="application/pdf,image/png,image/jpeg" required>
              <input type="hidden" name="invoice_id" id="att_id">
            </div>
          </div>
        </div>
        <footer class="card-footer">
          <div class="row">
            <div class="col-md-12 text-end">
              <button type="submit" class="btn btn-danger">Upload</button>
              <button type="button" class="btn btn-default modal-dismiss">Cancel</button>
            </div>
          </div>
        </footer>
      </section>
    </form>
  </div>

  <script>
    $(document).ready(function() {
      $('#purchaseInvoiceTable').DataTable({
        pageLength: 50,
        order: [[0, 'desc']],
      });
    });

    function getAttachments(invoiceId) {
      $('#invoice_attachments').html('<tr><td colspan="4">Loading...</td></tr>');

      $.get("/purchase_invoices/" + invoiceId + "/attachments", function(data) {
        let rows = '';
        if (data.length > 0) {
          data.forEach(function(att) {
            let downloadUrl = '/storage/' + att.file_path; // Path to storage
            let deleteUrl = '/purchase_invoices/attachments/' + att.id; // Delete route

            rows += `
              <tr>
                <td>${att.original_name}</td>
                <td><a href="${downloadUrl}" download>Download</a></td>
                <td><a href="${downloadUrl}" target="_blank">View</a></td>
                <td>
                  <form method="POST" action="${deleteUrl}" onsubmit="return confirm('Delete this attachment?')" style="display:inline;">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button class="btn btn-sm btn-link text-danger"><i class="fa fa-trash-alt"></i></button>
                  </form>
                </td>
              </tr>`;
          });
        } else {
          rows = '<tr><td colspan="4">No attachments found</td></tr>';
        }
        $('#invoice_attachments').html(rows);
      });
    }

    function setAttId(invoiceId) {
      $('#att_id').val(invoiceId);
      $('#addAttachmentForm').attr('action', '/purchase_invoices/' + invoiceId + '/attachments');
    }
  </script>
@endsection
