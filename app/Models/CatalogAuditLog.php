<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogAuditLog extends Model
{
    protected $fillable = ['user_id', 'user_name', 'user_role', 'action', 'detail'];
}
