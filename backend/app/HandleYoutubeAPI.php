<?php

namespace App;

use Google_Client;
use Google_Service_YouTube;
use Google_Service_Exception;
use Google_Exception;

class HandleYoutubeAPI
{
    private static $youtube;

    //APIキー認証を行う
    public function APIKeyAuthorization(){
        if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
            throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
        }
        require_once __DIR__ . '/../vendor/autoload.php';

        $client = new Google_Client();
        $client->setDeveloperKey(env('YOUTUBE_API_KEY'));
        $this::$youtube = new Google_Service_YouTube($client);
    }

    public function GetLiveInformation(){
        $nextPageToken = "";
        $videoIDs = array();
        $channelIDs = array();
        $channelTitles = array();
        $channelThumbnails = array();
        $countries = array();
        $videoTitles = array();
        $videoDescriptions = array();
        $videoThumbnails = array();
        $concurrentViewers = array();
        $actualStartTimes = array();
		$liveData = array();


		$params = array(
			'eventType' => 'live',
			'type' => 'video',
			'videoType' => 'any',
			'regionCode' => 'JP',
			'relevanceLanguage' => 'ja',
			'maxResults' => 25,
			'order' => 'viewCount',
			'safeSearch' => 'none',
		);

		//ビデオIDを指定するときに使用する文字列の最大文字数はおそらく500文字なので、ビデオIDが41個よりも多くなるとエラーが出る
		//ビデオIDの文字列は11文字、カンマ含めて12文字
		//クエリの上限は10000

		for($i = 0; $i < 5; $i++){
			$tmpVideoIDs = array();
			$tmpChannelIDs = array();
			$tmpChannelTitles = array();
			$tmpChannelThumbnails = array();
			$tmpCountries = array();
			$tmpVideoTitles = array();
			$tmpVideoDescriptions = array();
			$tmpVideoThumbnails = array();
			$tmpConcurrentViewers = array();
			$tmpActualStartTimes = array();
			$videoIDsString = "";
			$channelIDsString = "";

			try{
				$searchResponse = $this::$youtube->search->listSearch("id,snippet", $params);

				print("取得件数:" . count($searchResponse['items']) . "\n");

				/* videoIdとchannelIdを格納していく */
				foreach($searchResponse['items'] as $item){
					array_push($tmpVideoIDs, $item['id']['videoId']);
					array_push($tmpChannelIDs, $item['snippet']['channelId']);
					$videoIDsString .= $item['id']['videoId'] . ",";
					$channelIDsString .= $item['snippet']['channelId'] . ",";
				}

				$channelResponse = $this::$youtube->channels->listChannels('id,snippet', array('id' => $channelIDsString));

				$tmpChannelTitles = array_fill(0, count($tmpChannelIDs), ""); /* チャンネル名を格納する配列 */
				$tmpChannelThumbnails = array_fill(0, count($tmpChannelIDs), ""); /* チャンネルのサムネイルを格納する配列 */
				$tmpCountries = array_fill(0, count($tmpChannelIDs), ""); /* チャンネルの国情報を格納する配列 */

				/*
				チャンネル情報を取得するAPIを叩いたとき、 配列「$tmpChannelIDs」と同じ順番でデータは返ってこないようなので
				配列「$tmpChannelIDs」と同じ順番で配列「$tmpCountries」にAPIを叩いて取得したチャンネルの国の情報を格納する
				*/
				foreach($channelResponse['items'] as $item){
					for($j = 0; $j < count($tmpChannelIDs); $j++){
						if($item['id'] == $tmpChannelIDs[$j]){
							$tmpChannelTitles[$j] = $item['snippet']['title'];
							$tmpChannelThumbnails[$j] = $item['snippet']['thumbnails']['default']['url'];
							$tmpCountries[$j] = $item['snippet']['country'];
						}
					}
				}

				$videoResponse = $this::$youtube->videos->listVideos("id, snippet, liveStreamingDetails", array('id' => $videoIDsString));

				$tmpVideoTitles = array_fill(0, count($tmpVideoIDs), ""); /* 動画タイトルを格納する配列 */
				$tmpVideoDescriptions = array_fill(0, count($tmpVideoIDs), ""); /* 動画詳細を格納する配列 */
				$tmpVideoThumbnails = array_fill(0, count($tmpVideoIDs), ""); /* 動画サムネイルURLを格納する配列 */
				$tmpConcurrentViewers = array_fill(0, count($tmpVideoIDs), 0); /* 同時視聴者数を格納する配列 */
				$tmpActualStartTimes = array_fill(0, count($tmpVideoIDs), 0); /* 開始時間を格納する配列 */

				/* 配列「tmpChannelIDs」と同じ順番で動画のタイトル、動画の説明、動画のサムネイル、同時視聴者数、開始時間を格納していく */
				foreach ($videoResponse['items'] as $item) {
					for($j = 0; $j < count($tmpVideoIDs); $j++){
						if($item['id'] == $tmpVideoIDs[$j]){
							$tmpVideoTitles[$j] = $item['snippet']['title'];
							$tmpVideoDescriptions[$j] = $item['snippet']['description'];
							$tmpVideoThumbnails[$j] = $item['snippet']['thumbnails']['medium']['url'];
							$tmpConcurrentViewers[$j] = $item['liveStreamingDetails']['concurrentViewers'];
							$tmpActualStartTimes[$j] = $item['liveStreamingDetails']['actualStartTime'];
						}
					}
				}

				for($j = (count($tmpVideoIDs) - 1); $j >= 0; $j--){
					/* 同時視聴者数が空のときその放送の情報を配列から削除 */
					if ($tmpConcurrentViewers[$j] == ""){
						//print("同時視聴者にNULLが書き込まれました\n");
						array_splice($tmpVideoIDs, $j, 1);
						array_splice($tmpVideoTitles, $j, 1);
						array_splice($tmpVideoDescriptions, $j, 1);
						array_splice($tmpVideoThumbnails, $j, 1);
						array_splice($tmpConcurrentViewers, $j, 1);
						array_splice($tmpActualStartTimes, $j, 1);
						array_splice($tmpChannelIDs, $j, 1);
						array_splice($tmpChannelTitles, $j, 1);
						array_splice($tmpChannelThumbnails, $j, 1);
						array_splice($tmpCountries, $j, 1);				
					}

					/* 日本のチャンネルの場合、もしくはタイトルに日本語が含まれる場合 */
					// if($tmpCountries[$j] == "JP" or preg_match( "/[ぁ-ん]+|[ァ-ヴー]+/u", $tmpVideoTitles[$j])){
					//     /* 何もしない */
					// }
					// else{
					//     array_splice($tmpVideoIDs, $j, 1);
					//     array_splice($tmpVideoTitles, $j, 1);
					//     array_splice($tmpVideoDescriptions, $j, 1);
					//     array_splice($tmpVideoThumbnails, $j, 1);
					//     array_splice($tmpConcurrentViewers, $j, 1);
					//     array_splice($tmpActualStartTimes, $j, 1);
					//     array_splice($tmpChannelIDs, $j, 1);
					//     array_splice($tmpChannelTitles, $j, 1);
					//     array_splice($tmpChannelThumbnails, $j, 1);
					//     array_splice($tmpCountries, $j, 1);
					// }
				}

				print(($i + 1) . "回目 データ数 : " . count($tmpVideoIDs) . "\n");

				$videoIDs = array_merge($videoIDs, $tmpVideoIDs);
				$channelIDs = array_merge($channelIDs, $tmpChannelIDs);
				$channelTitles = array_merge($channelTitles, $tmpChannelTitles);
				$channelThumbnails = array_merge($channelThumbnails, $tmpChannelThumbnails);
				$countries = array_merge($countries, $tmpCountries);
				$videoTitles = array_merge($videoTitles, $tmpVideoTitles);
				$videoDescriptions = array_merge($videoDescriptions, $tmpVideoDescriptions);
				$videoThumbnails = array_merge($videoThumbnails, $tmpVideoThumbnails);
				$concurrentViewers = array_merge($concurrentViewers, $tmpConcurrentViewers);
				$actualStartTimes = array_merge($actualStartTimes, $tmpActualStartTimes);

				/* 次のページが存在する場合 */
				if($searchResponse["nextPageToken"] != ""){
					$nextPageToken = $searchResponse["nextPageToken"];
					$params['pageToken'] = $nextPageToken;
				}
				/* 次のページが存在しない場合 */
				else{
					break;
				}
			} catch (Google_Service_Exception $e) {
				echo "エラー : Google_Service_Exception\n", $e->getMessage(), "\n";
				exit;
			} catch (Google_Exception $e) {
				echo "エラー : Google_Exception\n", $e->getMessage(), "\n";
				exit;
			}
		}

		/* それぞれのライブ情報の配列を1つに変換して戻り値とする */
		$liveData = array(
			'videoIDs' => $videoIDs,
			'channelIDs' => $channelIDs,
			'channelTitles' => $channelTitles,
			'channelThumbnails' => $channelThumbnails,
			'countries' => $countries,
			'videoTitles' => $videoTitles,
			'videoDescriptions' => $videoDescriptions,
			'videoThumbnails' => $videoThumbnails,
			'concurrentViewers' => $concurrentViewers,
			'actualStartTimes' => $actualStartTimes,
		);

        return $liveData;
    }
}
?>
