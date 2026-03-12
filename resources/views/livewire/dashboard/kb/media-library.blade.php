<div>
    <flux:modal wire:model="showModal" class="md:w-[800px]">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Media Library</flux:heading>
                <div x-data="{ uploading: false }" x-on:livewire-upload-start="uploading = true" x-on:livewire-upload-finish="uploading = false" x-on:livewire-upload-error="uploading = false">
                    <input type="file" id="media-upload" class="hidden" wire:model="photo" accept="image/*">
                    <label for="media-upload" class="cursor-pointer px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg flex items-center gap-2 transition" :class="{ 'opacity-50 cursor-not-allowed': uploading }">
                        <svg x-show="!uploading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <svg x-show="uploading" class="animate-spin w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="uploading ? 'Uploading...' : 'Upload Image'"></span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 max-h-[500px] overflow-y-auto p-1">
                @forelse($medias as $media)
                    <div class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50 aspect-square">
                        <img src="{{ Storage::disk('public')->url($media->file_path) }}" alt="{{ $media->file_name }}" class="w-full h-full object-cover">
                        
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
                            <button wire:click="selectMedia('{{ Storage::disk('public')->url($media->file_path) }}')" class="px-3 py-1.5 bg-teal-500 hover:bg-teal-600 text-white text-xs font-medium rounded transition">
                                Insert
                            </button>
                            <button wire:click="deleteMedia({{ $media->id }})" wire:confirm="Delete this image?" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded transition">
                                Delete
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-zinc-500 dark:text-zinc-400">
                        <svg class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <p>No images uploaded yet.</p>
                    </div>
                @endforelse
            </div>
            
            <div class="flex justify-end pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:button wire:click="$set('showModal', false)" variant="ghost">Close</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
