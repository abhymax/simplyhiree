<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ComplianceDocument extends Model {protected $fillable=['party_type','party_id','document_type','title','file_path','version','status','accepted_at','accepted_by','uploaded_by'];protected $casts=['accepted_at'=>'datetime'];public function party(){return $this->belongsTo(User::class,'party_id');}public function uploader(){return $this->belongsTo(User::class,'uploaded_by');}}
