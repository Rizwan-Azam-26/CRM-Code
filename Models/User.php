<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use LaravelAndVueJS\Traits\LaravelPermissionToVueJS;
use Spatie\Permission\Traits\HasRoles;

use Modules\Contact\Models\CampaignGroup;
use Modules\Dashboard\Models\Dashboard;
use Modules\Event\Models\CalendarOption;
use App\Models\Role;
use App\Models\Status;
use App\Models\RoleUser;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, LaravelPermissionToVueJS, HasRoles;

    protected $fillable = [
        'account_id',
        'company_id',
        'region_id',
        'first_name',
        'last_name',
        'mobile',
        'email',
        'passwd',
        'password',
        'active',
        'extension',
        'type',
        'wildcard',
        'dashboard_id',
        'signature_id',
        'campaign_group_id',
        'department_id',
        'reset_token',
        'reset_requested',
        'phone_pass',
        'caller_id',
        'login_count',
        'alert_email',
        'skywave_admin'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'passwd',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'full_name'
    ];


    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification());
    }

    /**
     * Get the display name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get the passwd(FuelPHP Password field) for the user.
     *
     * @return string
     */
    public function getAuthPasswd()
    {
        return $this->passwd;
    }

    public function signature()
    {
        return $this->belongsTo(TemplateSignature::class);
    }

    public function dashboard()
    {
        return $this->belongsTo(Dashboard::class);
    }

    public function userSchedule()
    {
        return $this->hasOne(CalendarOption::class);
    }

    public function companySchedule()
    {
        return $this->hasOne(CalendarOption::class, 'company_id', 'company_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_users', 'user_id', 'role_id');
    }

    public function statuses()
    {
        return $this->belongsToMany(Status::class, 'statuses_users', 'user_id', 'status_id');
    }
}
