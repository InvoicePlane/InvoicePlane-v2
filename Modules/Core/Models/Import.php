<?php

namespace Modules\Core\Models;

use Modules\Core\Models\Import;

use Modules\Core\Importers\ImportFactory;

use Modules\Core\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $primaryKey = 'import_id';

    /*protected static function newFactory(): Factory
    {
        return ImportFactory::new();
    }*/
}
