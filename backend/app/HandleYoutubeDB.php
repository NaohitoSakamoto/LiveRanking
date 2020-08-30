<?php

namespace App;

use YoutubeTable;

class HandleYoutubeDB
{
    public static function InsertDB($liveData){
        for ($i = 0; $i < count($liveData['videoIDs']); $i++) {
            $youtubeTable = new YoutubeTable;
            $youtubeTable->videoID = $liveData['videoIDs'][$i];
            $youtubeTable->channelID = $liveData['channelIDs'][$i];
            $youtubeTable->channelTitle = $liveData['channelTitles'][$i];
            $youtubeTable->channelThumbnail = $liveData['channelThumbnails'][$i];
            $youtubeTable->country = $liveData['countries'][$i];
            $youtubeTable->videoTitle = $liveData['videoTitles'][$i];
            $youtubeTable->videoDescription = mb_strimwidth($liveData['videoDescriptions'][$i], 0, 150, "...", "UTF-8");
            $youtubeTable->videoThumbnail = $liveData['videoThumbnails'][$i];
            $youtubeTable->concurrentViewer = $liveData['concurrentViewers'][$i];
            $youtubeTable->actualStartTime = $liveData['actualStartTimes'][$i];
            $youtubeTable->save();
        }
    }
}
?>
