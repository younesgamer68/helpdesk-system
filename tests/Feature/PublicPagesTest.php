<?php

test('homepage returns successful response', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
});

test('about page returns successful response', function () {
    $response = $this->get('/about');

    $response->assertSuccessful();
});

test('help center page returns successful response', function () {
    $response = $this->get('/help-center');

    $response->assertSuccessful();
});

test('contact page returns successful response', function () {
    $response = $this->get('/contact');

    $response->assertSuccessful();
});

test('homepage contains dark mode bindings for key sections', function () {
    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee('$store.ui.darkMode ? \'bg-[#0b111b]\' : \'bg-white\'', false)
        ->assertSee('$store.ui.darkMode ? \'bg-[#0a101c]\' : \'bg-[#fdf0e8]\'', false)
        ->assertSee('$store.ui.darkMode ? \'text-white\' : \'text-[#1c1c2e]\'', false)
        ->assertSee('$store.ui.darkMode ? \'text-white/85\' : \'text-[#3a1e2e]\'', false);
});

test('homepage beacon tabs use dark aware active title colors', function () {
    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee('const beaconIsDarkMode = () => {', false)
        ->assertSee("title.classList.toggle('text-white', isActive && isDarkMode);", false)
        ->assertSee("title.classList.toggle('text-[#1c1c2e]', isActive && !isDarkMode);", false);
});

test('homepage still includes all beacon sections for dark mode behavior', function () {
    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee('id="beaconSection1"', false)
        ->assertSee('id="beaconSection2"', false)
        ->assertSee('id="beaconSection3"', false)
        ->assertSee("initBeaconTabs('beaconWrapper3', ['beacon3Panel0', 'beacon3Panel1', 'beacon3Panel2']);", false);
});
