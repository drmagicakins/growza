<?php

use App\Domain\Support\Models\ContactMessage;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\from;
use function Pest\Laravel\post;

/**
 * The route throttle stores its counters in the cache, and the array cache
 * driver used in testing persists for the life of the process. Without
 * clearing it, hits accumulated by earlier tests push later tests over the
 * limit and produce unrelated 429 failures.
 */
beforeEach(fn () => Cache::flush());

function validContactPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Adaeze Okafor',
        'email' => 'adaeze@example.com',
        'phone' => '+2348012345678',
        'subject' => 'campaign',
        'message' => 'We are releasing a single in November and want help planning the rollout campaign.',
    ], $overrides);
}

it('stores a valid enquiry and confirms it to the sender', function () {
    post(route('contact.store'), validContactPayload())
        ->assertRedirect(route('contact'))
        ->assertSessionHas('status');

    $message = ContactMessage::first();

    expect($message)->not->toBeNull()
        ->and($message->email)->toBe('adaeze@example.com')
        ->and($message->status)->toBe('new');
});

it('records request metadata the submitter cannot set', function () {
    post(route('contact.store'), validContactPayload());

    $message = ContactMessage::first();

    // status/ip/user_agent are outside $fillable — set by the Action only.
    expect($message->status)->toBe('new')
        ->and($message->ip_address)->not->toBeNull();
});

it('ignores attempts to mass-assign protected columns', function () {
    post(route('contact.store'), validContactPayload([
        'status' => 'archived',
        'handled_at' => now()->toDateTimeString(),
    ]));

    expect(ContactMessage::first()->status)->toBe('new')
        ->and(ContactMessage::first()->handled_at)->toBeNull();
});

it('rejects a submission that trips the honeypot', function () {
    from(route('contact'))
        ->post(route('contact.store'), validContactPayload(['website' => 'http://spam.example']))
        ->assertRedirect(route('contact'))
        ->assertSessionHasErrors('website');

    expect(ContactMessage::count())->toBe(0);
});

it('requires the essential fields', function () {
    from(route('contact'))
        ->post(route('contact.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'message']);

    expect(ContactMessage::count())->toBe(0);
});

it('rejects a message that is too short to be useful', function () {
    from(route('contact'))
        ->post(route('contact.store'), validContactPayload(['message' => 'hi']))
        ->assertSessionHasErrors('message');
});

it('rejects a subject outside the allowed list', function () {
    from(route('contact'))
        ->post(route('contact.store'), validContactPayload(['subject' => 'definitely-not-valid']))
        ->assertSessionHasErrors('subject');
});

it('throttles repeated submissions from the same client', function () {
    // Route limit is 5 per 10 minutes; the sixth must be rejected.
    foreach (range(1, 5) as $i) {
        post(route('contact.store'), validContactPayload(['email' => "sender{$i}@example.com"]))
            ->assertRedirect();
    }

    post(route('contact.store'), validContactPayload(['email' => 'sender6@example.com']))
        ->assertStatus(429);
});
