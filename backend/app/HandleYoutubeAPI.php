<?php

namespace App;

use Google_Client;
use Google_Service_YouTube;
use Google_Service_Drive;
use Debug;
use Log;

class HandleYoutubeAPI
{
    private static $youtube;
    private const APIKEY = "AIzaSyAPO5w-vOzRqn_e-YoIkzLyxu_607oCgyg";

    private $videoIDs;
    private $channelIDs;
    private $channelTitles;
    private $channelThumbnails;
    private $countries;
    private $videoTitles;
    private $videoDescriptions;
    private $videoThumbnails;
    private $concurrentViewers;
    private $actualStartTimes;

    public static function GetAPIKey(){
        return self::APIKEY;
    }

    //APIキー認証を行う
    public function APIKeyAuthorization(){
        if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
            throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
        }
        require_once __DIR__ . '/../vendor/autoload.php';

        $client = new Google_Client();
        $client->setDeveloperKey(self::APIKEY);
        $this::$youtube = new Google_Service_YouTube($client);
    }

    //OAuth認証を行う
    public function OAuthAuthorization(){
        if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
            throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
        }
        require_once __DIR__ . '/../vendor/autoload.php';
        session_start();

        $OAUTH2_CLIENT_ID = '1043223131231-dc775t4pqouh8jmtka4g1plih737f10s.apps.googleusercontent.com';
        $OAUTH2_CLIENT_SECRET = 'lkpoUiMkFSvgSrdfzKNlYOvb';

        $client = new Google_Client();
        $client->setClientId($OAUTH2_CLIENT_ID);
        $client->setClientSecret($OAUTH2_CLIENT_SECRET);
        $client->setScopes('https://www.googleapis.com/auth/youtube');
        $redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'], FILTER_SANITIZE_URL);
        $client->setRedirectUri($redirect);

        $this::$youtube = new Google_Service_YouTube($client);

        $tokenSessionKey = 'token-' . $client->prepareScopes();
        if (isset($_GET['code'])) {
            if (strval($_SESSION['state']) !== strval($_GET['state'])) {
                die('The session state did not match.');
            }
            $client->authenticate($_GET['code']);
            $_SESSION[$tokenSessionKey] = $client->getAccessToken();
            header('Location: ' . $redirect);
            exit();
        }
        if (isset($_SESSION[$tokenSessionKey])) {
            $client->setAccessToken($_SESSION[$tokenSessionKey]);
        }
        if ($client->getAccessToken()) { // OAuth認証されているとき
            $_SESSION[$tokenSessionKey] = $client->getAccessToken();
        } else { // OAuth認証されていない時
            $state = mt_rand();
            $client->setState($state);
            $_SESSION['state'] = $state;
            $authUrl = $client->createAuthUrl();
            header('Location: ' . $authUrl);
            exit();
        }
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

        try{
            $params = array(
                'eventType' => 'live',
                'type' => 'video',
                'regionCode' => 'JP',
                'relevanceLanguage' => 'ja',
                'maxResults' => 25,
                'order' => 'viewCount',
                'safeSearch' => 'none',
            );

            //ビデオIDを指定するときに使用する文字列の最大文字数はおそらく500文字なので、ビデオIDが41個よりも多くなるとエラーが出る
            //ビデオIDの文字列は11文字、カンマ含めて12文字
            //クエリの上限は10000

            for($i = 0; $i < 1; $i++){
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

                $searchResponse = $this::$youtube->search->listSearch("id, snippet", $params);

                print("検索結果:" . $searchResponse["pageInfo"]["totalResults"] . "<br>");

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
                            $tmpChannelThumbnails[$j] = $item['snippet']['thumbnails']['url'];
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

                foreach ($videoResponse['items'] as $item) {
                    for($j = 0; $j < count($tmpVideoIDs); $j++){
                        if($item['id'] == $tmpVideoIDs[$j]){
                            $tmpVideoTitles[$j] = $item['snippet']['title'];
                            $tmpVideoDescriptions[$j] = $item['snippet']['description'];
                            $tmpVideoThumbnails[$j] = $item['snippet']['thumbnails']['url'];
                            $tmpConcurrentViewers[$j] = $item['liveStreamingDetails']['concurrentViewers'];
                            $tmpActualStartTimes[$j] = $item['liveStreamingDetails']['actualStartTime'];
                        }
                    }
                }

                /* 日本のチャンネルじゃない情報を配列から削除 */
                for($j = (count($tmpVideoIDs) - 1); $j >= 0; $j--){

                    /* 日本のチャンネルの場合、もしくはタイトルに日本語が含まれる場合 */
                    if($tmpCountries[$j] == "JP" or preg_match( "/[ぁ-ん]+|[ァ-ヴー]+/u", $tmpVideoTitles[$j])){
                        /* 何もしない */
                    }
                    else{
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
                }

                for($j = 0; $j < count($tmpVideoTitles); $j++){
                    print("title:" . $tmpVideoTitles[$j] . ", country:" . $tmpCountries[$j] . ", concurrentViewers:" . $tmpConcurrentViewers[$j] . "<br>");
                }

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
                    print("nextPageToken:" . $searchResponse["nextPageToken"] . "<br>");
                    $nextPageToken = $searchResponse["nextPageToken"];
                    $params['pageToken'] = $nextPageToken;
                }
                /* 次のページが存在しない場合 */
                else{
                    break;
                }
            }
        } catch (Google_Service_Exception $e) {
            echo sprintf('<p>A service error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage()));
        } catch (Google_Exception $e) {
            echo sprintf('<p>An client error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage()));
        }
    }
}
?>
