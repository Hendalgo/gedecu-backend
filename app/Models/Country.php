<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'shortcode',
        'config',
        'locale',
        'img',
        'delete',
    ];

    public function banks()
    {
        return $this->hasMany('\App\Models\Bank', 'country_id');
    }

    public function users()
    {
        return $this->hasMany('\App\Models\User', 'country_id');
    }

    public function currency()
    {
        return $this->hasOne('\App\Models\Currency', 'country_id');
    }

    public function stores()
    {
        return $this->hasMany('\App\Models\Store', 'country_id');
    }

    public function delete()
    {
        foreach ($this->banks as $bank) {
            $bank->delete();
        }
        $this->currency->delete();
        foreach ($this->stores as $store) {
            $store->delete();
        }
        foreach ($this->users as $user) {
            $user->delete();
        }
        $this->delete = 1;

        return $this->save();
    }
}
