<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Models\TimeSlot;
use Illuminate\Notifications\Notification;

/**
 * Tells a parent their appointment was confirmed, declined, cancelled (by the
 * hospital being deactivated or by the system) or marked as missed.
 */
class AppointmentUpdated extends Notification
{
    public const EVENTS = ['approved', 'rejected', 'cancelled', 'no_show'];

    public function __construct(
        public readonly Appointment $appointment,
        public readonly string $event,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $a = $this->appointment->loadMissing('child', 'hospital', 'timeSlot', 'childDose.scheduleDose');
        $what = "{$a->child->first_name}'s {$a->doseLabel()}";
        $when = $a->timeSlot->date->format('D j M').' at '.TimeSlot::formatTime($a->timeSlot->start_time);

        [$title, $body, $kind] = match ($this->event) {
            'approved' => ['Appointment confirmed', "{$a->hospital->name} confirmed {$what} on {$when}.", 'success'],
            'rejected' => ['Appointment declined', "{$a->hospital->name} declined {$what} on {$when}. Reason: {$a->reason}", 'danger'],
            'cancelled' => ['Appointment cancelled', "{$what} on {$when} at {$a->hospital->name} was cancelled: {$a->reason}. Please book again.", 'warning'],
            'no_show' => ['Missed appointment', "{$a->hospital->name} recorded that {$a->child->first_name} didn't come for {$a->doseLabel()} on {$when}. Please book again.", 'warning'],
        };

        return [
            'kind' => $kind,
            'event' => $this->event,
            'title' => $title,
            'body' => $body,
            'url' => route('parent.appointments.index', absolute: false),
            'appointment_id' => $a->id,
            'child_id' => $a->child_id,
        ];
    }
}
