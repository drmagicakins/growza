<?php

namespace App\Http\Requests;

use App\Domain\Support\Models\ContactMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public endpoint — anyone may submit. Abuse is handled by the
        // throttle middleware on the route plus the honeypot check below,
        // not by authorization.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            // `email:rfc` rather than `email:rfc,dns`: the dns variant performs
            // a live MX lookup, which makes validation dependent on network
            // conditions and rejects valid addresses on domains with unusual
            // mail routing. Deliverability is proven by the reply, not by a
            // blocking DNS call on form submission.
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'subject' => ['nullable', 'string', Rule::in(array_keys(ContactMessage::SUBJECTS))],
            'message' => ['required', 'string', 'min:20', 'max:5000'],

            // Honeypot: must be absent or empty. A bot filling every field
            // trips this and fails validation like any other rule.
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.min' => 'Please give us a bit more detail — at least a couple of sentences.',
            'website.prohibited' => 'This submission could not be processed.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'name',
            'email' => 'email address',
            'message' => 'message',
        ];
    }
}
