<!doctype html>
<html>
    <head>

        @include('components/front/meta')

    </head>

    <body>

        @include('components/front/header')

        @yield('header-bottom')

        <div class="container minhitcon">
            @yield('content')
        </div>




        @include('components/front/footer')

        @include('components/front/scripts')


    </body>
</html>
