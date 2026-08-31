<?php

test('the /up health check endpoint responds successfully', function () {
    // Registered via ->withRouting(health: '/up') in bootstrap/app.php,
    // used by uptime monitoring per ARCHITECTURE.md §18.
    $response = $this->get('/up');

    $response->assertStatus(200);
});
