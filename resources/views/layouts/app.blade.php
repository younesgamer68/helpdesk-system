<x-layouts::app.sidebar :title="$title ?? null">
  
   @if(request()->route()->uri() === "tickets/{ticket}")
        <div class="animate-enter">
            {{ $slot }}
        </div>
    
    @else
     <flux:main>
        <div class="animate-enter">
            {{ $slot }}
        </div>
    </flux:main>
    @endif
</x-layouts::app.sidebar>
