<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['mail.default'=>'array']);
echo "from address: ".config('mail.from.address')."  name: ".config('mail.from.name')."\n\n";
// render the template from the screenshot
$html=view('emails.vendor_application_approved',[
  'application'=>App\Models\JobApplication::with(['job','candidate'])->find(737),
  'candidateName'=>'Aman prasad','jobTitle'=>'Van Delivery Executive','companyName'=>'Flipkart',
])->render();
file_put_contents('/tmp/mail_preview.html',$html);
echo "rendered ".strlen($html)." bytes\n";
// strip the <style> block to simulate Outlook ignoring it, then check the logo is still readable
$noStyle=preg_replace('#<style.*?</style>#is','',$html);
preg_match('#<div class="logo"[^>]*>(.*?)</div>#is',$noStyle,$m);
echo "logo markup without <style>: ".trim(strip_tags($m[0] ?? ''))."\n";
echo "  has inline white colour: ".(str_contains($m[0]??'','color:#ffffff')?'yes':'NO')."\n";
preg_match('#<div class="header"[^>]*>#i',$noStyle,$h);
echo "header inline background: ".(str_contains($h[0]??'','background-color:#0f172a')?'yes (#0f172a dark)':'NO')."\n";
