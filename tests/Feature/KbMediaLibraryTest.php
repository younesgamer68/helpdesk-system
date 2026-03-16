<?php

use App\Livewire\Tickets\Kb\MediaLibrary;
use App\Models\Company;
use App\Models\KbMedia;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('uploads multiple kb media images for the authenticated company', function () {
    Storage::fake('public');

    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $firstFile = UploadedFile::fake()->image('knowledge-base-image.jpg');
    $secondFile = UploadedFile::fake()->image('troubleshooting-guide.jpg');

    $this->actingAs($user);

    Livewire::test(MediaLibrary::class)
        ->set('photos', [$firstFile, $secondFile])
        ->assertHasNoErrors();

    $medias = KbMedia::query()->orderBy('id')->get();

    expect($medias)->toHaveCount(2);
    expect($medias->every(fn (KbMedia $media) => $media->company_id === $company->id))->toBeTrue();
    expect($medias->pluck('file_name')->all())->toBe([
        'knowledge-base-image.jpg',
        'troubleshooting-guide.jpg',
    ]);

    Storage::disk('public')->assertExists($medias[0]->file_path);
    Storage::disk('public')->assertExists($medias[1]->file_path);
});

it('dispatches selected media urls for batch insertion', function () {
    Storage::fake('public');

    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $first = KbMedia::create([
        'company_id' => $company->id,
        'file_name' => 'first.jpg',
        'file_path' => 'kb-media/first.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);

    $second = KbMedia::create([
        'company_id' => $company->id,
        'file_name' => 'second.jpg',
        'file_path' => 'kb-media/second.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);

    $this->actingAs($user);

    Livewire::test(MediaLibrary::class)
        ->set('showModal', true)
        ->set('selectedMediaIds', [$first->id, $second->id])
        ->call('insertSelectedMedia')
        ->assertDispatched('media-selected')
        ->assertSet('showModal', false)
        ->assertSet('selectedMediaIds', []);
});

it('dispatches selected media url for single insert by id', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $media = KbMedia::create([
        'company_id' => $company->id,
        'file_name' => "customer's-guide.jpg",
        'file_path' => 'kb-media/customers-guide.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);

    $this->actingAs($user);

    Livewire::test(MediaLibrary::class)
        ->set('showModal', true)
        ->call('selectMedia', $media->id)
        ->assertDispatched('media-selected')
        ->assertSet('showModal', false);
});

it('can select all and clear selected media', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $first = KbMedia::create([
        'company_id' => $company->id,
        'file_name' => 'first.jpg',
        'file_path' => 'kb-media/first.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);

    $second = KbMedia::create([
        'company_id' => $company->id,
        'file_name' => 'second.jpg',
        'file_path' => 'kb-media/second.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);

    $this->actingAs($user);

    Livewire::test(MediaLibrary::class)
        ->call('selectAllMedia')
        ->assertSet('selectedMediaIds', [$second->id, $first->id])
        ->call('clearSelectedMedia')
        ->assertSet('selectedMediaIds', []);
});

it('can delete all media for the company', function () {
    Storage::fake('public');

    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    KbMedia::create([
        'company_id' => $company->id,
        'file_name' => 'first.jpg',
        'file_path' => 'kb-media/first.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);

    KbMedia::create([
        'company_id' => $company->id,
        'file_name' => 'second.jpg',
        'file_path' => 'kb-media/second.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);

    $this->actingAs($user);

    Livewire::test(MediaLibrary::class)
        ->call('selectAllMedia')
        ->call('deleteAllMedia')
        ->assertSet('selectedMediaIds', []);

    expect(KbMedia::query()->where('company_id', $company->id)->count())->toBe(0);
});

it('can delete selected media only', function () {
    Storage::fake('public');

    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $first = KbMedia::create([
        'company_id' => $company->id,
        'file_name' => 'first.jpg',
        'file_path' => 'kb-media/first.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);

    $second = KbMedia::create([
        'company_id' => $company->id,
        'file_name' => 'second.jpg',
        'file_path' => 'kb-media/second.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);

    $third = KbMedia::create([
        'company_id' => $company->id,
        'file_name' => 'third.jpg',
        'file_path' => 'kb-media/third.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);

    $this->actingAs($user);

    Livewire::test(MediaLibrary::class)
        ->set('selectedMediaIds', [$third->id, $first->id])
        ->call('deleteSelectedMedia')
        ->assertSet('selectedMediaIds', []);

    expect(KbMedia::query()->where('company_id', $company->id)->pluck('id')->all())
        ->toBe([$second->id]);
});
