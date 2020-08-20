<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>生放送ランキング</title>
    </head>
    <body>
        <div class="items">
            @for ($i = 0; $i < count($liveData['videoIDs']); $i++)
                <div class="item">
                    <img src="{{$liveData['videoThumbnails'][$i]}}" alt="動画のサムネイル">
                    <div class="live-info">
                        <p><a href="{{url('https://www.youtube.com/watch?v=' . $liveData['videoIDs'][$i])}}">{{$liveData['videoTitles'][$i]}}</a></p>
                        <p><a href="{{url('https://www.youtube.com/channel/' . $liveData['channelIDs'][$i])}}">{{$liveData['channelTitles'][$i]}}</a></p>
                        <p><img src="{{$liveData['channelThumbnails'][$i]}}" alt="チャンネルのサムネイル"></p>
                        <p>詳細：{{$liveData['videoDescriptions'][$i]}}</p>
                        <p>同時視聴者数：{{$liveData['concurrentViewers'][$i]}}</p>
                        <p>開始時間：{{$liveData['actualStartTimes'][$i]}}</p>
                    </div>
                </div>
            @endfor
        </div>
    </body>
</html>
