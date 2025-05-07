<?php

namespace Modules\Clients\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCustom extends Model
{
    use HasFactory;

    public $table = 'client_custom';

    public $timestamps = false;

    protected $primaryKey = 'client_custom_id';
}
