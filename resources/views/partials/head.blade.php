<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.confirmDeletion = function(wire, id, method = 'deleteTicket', itemType = 'ticket') {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this " + itemType + "?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            background: '#ffffff',
            color: '#333333'
        }).then((result) => {
            if (result.isConfirmed) {
                wire.call(method, id);
            }
        })
    }

    window.confirmAction = function(wire, id, method, title = 'Are you sure?', text = "You won't be able to revert this!", confirmBtnText = 'Yes, do it!', icon = 'warning') {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#3085d6',
            confirmButtonText: confirmBtnText,
            background: '#ffffff',
            color: '#333333'
        }).then((result) => {
            if (result.isConfirmed) {
                wire.call(method, id);
            }
        })
    }
</script>
