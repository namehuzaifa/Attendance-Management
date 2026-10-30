@extends('layouts.master')
@section('title', 'Dashboard')

@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row"></div>
        <div class="content-body">

            @if (auth()->user()->isAdmin())
                @include('modules.admin.dashboard.partials.admin')
            @else
                @include('modules.admin.dashboard.partials.employee')
            @endif

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Live clock on the punch card — the date header used to be static, so a
    // tab left open overnight showed yesterday.
    (function () {
        var clock = document.getElementById('hrmsClock');
        var dateEl = document.getElementById('hrmsDate');
        if (!clock) return;

        function tick() {
            var now = new Date();
            var h = now.getHours();
            var m = String(now.getMinutes()).padStart(2, '0');
            var s = String(now.getSeconds()).padStart(2, '0');
            var ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;

            clock.innerHTML = h + ':' + m + ':' + s + ' <small>' + ampm + '</small>';

            if (dateEl) {
                dateEl.textContent = now.toLocaleDateString(undefined, {
                    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
                });
            }
        }

        tick();
        setInterval(tick, 1000);
    })();

    // Attach coordinates to the punch when the browser will share them, so a
    // mobile check-in can be located the same way the app does it.
    (function () {
        var forms = document.querySelectorAll('form[data-geo-punch]');
        if (!forms.length || !navigator.geolocation) return;

        forms.forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (form.dataset.located === 'yes') return;

                e.preventDefault();
                var btn = form.querySelector('button[type=submit]');
                if (btn) { btn.disabled = true; btn.dataset.label = btn.innerHTML; btn.innerHTML = 'Locating…'; }

                navigator.geolocation.getCurrentPosition(function (pos) {
                    form.querySelector('[name=latitude]').value = pos.coords.latitude;
                    form.querySelector('[name=longitude]').value = pos.coords.longitude;
                    form.dataset.located = 'yes';
                    form.submit();
                }, function () {
                    // Location is a nice-to-have, never a blocker.
                    form.dataset.located = 'yes';
                    form.submit();
                }, { timeout: 6000, maximumAge: 60000 });
            });
        });
    })();
</script>

@if (auth()->user()->isAdmin())
<script src="{{ asset('/') }}app-assets/vendors/js/charts/apexcharts.min.js"></script>
<script>
    (function () {
        var el = document.querySelector('#hrmsTrendChart');
        if (!el || typeof ApexCharts === 'undefined') return;

        var trend = @json($trend ?? []);

        new ApexCharts(el, {
            chart: { type: 'area', height: 280, toolbar: { show: false }, parentHeightOffset: 0 },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            colors: ['#7367f0', '#ff9f43'],
            series: [
                { name: 'Present', data: trend.map(function (d) { return d.present; }) },
                { name: 'Late',    data: trend.map(function (d) { return d.late; }) }
            ],
            xaxis: {
                categories: trend.map(function (d) { return d.label; }),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { fontSize: '11px' } }
            },
            yaxis: { labels: { formatter: function (v) { return Math.round(v); } } },
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 95] }
            },
            grid: { borderColor: 'rgba(0,0,0,0.06)', strokeDashArray: 4 },
            legend: { position: 'top', horizontalAlign: 'right' },
            tooltip: { shared: true }
        }).render();
    })();
</script>
@endif
@endsection
