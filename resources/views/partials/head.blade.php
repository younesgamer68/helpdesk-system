<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
@auth
<meta name="auth-user-id" content="{{ Auth::id() }}" />@endauth

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* SweetAlert2 custom theme — modern dialog (photo 2 style) */
    .swal2-popup.swal-custom {
        border-radius: 1rem !important;
        padding: 2rem 1.75rem 1.5rem !important;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,.15) !important;
        border: 1px solid #e5e7eb !important;
        max-width: 380px !important;
    }
    .swal-custom .swal2-icon {
        margin: 0 auto 1rem !important;
        width: 56px !important; height: 56px !important;
        border: none !important;
        background: #fef2f2 !important;
        border-radius: 50% !important;
    }
    .swal-custom .swal2-icon .swal2-icon-content {
        color: #ef4444 !important; font-size: 1.5rem !important;
    }
    .swal-custom .swal2-icon.swal2-warning { color: #ef4444 !important; }
    .swal-custom .swal2-icon.swal2-warning .swal2-icon-content { color: #ef4444 !important; }
    .swal-custom .swal2-title {
        font-size: 1.125rem !important; font-weight: 700 !important;
        color: #18181b !important; padding: 0 !important; margin-bottom: .25rem !important;
    }
    .swal-custom .swal2-html-container {
        font-size: .875rem !important; color: #71717a !important;
        margin: 0 0 1.25rem !important; padding: 0 !important;
    }
    .swal-custom .swal2-actions {
        gap: .75rem !important; margin: 0 !important; width: 100% !important;
        flex-wrap: nowrap !important; padding: 0 !important;
    }
    .swal-custom .swal2-cancel {
        flex: 1 !important; border-radius: .5rem !important;
        font-weight: 600 !important; font-size: .875rem !important;
        padding: .625rem 1rem !important;
        background: #fff !important; color: #18181b !important;
        border: 1px solid #d4d4d8 !important;
        transition: all .2s !important;
    }
    .swal-custom .swal2-cancel:hover { background: #f4f4f5 !important; }
    .swal-custom .swal2-confirm {
        flex: 1 !important; border-radius: .5rem !important;
        font-weight: 600 !important; font-size: .875rem !important;
        padding: .625rem 1rem !important;
        background: #10b981 !important; color: #fff !important;
        border: none !important; box-shadow: none !important;
        transition: all .2s !important;
    }
    .swal-custom .swal2-confirm:hover { background: #059669 !important; }
    .swal-custom .swal2-close {
        color: #a1a1aa !important; font-size: 1.25rem !important;
        top: .75rem !important; right: .75rem !important;
    }
    .swal-custom .swal2-close:hover { color: #18181b !important; }
</style>
<script>
    const swalCustom = {
        customClass: { popup: 'swal-custom' },
        showCloseButton: true,
        buttonsStyling: false,
        reverseButtons: true,
        background: '#ffffff',
        color: '#18181b',
    };

    window.confirmDeletion = function(wire, id, method = 'deleteTicket', itemType = 'ticket') {
        Swal.fire({
            ...swalCustom,
            title: 'Delete',
            text: 'Are you sure you would like to delete this ' + itemType + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                wire.call(method, id);
            }
        })
    }

    window.confirmAction = function(wire, id, method, title = 'Are you sure?', text =
        "You won't be able to revert this!", confirmBtnText = 'Confirm', icon = 'warning') {
        Swal.fire({
            ...swalCustom,
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonText: confirmBtnText,
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                wire.call(method, id);
            }
        })
    }

    /* Override all wire:confirm native dialogs → SweetAlert2 green theme */
    document.addEventListener('click', function(e) {
        const el = e.target.closest('[wire\\:confirm]');
        if (!el) return;

        const message = el.getAttribute('wire:confirm');

        // Block the click before Livewire sees it
        e.preventDefault();
        e.stopImmediatePropagation();

        Swal.fire({
            ...swalCustom,
            title: 'Confirm',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                // Temporarily remove wire:confirm so Livewire won't show native dialog
                el.removeAttribute('wire:confirm');
                el.click();
                // Restore after Livewire processes the click
                requestAnimationFrame(() => {
                    el.setAttribute('wire:confirm', message);
                });
            }
        });
    }, true); // ← capture phase: runs BEFORE Livewire's handlers
</script>
