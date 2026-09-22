<?php

namespace App\Domain\Support\Actions;

use App\Domain\Support\Models\ContactMessage;
use Illuminate\Support\Facades\Log;

/**
 * Persists an inbound marketing enquiry.
 *
 * Kept as an Action rather than inline controller code so that LEVEL 12
 * (notifications) can add an admin alert, and LEVEL 13 can add ticket
 * conversion, without the controller changing at all.
 */
class StoreContactMessage
{
    /**
     * @param  array<string, mixed>  $attributes  Already validated by ContactFormRequest.
     */
    public function handle(array $attributes, ?string $ipAddress = null, string $userAgent = ''): ContactMessage
    {
        $message = new ContactMessage($attributes);

        // Set outside $fillable deliberately — these are application-derived,
        // never user-supplied. See the model's fillable docblock.
        $message->status = 'new';
        $message->ip_address = $ipAddress;
        $message->user_agent = $userAgent !== '' ? $userAgent : null;
        $message->save();

        // Logged without the message body: the enquiry itself lives in the
        // database, and log files are a lower-trust store than the DB.
        Log::info('Contact enquiry received', [
            'contact_message_id' => $message->id,
            'subject' => $message->subject,
        ]);

        return $message;
    }
}
