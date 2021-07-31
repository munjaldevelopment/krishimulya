<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoilTestOrder extends Model
{
    use CrudTrait;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'soil_test_orders';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function allCustomers()
    {
        return $this->belongsTo('App\Models\Customer', 'customer_id');
    }

    public function allVendors()
    {
        return $this->belongsTo('App\Models\Vendor', 'customer_id');
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function getCropTypeRowAttribute() {
        $fullName = $this->crop_type;
        return $fullName;
    }

    public function getSoilTypeRowAttribute() {
        $fullName = $this->soil_type;
        return $fullName;
    }

    public function getSoilTestCropRowAttribute() {
        $fullName = $this->crop_grown ." " . " (".$this->sample_number.") <br />".$this->field_size ." ".$this->sampling_date ." ".$this->sample_testing_date ." ".$this->region ." ".$this->previous_season ." ".$this->sample_collected_by ." ".$this->yield_goal ." ".$this->previous_crop;
        return $fullName;
    }
}
