<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Scopes\CompanyAndSystemCompanyOwnScope;
use App\Scopes\ActiveScope;
use Modules\Label\Models\Label;
use Modules\Accounting\Models\AccountingStatus;
use App\Models\WorkflowItem;
use App\Models\TemplateEmail;
use App\Models\StatusExpiration;

class Status extends Model
{
    use HasFactory;

    protected $table = 'statuses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    //TODO add - created_by, updated_by
    protected $fillable = [
        'company_id',
        'next_status_id',
        'group_id',
        'milestone_id',
        'name',
        'type',
        'expiry_days',
        'expiry_action_id',
        'level',
        'sort',
        'active',
        'system',
        'payable',
        'department_route_id',
        'portal_milestone_id',
        'description',
        'shared',
        'action_id',
        'anytime'
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new CompanyAndSystemCompanyOwnScope());
        static::addGlobalScope(new ActiveScope());
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $type
     *         1: Workflow
     *         2: Accounting
     *         3: Dialer
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the group of the status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(StatusGroup::class, 'group_id');
    }

    public function status_types()
    {
        return $this->belongsTo(StatusType::class, 'type')->select(["status_types.id", "status_types.name"]);
    }

    public function milestone()
    {
        return $this->belongsTo(Milestone::class, 'milestone_id')->select(["milestones.id", "milestones.name"]);
    }
    public function status_labels()
    {
        return $this->belongsToMany(Label::class, 'statuses_labels', 'status_id', 'label_id')->select(["labels.id", "labels.name"]);
    }
    public function status_account_type()
    {
        return $this->belongsToMany(AccountingStatus::class, 'statuses_statuses', 'status_id', 'account_type_id')->select(["accounting_statuses.id", "accounting_statuses.name"]);
    }
    public function status_workflow_item()
    {
        return $this->belongsToMany(WorkflowItem::class, 'statuses_workflow_items', 'status_id', 'workflow_item_id')->select(["workflow_items.id", "workflow_items.name"]);
    }
    public function template_emails()
    {
        return $this->belongsToMany(TemplateEmail::class, 'statuses_emails', 'status_id', 'email_template_id')->select(["template_emails.id", "template_emails.name"]);
    }
    public function expirations()
    {
        return $this->hasMany(StatusExpiration::class, 'status_id')->select(["statuses_expirations.id", "expr_days", "action_id"]);
    }
}
