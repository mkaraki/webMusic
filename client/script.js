document.getElementsByClassName('controller-playback-position-holder')[0]
    .addEventListener('click', function (event) {
        const pbindicator = document.getElementsByClassName('controller-playback-position-indicator')[0];
        pbindicator.setAttribute("style", `width: ${event.clientX * 100.0 / window.innerWidth}%; `);
    });