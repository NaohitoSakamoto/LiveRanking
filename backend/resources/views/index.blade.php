<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>生放送ランキング</title>
        <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    </head>
    <body>
        <div class="items">
            @for ($i = 0; $i < count($liveData['videoIDs']); $i++)
                <div class="item">
                    <img src="{{$liveData['videoThumbnails'][$i]}}" alt="動画のサムネイル">
                    <div class="live-info">
                        <p class="videoTitle"><a href="{{url('https://www.youtube.com/watch?v=' . $liveData['videoIDs'][$i])}}">{{$liveData['videoTitles'][$i]}}</a></p>
                        <p class="videoInfo">
                            <a href="{{url('https://www.youtube.com/channel/' . $liveData['channelIDs'][$i])}}">{{$liveData['channelTitles'][$i]}}</a>
                            ・{{$liveData['concurrentViewers'][$i]}}人が視聴中
                        </p>
                        <p class="videoDetail">{{mb_strimwidth($liveData['videoDescriptions'][$i], 0, 150, "...", "UTF-8")}}</p>
                    </div>
                </div>
            @endfor
        </div>
    </body>
</html>
