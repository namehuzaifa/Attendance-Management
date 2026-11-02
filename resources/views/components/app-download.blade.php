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

                    <hr class="my-1">

                    {{-- For iPhones and desktops, where the APK is no use.
                         Chromium offers a real install button; iOS has no such
                         API at all, so there it becomes written steps. --}}
                    <button type="button" class="btn btn-outline-primary w-100 d-none" id="hrmsInstallWebApp">
                        <i data-feather="plus-square" class="me-25"></i> Install the web app
                    </button>

                    <div class="hrms-ios-install d-none" id="hrmsIosInstall">
                        <p class="hrms-ios-install__lead">Install on your iPhone</p>

                        <ol class="hrms-ios-install__steps">
                            <li>
                                Tap the <strong>Share</strong> button
                                {{-- Sized on the element too: relying on the
                                     stylesheet alone drew it full width when
                                     a stale CSS file was cached. --}}
                                <svg class="hrms-ios-install__share" width="15" height="15"
                                     viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                     stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 16V4"/>
                                    <path d="M8 8l4-4 4 4"/>
                                    <path d="M5 13v5a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-5"/>
                                </svg>
                                at the bottom of Safari
                            </li>
                            <li>Scroll down and choose <strong>Add to Home Screen</strong></li>
                            <li>Tap <strong>Add</strong></li>
                        </ol>

                        <p class="hrms-appcta__note mb-0">
                            This only works in Safari — Chrome on iPhone cannot add to the
                            home screen.
                        </p>
                    </div>

                    <p class="hrms-appcta__note text-center mb-0" id="hrmsInstallHint">
                        Or install this site as an app on your phone or computer.
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

        (function () {
            var button = document.getElementById('hrmsInstallWebApp');
            var hint = document.getElementById('hrmsInstallHint');
            var iosSteps = document.getElementById('hrmsIosInstall');
            if (!button) return;

            var standalone = window.matchMedia('(display-mode: standalone)').matches
                || window.navigator.standalone === true;

            if (standalone) {
                button.remove();
                if (iosSteps) iosSteps.remove();
                if (hint) hint.textContent = 'You are already using the installed app.';
                return;
            }

            // iOS has no install API at all — Apple only allows Add to Home
            // Screen by hand — so there the button is replaced by the steps.
            var isIos = /iPad|iPhone|iPod/.test(navigator.userAgent)
                || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

            if (isIos) {
                button.remove();
                if (iosSteps) iosSteps.classList.remove('d-none');
                if (hint) hint.remove();
                return;
            }

            function reveal() {
                if (window.hrmsInstallPrompt) button.classList.remove('d-none');
            }

            // The event may have fired before this dialog was parsed.
            reveal();
            window.addEventListener('hrms:installable', reveal);

            button.addEventListener('click', async function () {
                var prompt = window.hrmsInstallPrompt;
                if (!prompt) return;

                button.disabled = true;
                prompt.prompt();

                var choice = await prompt.userChoice.catch(function () { return null; });

                // The browser only hands out one prompt per event.
                window.hrmsInstallPrompt = null;
                button.classList.add('d-none');
                button.disabled = false;

                if (hint && choice && choice.outcome === 'accepted') {
                    hint.textContent = 'Installed — look for HRMS on your home screen.';
                }
            });
        })();
    </script>
@endif
