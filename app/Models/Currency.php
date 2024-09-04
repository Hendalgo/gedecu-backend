<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'shortcode',
        'symbol',
    ];

    public function banks()
    {
        return $this->hasMany('App\Models\Bank', 'currency_id', 'id');
    }

    public function delete()
    {
        $this->delete = 1;

        return $this->save();
    }
}
