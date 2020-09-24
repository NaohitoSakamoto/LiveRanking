import React, { Component } from 'react';
import ReactDOM from 'react-dom';

export default class DisplayRanking extends Component {
    constructor(){
        super();
        this.state = {
            ranks:[],
            pageNumber: 0,
	    isLoad: false
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
	if (this.state.isLoad == true) {
	    return;
	}
	
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
	    this.setState({ pageNumber : this.state.pageNumber + 1});
	    this.getLiveInformation();
	    this.setState({ isLoad : true});
	    setTimeout(function(){this.setState({ isLoad : false});}.bind(this), 1000);
	    if (this.state.pageNumber >= 3) {
		 window.removeEventListener("scroll", this.onScroll, false);
            }
        }
    }

    getLiveInformation() {
        axios
            .get('/api/live_informations', {
                params: {
                    pageNumber: this.state.pageNumber
                }
            })
            .then((res) => {
                res.data.map((rank) => {
                    this.state.ranks.push(rank);
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
