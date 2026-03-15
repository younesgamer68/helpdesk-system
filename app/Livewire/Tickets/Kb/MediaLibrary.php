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

    public $photo;

    public $showModal = false;

    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:10240', // 10MB Max
        ]);

        $path = $this->photo->store('kb-media', 'public');

        $media = KbMedia::create([
            'company_id' => Auth::user()->company_id,
            'file_name' => $this->photo->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $this->photo->getMimeType(),
            'size' => $this->photo->getSize(),
        ]);

        $this->photo = null;
        $this->dispatch('show-toast', ['message' => 'Image uploaded successfully.', 'type' => 'success']);
        $this->dispatch('media-uploaded', ['url' => asset('storage/'.$path)]);
    }

    public function deleteMedia($id)
    {
        $media = KbMedia::where('company_id', Auth::user()->company_id)->findOrFail($id);
        Storage::disk('public')->delete($media->file_path);
        $media->delete();
        $this->dispatch('show-toast', ['message' => 'Image deleted.', 'type' => 'success']);
    }

    public function selectMedia($url)
    {
        $this->dispatch('media-selected', ['url' => $url]);
        $this->showModal = false;
    }

    public function render()
    {
        $medias = KbMedia::where('company_id', Auth::user()->company_id)->latest()->get();

        return view('livewire.tickets.kb.media-library', [
            'medias' => $medias,
        ]);
    }
}
