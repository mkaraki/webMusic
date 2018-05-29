var is_loop = false;
var plock = true;

var scroll_event = 'onwheel' in document ? 'wheel' : 'onmousewheel' in document ? 'mousewheel' : 'DOMMouseScroll';
$(document).on(scroll_event,function(e){e.preventDefault();});
$(document).on('touchmove.noScroll', function(e) {e.preventDefault();});

var docCookies = {
  getItem: function (sKey) {
    if (!sKey || !this.hasItem(sKey)) { return null; }
    return unescape(document.cookie.replace(new RegExp("(?:^|.*;\\s*)" + escape(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*((?:[^;](?!;))*[^;]?).*"), "$1"));
  },
  setItem: function (sKey, sValue, vEnd, sPath, sDomain, bSecure) {
    if (!sKey || /^(?:expires|max\-age|path|domain|secure)$/i.test(sKey)) { return; }
    var sExpires = "";
    if (vEnd) {
      switch (vEnd.constructor) {
        case Number:
          sExpires = vEnd === Infinity ? "; expires=Tue, 19 Jan 2038 03:14:07 GMT" : "; max-age=" + vEnd;
          break;
        case String:
          sExpires = "; expires=" + vEnd;
          break;
        case Date:
          sExpires = "; expires=" + vEnd.toGMTString();
          break;
      }
    }
    document.cookie = escape(sKey) + "=" + escape(sValue) + sExpires + (sDomain ? "; domain=" + sDomain : "") + (sPath ? "; path=" + sPath : "") + (bSecure ? "; secure" : "");
  },
  removeItem: function (sKey, sPath) {
    if (!sKey || !this.hasItem(sKey)) { return; }
    document.cookie = escape(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT" + (sPath ? "; path=" + sPath : "");
  },
  hasItem: function (sKey) {
    return (new RegExp("(?:^|;\\s*)" + escape(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=")).test(document.cookie);
  },
};

function changePlay(){
  if(player.paused){
    player.play();
    play_ui.src="/src/img/play_control/play.svg"
  }else{
    player.pause();
    play_ui.src="/src/img/play_control/pause.svg"
  }
}

function change_volume(){
  docCookies.setItem("volume",player.volume,328320000);
  console.log(player.volume);
}

$(function () {
  $(document).on('drop dragover', function (e) {
    e.stopPropagation();
    e.preventDefault();
  });
});

function init_loop(){
  is_loop = false;
  player.addEventListener("ended", function(){location.reload();}, false);
}

setInterval(function (){
  if (plock == false){
    player.loop = is_loop;
    var playpercentage = ( player.currentTime / player.duration ) * 100;
    $("#seekbar span").css("width", playpercentage+"%");
    var buffpercentage = ( (player.buffered.end(0)) / player.duration ) * 100;
    $("#seekbarbf span").css("width", buffpercentage+"%");
  }
},1000/60);
