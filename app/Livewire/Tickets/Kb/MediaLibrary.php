<?php

namespace App\Livewire\Tickets\Kb;

use App\Models\KbMedia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('KB Media Library')]
class MediaLibrary extends Component
{
    use WithFileUploads;

    public array $photos = [];

    public $showModal = false;

    public array $selectedMediaIds = [];

    public function updatedPhotos(): void
    {
        $this->validate([
            'photos' => 'array|min:1',
            'photos.*' => 'image|max:10240',
        ]);

        foreach ($this->photos as $photo) {
            $path = $photo->store('kb-media', 'public');

            KbMedia::create([
                'company_id' => Auth::user()->company_id,
                'file_name' => $photo->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $photo->getMimeType(),
                'size' => $photo->getSize(),
            ]);
        }

        $uploadedCount = count($this->photos);
        $this->photos = [];

        $this->dispatch('show-toast', [
            'message' => $uploadedCount === 1
                ? 'Image uploaded successfully.'
                : "{$uploadedCount} images uploaded successfully.",
            'type' => 'success',
        ]);
    }

    public function deleteMedia($id)
    {
        $media = KbMedia::where('company_id', Auth::user()->company_id)->findOrFail($id);
        Storage::disk('public')->delete($media->file_path);
        $media->delete();

        $this->selectedMediaIds = array_values(array_filter(
            $this->selectedMediaIds,
            fn ($selectedId) => (int) $selectedId !== (int) $id
        ));

        $this->dispatch('show-toast', ['message' => 'Image deleted.', 'type' => 'success']);
    }

    public function updatedShowModal(bool $value): void
    {
        if (! $value) {
            $this->selectedMediaIds = [];
        }
    }

    public function toggleMediaSelection(int $id): void
    {
        if (in_array($id, $this->selectedMediaIds, true)) {
            $this->selectedMediaIds = array_values(array_filter(
                $this->selectedMediaIds,
                fn ($selectedId) => (int) $selectedId !== $id
            ));

            return;
        }

        $this->selectedMediaIds[] = $id;
    }

    public function selectAllMedia(): void
    {
        $this->selectedMediaIds = KbMedia::query()
            ->where('company_id', Auth::user()->company_id)
            ->orderByDesc('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function clearSelectedMedia(): void
    {
        $this->selectedMediaIds = [];
    }

    public function deleteSelectedMedia(): void
    {
        if (count($this->selectedMediaIds) === 0) {
            return;
        }

        $mediaItems = KbMedia::query()
            ->where('company_id', Auth::user()->company_id)
            ->whereIn('id', $this->selectedMediaIds)
            ->get();

        if ($mediaItems->isEmpty()) {
            $this->selectedMediaIds = [];

            return;
        }

        foreach ($mediaItems as $media) {
            Storage::disk('public')->delete($media->file_path);
        }

        KbMedia::query()
            ->where('company_id', Auth::user()->company_id)
            ->whereIn('id', $this->selectedMediaIds)
            ->delete();

        $deletedCount = $mediaItems->count();
        $this->selectedMediaIds = [];

        $this->dispatch('show-toast', [
            'message' => $deletedCount === 1
                ? 'Image deleted.'
                : "{$deletedCount} selected images deleted.",
            'type' => 'success',
        ]);
    }

    public function deleteAllMedia(): void
    {
        $mediaItems = KbMedia::query()
            ->where('company_id', Auth::user()->company_id)
            ->get();

        if ($mediaItems->isEmpty()) {
            return;
        }

        foreach ($mediaItems as $media) {
            Storage::disk('public')->delete($media->file_path);
        }

        KbMedia::query()
            ->where('company_id', Auth::user()->company_id)
            ->delete();

        $deletedCount = $mediaItems->count();
        $this->selectedMediaIds = [];

        $this->dispatch('show-toast', [
            'message' => $deletedCount === 1
                ? 'Image deleted.'
                : "{$deletedCount} images deleted.",
            'type' => 'success',
        ]);
    }

    public function selectMedia(int $id): void
    {
        $media = KbMedia::query()
            ->where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

        $url = Storage::disk('public')->url($media->file_path);

        $this->dispatch('media-selected', ['url' => $url]);
        $this->showModal = false;
    }

    public function insertSelectedMedia(): void
    {
        if (count($this->selectedMediaIds) === 0) {
            return;
        }

        $urls = KbMedia::query()
            ->where('company_id', Auth::user()->company_id)
            ->whereIn('id', $this->selectedMediaIds)
            ->orderByDesc('id')
            ->get()
            ->reverse()
            ->map(fn (KbMedia $media) => Storage::disk('public')->url($media->file_path))
            ->values()
            ->all();

        if (count($urls) === 0) {
            return;
        }

        $this->dispatch('media-selected', ['urls' => $urls]);
        $this->showModal = false;
        $this->selectedMediaIds = [];
    }

    public function render()
    {
        $medias = KbMedia::where('company_id', Auth::user()->company_id)->latest()->get();

        return view('livewire.tickets.kb.media-library', [
            'medias' => $medias,
        ]);
    }
}
