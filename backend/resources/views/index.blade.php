<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
	<title>生放送ランキング</title>
	<link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
        <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    </head>
    <body>
        <h1><p>Youtube Live Ranking</p></h1>
        <div id="react-display-ranking"></div>
        <script src="{{ asset('js/app.js')}}"></script>
    </body>
</html>
