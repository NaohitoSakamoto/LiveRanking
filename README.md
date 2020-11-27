# LiveRanking

## このサイトについて
Youtubeライブの同時視聴者数ランキングを表示するサイトです<br>
サイトURL:https://youtube-live-information.herokuapp.com/<br>
以下のURLにある開発環境を利用して作成しました<br>
https://github.com/ucan-lab/docker-laravel<br>

### フロントエンド側の処理
- WebAPIを叩いてサーバーからYoutubeライブの情報を取得する
- 画面の一番下までスクロールすると追加で25件Youtubeライブの情報を取得して表示する

### バックエンド側の処理
- コマンドライン処理でYoutube Data APIを叩いてYoutubeライブ情報を取得してデータベースに格納する
- Webページのリクエストが送られた場合、Viewをクライアントに送る
- WebAPIのリクエストが送られた場合、データベースからYoutubeライブ情報を取得し、クライアントに送る

## 使い方
```bash
$ git clone https://github.com/NaohitoSakamoto/LiveRanking.git
$ cd LiveRanking/infrastructure
$ make init
```

LiveRanking/backend/.envファイルの最終行にYoutube Data API のAPIキーを書き込む
```
YOUTUBE_API_KEY={APIキー}
```

LiveRanking/infrastructureでコマンドラインに以下のコマンドを打ち込む
```bash
$ make get-live-information
```

## Container structure

```bash
├── app
├── web
└── db
```

### app container

- Base image
  - [php](https://hub.docker.com/_/php):7.4-fpm-buster
  - [composer](https://hub.docker.com/_/composer):1.10

### web container

- Base image
  - [nginx](https://hub.docker.com/_/nginx):1.18-alpine
  - [node](https://hub.docker.com/_/node):14.2-alpine

### db container

- Base image
  - [mysql](https://hub.docker.com/_/mysql):8.0
