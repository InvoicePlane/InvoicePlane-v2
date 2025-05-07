<?php

namespace Modules\Core\Models;

use Modules\Core\Models\ImportDetail;

use Modules\Core\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
