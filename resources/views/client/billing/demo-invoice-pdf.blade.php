<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 34px 36px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #17213a; font: 11px DejaVu Sans, sans-serif; }
        .watermark { position: fixed; top: 330px; left: 70px; width: 460px; color: #e7b642; font-size: 38px; font-weight: bold; opacity: .13; transform: rotate(-31deg); }
        .header { border-bottom: 4px solid #d9a92a; padding-bottom: 17px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid td { vertical-align: top; }
        .brand { color: #111c35; font-size: 27px; font-weight: bold; letter-spacing: 2px; }
        .tag { color: #a26b00; font-size: 9px; font-weight: bold; letter-spacing: 1.1px; margin-top: 3px; }
        .invoice-title { color: #17213a; font-size: 18px; font-weight: bold; text-align: right; }
        .muted { color: #63708a; line-height: 1.55; }
        .right { text-align: right; }
        .notice { background: #fff8df; border: 1px solid #e7c65d; color: #745100; padding: 9px 11px; margin: 18px 0; font-size: 9px; font-weight: bold; }
        .section-label { color: #9b700d; font-size: 9px; font-weight: bold; letter-spacing: .8px; text-transform: uppercase; margin-bottom: 6px; }
        .box { min-height: 112px; border: 1px solid #d9e0ec; border-top: 3px solid #314670; padding: 11px; line-height: 1.65; }
        .box strong { color: #17213a; }
        .items { width: 100%; border-collapse: collapse; margin-top: 22px; }
        .items th { background: #17213a; color: #f6d777; padding: 10px; font-size: 9px; letter-spacing: .7px; text-align: left; }
        .items td { border-bottom: 1px solid #dfe5ee; padding: 12px 10px; }
        .amount { text-align: right; white-space: nowrap; }
        .totals { width: 42%; margin-left: auto; margin-top: 18px; border-collapse: collapse; }
        .totals td { padding: 7px 8px; }
        .totals .grand td { background: #17213a; color: #fff; font-size: 13px; font-weight: bold; padding: 10px 8px; }
        .terms { border-top: 1px solid #d9e0ec; margin-top: 32px; padding-top: 13px; color: #5b6780; font-size: 9px; line-height: 1.6; }
        .signature { margin-top: 45px; text-align: right; color: #17213a; font-size: 10px; }
        .foot { border-top: 1px solid #d9e0ec; margin-top: 25px; padding-top: 9px; color: #7b879d; font-size: 8px; }
    </style>
</head>
<body>
    <div class="watermark">DEMO - NOT A TAX INVOICE</div>
    <table class="grid header"><tr>
        <td width="58%">
            <div class="brand">{{ $issuer['brand'] }}</div>
            <div class="tag">TALENT ACQUISITION &amp; RECRUITMENT SERVICES</div>
        </td>
        <td class="right">
            <div class="invoice-title">DEMO INVOICE</div>
            <div class="muted" style="margin-top:5px">Invoice no: <strong>{{ $invoiceNumber }}</strong><br>Issued: {{ now()->format('d M Y') }}<br>Currency: INR</div>
        </td>
    </tr></table>

    <div class="notice">DEMO DOCUMENT ONLY. The issuer name, GSTIN, address, bank details and this invoice number are placeholders. It is not valid for payment, GST input credit, accounting or tax filing.</div>

    <table class="grid"><tr>
        <td width="48%">
            <div class="section-label">Issued by</div>
            <div class="box"><strong>{{ $issuer['legal_name'] }}</strong><br>{{ $issuer['address'] }}<br>GSTIN (demo): {{ $issuer['gstin'] }}<br>{{ $issuer['email'] }}</div>
        </td>
        <td width="4%"></td>
        <td width="48%">
            <div class="section-label">Bill to</div>
            <div class="box"><strong>{{ $clientProfile?->company_name ?: $application->job?->user?->name }}</strong><br>{{ $clientProfile?->address ?: 'Client registered address not provided' }}<br>{{ $clientProfile?->city }}{{ $clientProfile?->state ? ', '.$clientProfile->state : '' }}{{ $clientProfile?->pincode ? ' - '.$clientProfile->pincode : '' }}<br>GSTIN: {{ $clientProfile?->gst_number ?: 'Not provided' }}<br>{{ $application->job?->user?->email }}</div>
        </td>
    </tr></table>

    <table class="items"><thead><tr><th width="56%">SERVICE DESCRIPTION</th><th width="14%">QTY</th><th width="15%" class="amount">RATE</th><th width="15%" class="amount">AMOUNT</th></tr></thead>
    <tbody><tr>
        <td><strong>Recruitment and placement service</strong><br><span class="muted">Candidate: {{ $invoice['candidate_name'] }} | Role: {{ $invoice['job_title'] }}<br>Joining date: {{ $invoice['joining_date']?->format('d M Y') }} | Commercial: {{ ucwords(str_replace('_', ' ', (string) $invoice['billing_type'])) }}</span></td>
        <td>1</td><td class="amount">₹{{ number_format((float) $invoice['invoice_amount'], 2) }}</td><td class="amount">₹{{ number_format((float) $invoice['invoice_amount'], 2) }}</td>
    </tr></tbody></table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="amount">₹{{ number_format((float) $invoice['invoice_amount'], 2) }}</td></tr>
        <tr><td>GST @ {{ number_format((float) $invoice['gst_rate'], 0) }}%</td><td class="amount">₹{{ number_format((float) $invoice['gst_amount'], 2) }}</td></tr>
        <tr class="grand"><td>DEMO TOTAL</td><td class="amount">₹{{ number_format((float) $invoice['invoice_total'], 2) }}</td></tr>
    </table>

    <div class="terms"><strong>Commercial note:</strong> GST treatment above follows the commercial flag currently saved against this placement. Final tax calculation must be verified by the issuer’s finance team before a real invoice is issued. Payment terms and bank instructions are intentionally omitted from this demo.</div>
    <div class="signature">For {{ $issuer['legal_name'] }}<br><br><strong>Authorised signatory (demo)</strong></div>
    <div class="foot">Generated from the SimplyHiree billing workspace on {{ now()->format('d M Y, h:i A') }} IST. Candidate and commercial data is shown for invoice-preview purposes only.</div>
</body>
</html>
