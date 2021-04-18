<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use CrudTrait, RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'vendors';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];

    protected $fillable = ['user_id', 'name', 'email','phone', 'password', 'is_onboard']; //
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function users()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function vendorService()
    {
        return $this->belongsToMany('App\Models\VendorService', 'vendor_vendor_assign');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function identifiableName()
    {
        return $this->name;
    }

    // If you are using another bootable trait
    // be sure to override the boot method in your model
    public static function boot()
    {
        parent::boot();
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
}
