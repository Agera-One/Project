<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Documents;

class status extends Model
{
    use HasFactory;

    protected $table = 'status';

    protected $fillable = ['status_name'];

    public function documents(): HasMany
    {
        return $this->hasMany(Documents::class, 'documents_id');
    }
}
