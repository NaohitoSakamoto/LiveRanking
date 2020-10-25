import React, { Component } from 'react';
import ReactDOM from 'react-dom';

export default class DisplayRanking extends Component {
    constructor(){
        super();
        this.state = {
            ranks:[],　/* ライブ情報を格納する配列 */
            pageNumber: 0, /* 何ページ目を取得するのかに使用する */
	        isLoad: false /* スクロールイベントが連続発火し、1ページずつライブ情報が取得できなくなることを防ぐためのフラグ */
        }
        this.onScroll = this.onScroll.bind(this);
    }

    render() {
        return (
            <React.Fragment>
                <div class="items">
                    <MakeItems ranks = {this.state.ranks}/>
                </div>
            </React.Fragment>
        );
    }

    //コンポーネントがマウントされた時点で動作
    componentDidMount(){
        this.getLiveInformation();
        window.addEventListener("scroll", this.onScroll, false);
    }

    /* スクロールイベント関数 */
    onScroll() {
        /* ライブ情報を取得して1秒間スクロールイベント関数を実行しない */
        if (this.state.isLoad == true) {
            return;
        }
        /* スクロールバーが一番下に来た時 */
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
            this.setState({ pageNumber : this.state.pageNumber + 1});
            this.getLiveInformation();
            this.setState({ isLoad : true});
            /* スクロールイベントが連続発火しスクロールバーが一番下に来た時にすべてのライブ情報を取得するのを防ぐための処理 */
            setTimeout(function(){this.setState({ isLoad : false});}.bind(this), 1000);
            /* 全ページ取得し終わったらスクロールイベントを外す */
            if (this.state.pageNumber >= 3) {
                window.removeEventListener("scroll", this.onScroll, false);
            }
        }
    }

    /* ライブ情報を取得する関数 */
    getLiveInformation() {
        axios
            .get('/api/live_informations', {
                params: {
                    pageNumber: this.state.pageNumber
                }
            })
            .then((res) => {
                res.data.map((rank) => {
                    this.state.ranks.push(rank); /* 取得したライブ情報を格納する */
                });
		        this.setState(this.state.ranks);
            })
            .catch(error => {
                console.log(error)
            });
    }
}

function MakeItems(props){
    return props.ranks.map((rank, index) => {
        return(
            <div class="item" key={index}>
                <div class="live-rank">
                    <p>{index + 1}</p>
                </div>
                <div class="live-thumbnails">
                    <a href={"https://www.youtube.com/watch?v=" + rank.videoID}><img src={rank.videoThumbnail} alt="動画のサムネイル" /></a>
                </div>
                <div class="live-info">
                    <p class="videoTitle"><a href={"https://www.youtube.com/watch?v=" + rank.videoID}>{rank.videoTitle}</a></p>
                    <p class="videoInfo"><a href={"https://www.youtube.com/channel/" + rank.channelID}>{rank.channelTitle}</a>・{rank.concurrentViewer}人が視聴中</p>
                    <p class="videoDetail">{rank.videoDescription}</p>
                </div>
            </div>
        );
    });
}

ReactDOM.render(<DisplayRanking />, document.getElementById('react-display-ranking'));
