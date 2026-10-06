<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ManualRevenueEntry extends Model {protected $fillable=['client_id','amount','recognized_on','reference','reason','created_by'];protected $casts=['recognized_on'=>'date','amount'=>'decimal:2'];public function client(){return $this->belongsTo(User::class,'client_id');}public function creator(){return $this->belongsTo(User::class,'created_by');}}
