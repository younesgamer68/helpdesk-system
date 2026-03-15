<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertSuccessful()
        ->assertSeeText('Support heroes')
        ->assertSeeText('New conversation');
});
