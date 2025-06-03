<div>
    <h1>Invoice</h1>
    <p>Invoice No: {{ $invoiceMain->invoice_number }}</p>
    <p>Invoice Date: {{ $invoiceMain->created_at->format('Y-m-d') }}</p>    
</div>