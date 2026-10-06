<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 30px 34px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #17213a; font: 10px DejaVu Sans, sans-serif; }
        .watermark { position: fixed; top: 340px; left: 44px; width: 500px; color: #c29a2f; font-size: 33px; font-weight: bold; opacity: .12; transform: rotate(-31deg); }
        .grid { width: 100%; border-collapse: collapse; }
        .grid td { vertical-align: top; }
        .header { border-bottom: 4px solid #1d4ed8; padding-bottom: 15px; }
        .brand { font-size: 26px; font-weight: bold; color: #0f254d; }
        .tag { margin-top: 3px; color: #1d4ed8; font-size: 8px; font-weight: bold; letter-spacing: 1.1px; }
        .right { text-align: right; }
        .title { font-size: 18px; font-weight: bold; color: #0f254d; }
        .muted { color: #64748b; line-height: 1.55; }
        .notice { margin: 15px 0; padding: 8px 10px; border: 1px solid #f0c45d; background: #fff8df; color: #735700; font-size: 8px; font-weight: bold; }
        .label { margin-bottom: 5px; color: #1d4ed8; font-size: 8px; font-weight: bold; letter-spacing: .7px; }
        .box { min-height: 114px; border: 1px solid #d8e1ed; border-top: 3px solid #1d4ed8; padding: 10px; line-height: 1.6; }
        .items { width: 100%; margin-top: 18px; border-collapse: collapse; }
        .items th { padding: 9px; background: #10264a; color: #dbeafe; font-size: 8px; letter-spacing: .5px; text-align: left; }
        .items td { padding: 10px 8px; border-bottom: 1px solid #dde5f0; }
        .amount { text-align: right; white-space: nowrap; }
        .tax { width: 48%; margin: 16px 0 0 auto; border-collapse: collapse; }
        .tax td { padding: 5px 7px; border-bottom: 1px solid #e4eaf2; }
        .tax .grand td { padding: 9px 7px; background: #10264a; color: #fff; font-size: 12px; font-weight: bold; }
        .bank { margin-top: 24px; padding: 11px; border: 1px solid #cad8ea; background: #f7faff; line-height: 1.7; }
        .terms { margin-top: 18px; padding-top: 10px; border-top: 1px solid #d8e1ed; color: #57657b; font-size: 8px; line-height: 1.6; }
        .authorisation { margin-top: 24px; text-align: right; color: #17213a; line-height: 1.45; }
        .stamp { display: block; width: 88px; height: auto; margin: 5px 0 4px auto; opacity: .92; }
        .signatory-name { font-size: 9px; font-weight: bold; }
        .foot { margin-top: 20px; padding-top: 8px; border-top: 1px solid #d8e1ed; color: #7b879d; font-size: 7px; }
    </style>
</head>
<body>
@if($document['isProvisional'])
    <div class="watermark">PROVISIONAL - NOT A TAX INVOICE</div>
@endif

@php($issuer = $document['issuer'])
@php($tax = $document['tax'])
@php($snapshot = $document['snapshot'])
@php($profile = $document['clientProfile'])

<table class="grid header">
    <tr>
        <td width="58%">
            <div class="brand">{{ $issuer['brand'] }}</div>
            <div class="tag">TALENT ACQUISITION &amp; RECRUITMENT SERVICES</div>
        </td>
        <td class="right">
            <div class="title">{{ $document['isProvisional'] ? 'PROVISIONAL INVOICE' : 'TAX INVOICE' }}</div>
            <div class="muted" style="margin-top:4px">
                Invoice no: <strong>{{ $document['invoiceNumber'] }}</strong><br>
                Invoice date: {{ $document['invoiceDate']->format('d M Y') }}<br>
                Due date: {{ $document['dueDate']->format('d M Y') }}
            </div>
        </td>
    </tr>
</table>

@if($document['isProvisional'])
    <div class="notice">GSTIN and PAN for SimplyHiree have not yet been configured. This preview is not valid for GST input credit or statutory accounting. Add those verified details before issuing the final tax invoice.</div>
@endif

<table class="grid">
    <tr>
        <td width="48%">
            <div class="label">Supplier</div>
            <div class="box">
                <strong>{{ $issuer['legal_name'] }}</strong><br>
                {{ $issuer['office_address'] }}<br>
                State: {{ $issuer['state'] }} (Code: {{ $issuer['state_code'] }})<br>
                GSTIN: {{ $issuer['gstin'] ?: 'Pending configuration' }}<br>
                PAN: {{ $issuer['pan'] ?: 'Pending configuration' }}<br>
                {{ $issuer['email'] }} | {{ $issuer['phone'] }}
            </div>
        </td>
        <td width="4%"></td>
        <td width="48%">
            <div class="label">Recipient / Bill to</div>
            <div class="box">
                <strong>{{ $profile?->company_name ?: $document['application']->job?->user?->name }}</strong><br>
                {{ $profile?->address ?: 'Address not provided' }}<br>
                {{ $profile?->city }}{{ $profile?->state ? ', '.$profile->state : '' }}{{ $profile?->pincode ? ' - '.$profile->pincode : '' }}<br>
                Place of supply: {{ $tax['recipient_state'] }} (Code: {{ $tax['recipient_state_code'] }})<br>
                GSTIN: {{ $profile?->gst_number ?: 'Not provided' }}<br>
                {{ $document['application']->job?->user?->email }}
            </div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th width="7%">#</th><th width="43%">DESCRIPTION OF SERVICE</th><th width="11%">SAC</th><th width="9%">QTY</th><th width="15%" class="amount">TAXABLE VALUE</th><th width="15%" class="amount">AMOUNT</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td><strong>{{ $document['serviceDescription'] }}</strong><br><span class="muted">Candidate: {{ $snapshot['candidate_name'] }} | Role: {{ $snapshot['job_title'] }}<br>Joining: {{ $snapshot['joining_date']?->format('d M Y') }} | Final CTC: INR {{ number_format((float) $snapshot['final_ctc'], 2) }}</span></td>
            <td>{{ $document['sac'] }}</td><td>1</td>
            <td class="amount">INR {{ number_format((float) $snapshot['invoice_amount'], 2) }}</td>
            <td class="amount">INR {{ number_format((float) $snapshot['invoice_amount'], 2) }}</td>
        </tr>
    </tbody>
</table>

<table class="tax">
    <tr><td>Taxable value</td><td class="amount">INR {{ number_format((float) $snapshot['invoice_amount'], 2) }}</td></tr>
    @if($tax['type'] === 'intra_state')
        <tr><td>CGST @ {{ number_format($tax['cgst_rate'], 2) }}%</td><td class="amount">INR {{ number_format($tax['cgst_amount'], 2) }}</td></tr>
        <tr><td>SGST / UTGST @ {{ number_format($tax['sgst_rate'], 2) }}%</td><td class="amount">INR {{ number_format($tax['sgst_amount'], 2) }}</td></tr>
    @elseif($tax['type'] === 'inter_state')
        <tr><td>IGST @ {{ number_format($tax['igst_rate'], 2) }}%</td><td class="amount">INR {{ number_format($tax['igst_amount'], 2) }}</td></tr>
    @else
        <tr><td>GST</td><td class="amount">Not applicable</td></tr>
    @endif
    <tr class="grand"><td>Invoice total</td><td class="amount">INR {{ number_format($tax['grand_total'], 2) }}</td></tr>
</table>

<div class="bank">
    <strong>Payment details</strong><br>
    Account name: {{ $issuer['bank_account_name'] }}<br>
    Bank: {{ $issuer['bank_name'] }} | Account no.: {{ $issuer['bank_account_number'] }} | IFSC: {{ $issuer['bank_ifsc'] }}<br>
    Payment terms: Net {{ $document['paymentTermsDays'] }} days from invoice date. Reverse charge: No.
</div>

<div class="terms"><strong>Notes:</strong> This invoice relates to recruitment and placement services. Tax has been calculated as CGST + SGST/UTGST where the recipient's place of supply matches the supplier's state; otherwise IGST is shown. SAC classification and final tax treatment must be approved by SimplyHiree's finance/tax adviser before issue.</div>

<div class="authorisation">
    <div>For {{ $issuer['legal_name'] }}</div>
    @if(!empty($document['stampDataUri']))
        <img class="stamp" src="{{ $document['stampDataUri'] }}" alt="Simply Hiree company stamp">
    @endif
    <div class="signatory-name">{{ $issuer['authorised_signatory_name'] }}</div>
    <strong>Authorised signatory</strong>
</div>

<div class="foot">System-generated from SimplyHiree Billing on {{ now()->format('d M Y, h:i A') }} IST.</div>
</body>
</html>
