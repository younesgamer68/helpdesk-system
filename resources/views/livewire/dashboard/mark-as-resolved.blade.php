<div>
@if($state != "resolved")
    <button wire:click="resolve"
    wire:confirm="Are you sure you want to mark this ticket as resolved?"class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>
    Mark as Resolved
</button>
@else
    <button wire:click="unresolve"
    wire:confirm="Are you sure you want to unresolve this ticket?"class="px-4 py-2 bg-zinc-600 hover:bg-zinc-700 text-white rounded-lg transition flex items-center gap-2">
   <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M3 10h10a8 8 0 018 8v2M3 10l3-3m0 0l3 3m-3-3v9" />
</svg>
    Unresolve
</button>
@endif
</div>