window.onload = initExpand;
$(document).ready(function(){$("#altable").tablesorter();$("#artable").tablesorter();$("#sltable").tablesorter();});

function toggleExpand(t) {
  for (var i=0; i<3; i++) {
    var t = t.parentNode;
    if (t.className == 'hide') {
      t.className = 'show';
      break;
    }
  }
}

function initExpand() {
  var e = document.getElementsByTagName('*');
  for (var i=0; i<e.length; i++) {
    if (e[i].className == 'hide') {
      e[i].className = 'show';
    }
    if (e[i].className == 'trigger') {
      e[i].setAttribute('onclick',   'toggleExpand(this)');
      e[i].setAttribute('onkeypress','toggleExpand(this)');
    }
  }
}
