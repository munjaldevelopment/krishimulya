<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class SoilTestOrderData extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'soil_test_order_data';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    
    protected $fillable = ['farmer_name', 'farmer_code', 'crop_grown', 'sample_number', 'field_size', 'sampling_date', 'sample_testing_date', 'region', 'previous_season', 'sample_collected_by', 'yield_goal', 'previous_crop', 'recommendation', 'comments'];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function soilTestOrder()
    {
        return $this->belongsTo('App\Models\SoilTestOrder', 'soil_test_order_id');
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
    public function getSoilTestCropRowAttribute() {
        $fullName = $this->crop_grown ." " . " (".$this->sample_number.") <br />".$this->field_size ." ".$this->sampling_date ." ".$this->sample_testing_date ." ".$this->region ." ".$this->previous_season ." ".$this->sample_collected_by ." ".$this->yield_goal ." ".$this->previous_crop;
        return $fullName;
    }
}
