<div x-data="{
    show: false,
    message: '',
    type: 'success',
    timeoutId: null,
    init() {
        $wire.on('show-toast', (event) => {
            // Clear existing timeout if there is one
            if (this.timeoutId) {
                clearTimeout(this.timeoutId);
            }

            this.message = event.message;
            this.type = event.type || 'success';
            this.show = true;

            // Set new timeout and store the ID
            this.timeoutId = setTimeout(() => {
                this.show = false;
                this.timeoutId = null;
            }, 3000);
        });
    }
}" x-show="show" x-transition:enter="transition transform ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition transform ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-4"
    :class="{
        'bg-teal-600': type === 'success',
        'bg-red-500': type === 'error',
        'bg-blue-500': type === 'info'
    }"
    class="fixed top-5 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-lg shadow-lg text-white text-sm z-50"
    style="display: none;">
    <span x-text="message"></span>
</div>
