<?php

use App\Domain\Audit\Services\AuditLogger;
use Illuminate\Http\Request;

it('redacts sensitive keys from audit snapshots', function () {
    $logger = new AuditLogger(Request::create('/', 'GET'));

    $redact = (new ReflectionClass($logger))->getMethod('redact');
    $redact->setAccessible(true);

    $result = $redact->invoke($logger, [
        'email' => 'user@example.com',
        'password' => 'super-secret',
        'api_key' => 'live_abc123',
        'two_factor_secret' => 'otp-seed',
        'nested' => ['card_number' => '4111111111111111', 'name' => 'Ada'],
    ]);

    expect($result['email'])->toBe('user@example.com')
        ->and($result['password'])->toBe('[redacted]')
        ->and($result['api_key'])->toBe('[redacted]')
        ->and($result['two_factor_secret'])->toBe('[redacted]')
        ->and($result['nested']['card_number'])->toBe('[redacted]')
        ->and($result['nested']['name'])->toBe('Ada');
});
