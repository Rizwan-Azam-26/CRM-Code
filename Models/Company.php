<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Modules\Event\Entities\EventType;

use App\Scopes\ActiveScope;
use Modules\Accounting\Models\AccountingType;
use Modules\Accounting\Models\PaymentScheduleType;
use Modules\Document\Models\Document;
use App\Models\Servicer;
use Modules\Contact\Models\DuplicateGroup;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        // 'parent_id',
        // 'broker_user_id',

        'name',
        'long_name',
        'legal_name',
        'email',
        'case_email_domain',
        'logo',

        'office_address',
        'office_address_2',
        'office_city',
        'office_state',
        'office_zip',
        'office_fax',
        'office_phone',

        'cs_phone',
        'cs_email',

        'website',
        'client_portal_url',
        'inbox_prefix',
        // 'social_facebook',
        // 'social_instagram',
        // 'social_twitter',
        // 'social_googleplus',
        // 'social_linkedin',
        // 'suspended',
        // 'created_by',
        // 'updated_by',
    ];

    protected $casts = [
        'suspended' => 'boolean'
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('AccessLevel', function (Builder $builder) {
            // $builder->where('assigned_user_id', auth()->user()->id);
        });
    }

    /**
     * Scope a query to only include suspended items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuspended($query)
    {
        return $query->where('suspended', true);
    }

    /**
     * Get the parent company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Company::class, 'parent_id');
    }

    /**
     * Get children companies.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function children()
    {
        return $this->hasMany(Company::class, 'parent_id');
    }

    /**
     * Get the networks of the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function networks()
    {
        return $this->hasMany(Network::class);
    }

    /**
     * Get the regions of the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    /**
     * Get the departments of the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get the shared departments of the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sharedDepartments()
    {
        return $this->belongsToMany(Department::class, 'departments_shared');
    }

    /**
     * Get the campaign groups of the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaign_groups()
    {
        return $this->hasMany(CampaignGroup::class);
    }

    /**
     * Get the campaigns of the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Get the users of the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the parent company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function broker()
    {
        return $this->hasOne(User::class, 'broker_user_id');
    }

    public function accounting_types()
    {
        return $this->belongsToMany(AccountingType::class, 'account_types_companies', 'company_id', 'account_type_id');
    }

    public function payment_schedule_types()
    {
        return $this->belongsToMany(PaymentScheduleType::class, 'payment_schedule_types_cos', 'company_id', 'schedule_type_id');
    }

    public function document_templates()
    {
        return $this->belongsToMany(Document::class, 'documents_companies', 'company_id', 'form_id');
    }

    public function services()
    {
        return $this->belongsToMany(Servicer::class, 'services_companies', 'company_id', 'service_id');
    }

    public function event_types()
    {
        return $this->belongsToMany(EventType::class, 'event_types_companies', 'company_id', 'event_type_id');
    }

    /**
     * Get the Calendar option associated with the company.
     */
    public function calendar_option()
    {
        return $this->hasOne(CalendarOption::class);
    }

    public function duplicate_groups()
    {
        return $this->belongsToMany(DuplicateGroup::class, 'duplicate_groups_companies', 'company_id', 'group_id');
    }
}
