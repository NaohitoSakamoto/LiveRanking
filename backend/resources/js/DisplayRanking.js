import React, { Component } from 'react';
import ReactDOM from 'react-dom';

export default class DisplayRanking extends Component {
    constructor(){
        super();
        this.state = {
            ranks:[],
            pageNumber: 1
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

    onScroll() {
        if (this.hasReachedBottom()) {
            this.setState({ count : this.state.pageNumber + 1});
            getLiveInformation();

            if (this.state.pageNumber >= 4) {
                window.removeEventListener("scroll", this.onScroll);
            }
        }
    };

    hasReachedBottom() {
        return (
            document.body.offsetHeight + document.body.scrollTop === document.body.scrollHeight
        );
    }

    getLiveInformation() {
        axios
            .get('/api/get', {
                params: {
                    pageNumber: this.state.pageNumber
                }
            })
            .then((res) => {
                res.data.map((data) => {
                    this.state.ranks.push({
                        rank: data
                    });
                });
            })
            .catch(error => {
                console.log(error)
            });
    }
}

function MakeItems(props){
    return props.ranks.map((rank, index) => {
        return(
            <div class="item">
                <div class="live-rank">
                    <p>{index}</p>
                </div>
                <div class="live-thumbnails">
                    <a href="https://www.youtube.com/watch?v={rank.videoID}"><img src="{rank.videoThumbnail}" alt="動画のサムネイル" /></a>
                </div>
                <div class="live-info">
                    <p class="videoTitle"><a href="https://www.youtube.com/watch?v={rank.videoID}">{rank.videoTitle}</a></p>
                    <p class="videoInfo"><a href="https://www.youtube.com/channel/{rank.channelID}}">{rank.channelTitle}</a>・{rank.concurrentViewer}人が視聴中</p>
                    <p class="videoDetail">{rank.videoDescription}</p>
                </div>
            </div>
        );
    });
}

ReactDOM.render(<DisplayRanking />, document.getElementById('react-display-ranking'));