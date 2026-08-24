<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Importers\ImportFactory;
use Modules\Core\Traits\BelongsToCompany;

class Import extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $guarded = [];

    protected $primaryKey = 'import_id';

    /*protected static function newFactory(): Factory
    {
        return ImportFactory::new();
    }*/
}
