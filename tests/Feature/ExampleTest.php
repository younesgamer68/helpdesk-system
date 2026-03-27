<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertSuccessful()
        ->assertSeeText('Support heroes')
        ->assertSeeText('New conversation');
});

test('contact page is accessible', function () {
    $response = $this->get(route('contact'));

    $response->assertSuccessful()
        ->assertSeeText('Contact')
        ->assertSeeText('Send a message');
});

test('help center page is accessible', function () {
    $response = $this->get(route('help-center'));

    $response->assertSuccessful()
        ->assertSeeText('Help Center')
        ->assertSeeText('Popular answers')
        ->assertSeeText('Search answers');
});

test('about page is accessible', function () {
    $response = $this->get(route('about'));

    $response->assertSuccessful()
        ->assertSeeText('About us')
        ->assertSeeText('Our story')
        ->assertSeeText('What makes HelpDesk different');
});
