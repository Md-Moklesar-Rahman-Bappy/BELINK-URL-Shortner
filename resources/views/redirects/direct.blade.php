<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta property="og:title" content="{{Request::route('linkResponse.link')->title}}">
    <meta property="og:description" content="{{Request::route('linkResponse.link')->description}}">

    <title>{{Request::route('linkResponse.link')->title}}</title>

    @foreach(Request::route('linkResponse.link')->pixels as $pixel)
        @include("pixels.{$pixel->type}", ['pixel' => $pixel])
    @endforeach

    @yield('head-end')

    <script>
        var timer = setTimeout(function () {
            window.location = '{!! Request::route('linkResponse.link')->long_url !!}'
        }, 500);
    </script>
</head>
<body>
<noscript>
    Redirecting to <a href="{{Request::route('linkResponse.link')->long_url}}">{{Request::route('linkResponse.link')->long_url}}</a>
</noscript>

@yield('body-end')
</body>
</html>
