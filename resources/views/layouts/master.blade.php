<!DOCTYPE html>
<html lang="en">

    <script>
        // Replace 'your-element-id' with the actual ID of your HTML element
        var element = document.querySelector('html');
        element.classList.add(localStorage.getItem('light-layout-current-skin'));
    </script>

    @include('components.base.head')
    @yield('style')

    <body class="vertical-layout vertical-menu-modern  navbar-floating footer-static @auth login @endauth" data-open="click" data-menu="vertical-menu-modern" data-col="">
        @include('components.base.menu')

        @yield('content')

        @include('components.base.footer')
        @include('components.base.scripts')
        @yield('scripts')
    </body>

</html>
