<?php

namespace App\Domain\Support\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An enquiry submitted through the public marketing contact form.
 *
 * Lives in the Support domain because that is where inbound customer
 * communication belongs, but it is not a SupportTicket — see the
 * migration's docblock for why the two are kept distinct.
 */
class ContactMessage extends Model
{
    use HasFactory;

    /**
     * Enquiry topics offered on the contact form. Kept here rather than
     * hard-coded in the Blade template so the form options and the
     * validation rule can never drift apart.
     */
    public const SUBJECTS = [
        'campaign' => 'Starting a campaign',
        'pricing' => 'Pricing and quotes',
        'agency' => 'Agency or reseller enquiry',
        'existing_order' => 'An existing order',
        'billing' => 'Billing or payments',
        'other' => 'Something else',
    ];

    /**
     * `status`, `handled_at`, `ip_address` and `user_agent` are deliberately
     * excluded — they are set by the application, never by user input.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
        ];
    }

    public function subjectLabel(): string
    {
        return self::SUBJECTS[$this->subject] ?? 'General enquiry';
    }
}
