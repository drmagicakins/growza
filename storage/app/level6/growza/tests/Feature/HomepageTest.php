<?php

test('the homepage responds successfully', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
