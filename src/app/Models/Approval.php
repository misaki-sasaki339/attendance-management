<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'approved_by'
    ];

    //リレーションの定義
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }
}
