@php
    use App\Http\Controllers\AppDownloadController;

    $available = AppDownloadController::isAvailable();
    $url = route('app.download');
    $size = AppDownloadController::size();
@endphp

@if ($available)
    {{-- The details live in a dialog so neither the sign-in screen nor the
         dashboard carries a block nobody asked for. Triggered by the navbar
         icon and by the link under the sign-in button. --}}
    <div class="modal fade" id="appDownloadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Get the Android app</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    <p class="mb-1" style="font-size:.88rem">
                        Mark attendance, request leave and check your month — from your phone.
                    </p>

                    <div class="hrms-appcta__qr d-inline-block">
                        <div id="appDownloadQr" title="Scan to download the HRMS app"></div>
                    </div>

                    <p class="text-muted mt-1 mb-0" style="font-size:.78rem">
                        Scan with your phone's camera
                    </p>
                </div>

                <div class="modal-footer flex-column align-items-stretch">
                    <a href="{{ $url }}" class="btn btn-primary w-100">
                        <i data-feather="download" class="me-25"></i>
                        Download APK @if ($size) <span class="opacity-75">({{ $size }})</span> @endif
                    </a>
                    <p class="hrms-appcta__note text-center mb-0">
                        Android will ask you to allow installs from this source — that is
                        normal for an app distributed outside the Play Store.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/qrcode.js') }}"></script>
    <script>
        (function () {
            var target = document.getElementById('appDownloadQr');
            if (!target || typeof qrcode === 'undefined') return;

            // Type 0 lets the library pick the smallest version that fits.
            var qr = qrcode(0, 'M');
            qr.addData(@json($url));
            qr.make();

            target.innerHTML = qr.createSvgTag({ cellSize: 4, margin: 2, scalable: true });

            var svg = target.querySelector('svg');
            if (svg) {
                svg.setAttribute('width', '170');
                svg.setAttribute('height', '170');
            }
        })();
    </script>
@endif
