<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Scopes\CompanyOwnScope;

class ActionGroup extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'created_by',
        'updated_by',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new CompanyOwnScope());
    }

    /**
     * Get the company of resource.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user created the resource.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creater()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user updated the resource.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
