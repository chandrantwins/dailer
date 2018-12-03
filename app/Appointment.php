<?php
namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    public function scopeAppointmentsDue($query)
    {
        $now = Carbon::now();
        $inTenMinutes = Carbon::now()->addMinutes(10);
        return $query->where('notificationTime', '>=', $now)->where('notificationTime', '<=', $inTenMinutes);
    }

    /**
     * Get the contact that attend the appointment.
     */
    public function contact()
    {
        return $this->belongsTo('App\Contact', 'contact_id');
    }

    /**
     * Get the closer who assigned to appointment.
     */
    public function closeruser()
    {
        return $this->belongsTo('App\User','assigned_to');
    }

    /**
     *  Get the company user details who created appointment
     */
    public function companyuser()
    {
        return $this->belongsTo('App\User','created_by');
    }
}