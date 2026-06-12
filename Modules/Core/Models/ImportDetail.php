<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\BelongsToCompany;

class ImportDetail extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    /*protected static function newFactory(): Factory
    {
        return ImportDetailFactory::new();
    }*/
}
