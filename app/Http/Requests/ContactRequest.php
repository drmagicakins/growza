<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Server-side validation for the public contact form. Per master prompt
 * §3 ("never trust client-side validation alone") the HTML `required`
 * attributes on the form are a convenience only — these rules are the
 * actual gate.
 */
final class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public endpoint — no authenticated actor required. Abuse control
        // is handled by the route's throttle middleware plus the honeypot
        // check below, not by authorization.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', Rule::in(array_keys(config('marketing.contact.subjects')))],
            'message' => ['required', 'string', 'min:20', 'max:5000'],

            // Honeypot: a real user never fills this (it is visually hidden
            // and aria-hidden). Bots that blindly complete every field do.
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.min' => 'Please give us a little more detail — at least 20 characters.',
            'website.prohibited' => 'This submission could not be processed.',
        ];
    }
}
