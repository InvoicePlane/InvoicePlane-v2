<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Factories\ImportDetailFactory;

class ImportDetail extends Model
{
    use HasFactory;

    public $table = 'import_details';

    public $timestamps = false;

    protected $fillable = [
        'import_id',
        'import_lang_key',
        'import_table_name',
        'import_record_id',
    ];

    protected $primaryKey = 'import_detail_id';

    protected static function newFactory(): Factory
    {
        return ImportDetailFactory::new();
    }
}
