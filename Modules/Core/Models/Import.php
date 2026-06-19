<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Factories\ImportFactory;

class Import extends Model
{
    use HasFactory;

    public static $rules = [
        'import_date' => 'required',
    ];

    public $table = 'imports';

    public $timestamps = false;

    protected $fillable = [
        'import_date',
    ];

    protected $primaryKey = 'import_id';

    protected static function newFactory(): Factory
    {
        return ImportFactory::new();
    }
}
