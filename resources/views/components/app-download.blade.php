@props(['variant' => 'card'])

@php
    use App\Http\Controllers\AppDownloadController;

    $available = AppDownloadController::isAvailable();
    $url = route('app.download');
    $size = AppDownloadController::size();
    $id = 'appQr' . \Illuminate\Support\Str::random(6);
@endphp

@if ($available)
    <div {{ $attributes->merge(['class' => 'hrms-appcta hrms-appcta--' . $variant]) }}>
        <div class="hrms-appcta__body">
            <div class="hrms-appcta__text">
                <span class="hrms-appcta__badge">
                    <i data-feather="smartphone"></i> Android app
                </span>

                <h5 class="hrms-appcta__title">Mark attendance from your phone</h5>

                <p class="hrms-appcta__sub">
                    Scan the code with your phone's camera, or download it here.
                    @if ($size) <span class="text-muted">({{ $size }})</span> @endif
                </p>

                <a href="{{ $url }}" class="btn btn-primary btn-sm">
                    <i data-feather="download" class="me-25"></i> Download APK
                </a>
            </div>

            <div class="hrms-appcta__qr">
                <div id="{{ $id }}" title="Scan to download the HRMS app"></div>
                <small class="text-muted d-block text-center mt-50">Scan to install</small>
            </div>
        </div>

        <p class="hrms-appcta__note">
            Android will ask you to allow installs from this source — that is
            normal for an app distributed outside the Play Store.
        </p>
    </div>

    @once
        <script src="{{ asset('assets/js/qrcode.js') }}"></script>
    @endonce

    <script>
        (function () {
            var target = document.getElementById(@json($id));
            if (!target || typeof qrcode === 'undefined') return;

            // Type 0 lets the library pick the smallest version that fits.
            var qr = qrcode(0, 'M');
            qr.addData(@json($url));
            qr.make();

            target.innerHTML = qr.createSvgTag({ cellSize: 4, margin: 2, scalable: true });

            var svg = target.querySelector('svg');
            if (svg) {
                svg.setAttribute('width', '132');
                svg.setAttribute('height', '132');
            }
        })();
    </script>
@endif
