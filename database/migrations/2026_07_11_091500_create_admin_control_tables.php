<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('marketplace_settings', function(Blueprint $t){$t->id();$t->string('key')->unique();$t->text('value')->nullable();$t->string('type',20)->default('string');$t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();});
  Schema::create('compliance_documents', function(Blueprint $t){$t->id();$t->string('party_type',20);$t->foreignId('party_id')->nullable()->constrained('users')->nullOnDelete();$t->string('document_type',30);$t->string('title');$t->string('file_path');$t->string('version')->nullable();$t->string('status',20)->default('active');$t->timestamp('accepted_at')->nullable();$t->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();$t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();$t->index(['party_type','document_type']);});
  Schema::create('manual_revenue_entries', function(Blueprint $t){$t->id();$t->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();$t->decimal('amount',14,2);$t->date('recognized_on');$t->string('reference')->nullable();$t->text('reason');$t->foreignId('created_by')->constrained('users')->restrictOnDelete();$t->timestamps();});
 }
 public function down(): void {Schema::dropIfExists('manual_revenue_entries');Schema::dropIfExists('compliance_documents');Schema::dropIfExists('marketplace_settings');}
};
