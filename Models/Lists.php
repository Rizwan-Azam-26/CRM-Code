<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Scopes\CreatorScope;

class Lists extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lists';

    protected $fillable = [
        'name',
        'type',
        'description',
        'filters',
        'active',
        'system',
        'counter',
        'company_id',
        'company_shared',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'filters' => 'json',
    ];


    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new CreatorScope());
    }

    /**
     * Scope a query to only include lists of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
