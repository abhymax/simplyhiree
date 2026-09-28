<!DOCTYPE html><html><head><meta charset="utf-8">
<style>
  @page { margin: 0; }
  body { font-family: DejaVu Sans, sans-serif; color:#1f2937; font-size:12px; line-height:1.6; margin:0; }
  .page { padding: 42px 48px 120px; }
  .lh { border-bottom:2px solid #4f46e5; padding-bottom:12px; margin-bottom:22px; }
  .lh table{ width:100%; }
  .lh .logo{ height:46px; }
  .lh .co{ text-align:right; font-size:11px; color:#475569; }
  .lh .co b{ color:#4f46e5; font-size:15px; }
  .meta{ color:#6b7280; font-size:11px; margin-bottom:14px; }
  h1.subj{ font-size:15px; color:#111827; margin:0 0 14px; }
  .body{ font-size:12.5px; }
  .sign{ margin-top:38px; }
  .sign img{ height:52px; }
  .sign .nm{ font-weight:bold; margin-top:4px; }
  .sign .dg{ color:#6b7280; font-size:11px; }
  .foot{ position:fixed; bottom:0; left:0; right:0; border-top:1px solid #e5e7eb; padding:10px 48px; font-size:9.5px; color:#94a3b8; text-align:center; }
</style></head>
<body>
<div class="page">
  <div class="lh"><table><tr>
    <td style="width:55%;">@if($logo)<img src="{{ $logo }}" class="logo">@else<span style="font-weight:bold;color:#4f46e5;font-size:18px;">SimplyHiree</span>@endif</td>
    <td class="co">@if($settings->company_address){!! nl2br(e($settings->company_address)) !!}@endif</td>
  </tr></table></div>

  <div class="meta">Date: {{ now()->format('d M Y') }}</div>
  <h1 class="subj">{{ $subject }}</h1>
  <div class="body">{!! $bodyHtml !!}</div>

  <div class="sign">
    @if($signature)<img src="{{ $signature }}"><br>@endif
    <div class="nm">{{ $settings->director_name ?: 'Aman Yadav' }}</div>
    <div class="dg">{{ $settings->director_designation ?: 'Director' }}, SimplyHiree</div>
  </div>
</div>
@if($settings->company_footer)<div class="foot">{!! nl2br(e($settings->company_footer)) !!}</div>@endif
</body></html>
