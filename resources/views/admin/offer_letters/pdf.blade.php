<!DOCTYPE html><html><head><meta charset="utf-8">
<style>
  @page { margin: 96px 56px 84px 56px; }
  body { font-family: DejaVu Sans, sans-serif; color:#1f2937; font-size:11.5px; line-height:1.55; }
  /* running header on every page */
  header { position: fixed; top: -70px; left:0; right:0; height:56px; }
  header .logo { height: 40px; }
  /* running footer on every page */
  footer { position: fixed; bottom: -64px; left:0; right:0; text-align:center; font-size:9px; color:#334155;
           border-top:1px solid #cbd5e1; padding-top:6px; font-weight:bold; }
  h1.title { text-align:center; color:#1d4ed8; font-size:15px; font-weight:bold; letter-spacing:1px; margin:0 0 14px; }
  .refrow { width:100%; margin-bottom:10px; font-size:11px; }
  .refrow td { padding:0; }
  .refrow .r { text-align:right; }
  /* section headings authored as <h2>/<h3> in the template body */
  .body h2, .body h3 { color:#1d4ed8; font-size:12px; font-weight:bold; border-bottom:1px solid #cbd5e1;
                       padding-bottom:3px; margin:16px 0 8px; }
  .body p { margin:6px 0; }
  .body table { width:100%; border-collapse:collapse; margin:8px 0; }
  .body table td, .body table th { border:1px solid #cbd5e1; padding:5px 8px; font-size:11px; vertical-align:top; }
  .body table th, .body table tr:first-child td { background:#eef2f7; font-weight:bold; }
  .body ul { margin:6px 0 6px 4px; padding-left:16px; }
  .body li { list-style-type: square; margin:3px 0; }
  .body b, .body strong { color:#111827; }
  .sigblock { margin-top:26px; }
</style></head>
<body>
  <header>
    @if($logo)
      <img src="{{ $logo }}" class="logo" alt="SimplyHiree">
    @else
      <span style="color:#1d4ed8;font-weight:bold;font-size:17px;">SIMPLY HIREE</span>
    @endif
  </header>
  <footer>
    @if($settings->company_footer)
      {!! nl2br(e($settings->company_footer)) !!}
    @else
      B-23, Sector 62, Gautam Buddha Nagar, Uttar Pradesh 201301<br>
      Mail- Support@Simplyhiree.com | Web- www.simplyhiree.com
    @endif
  </footer>

  <h1 class="title">OFFER LETTER</h1>
  <div class="body">{!! $bodyHtml !!}</div>
</body></html>
