<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MarketplaceSetting extends Model {
 protected $fillable=['key','value','type','updated_by'];
 public static function valueOf(string $key, mixed $default=null): mixed {$row=static::where('key',$key)->first();if(!$row)return $default;return match($row->type){'boolean'=>(bool)filter_var($row->value,FILTER_VALIDATE_BOOLEAN),'integer'=>(int)$row->value,'decimal'=>(float)$row->value,default=>$row->value};}
}
