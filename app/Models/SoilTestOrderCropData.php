<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class SoilTestOrderCropData extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'soil_test_order_crop_data';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['texture_parameter', 'texture_value', 'texture_unit', 'sand_ideal_range', 'fertility_rating', 'nutrient', 'slit_parameter', 'clay_value', 'clay_unit', 'clay_ideal_range', 'soiltype_fertility', 'soiltype_nutrient'];
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

    public function soilTestOrderData()
    {
        return $this->belongsTo('App\Models\SoilTestOrderData', 'soil_test_order_data_id');
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
