var pageX = 0;
var pageY = 0;

function setListScroll(t) {
    if (window.pageYOffset != undefined) {
        pageX = pageXOffset;
        pageY = pageYOffset;
    } else {
        var sx, sy, d = document,
            r = d.documentElement,
            b = d.body;
        sx = r.scrollLeft || b.scrollLeft || 0;
        sy = r.scrollTop || b.scrollTop || 0;
        pageX = sx;
        pageY = sy;
    }
    localStorage.setItem('ListScroll' + window.location.pathname, JSON.stringify({"pageX": pageX, "pageY": pageY}))

}

let scrollPosition = localStorage.getItem('ListScroll' + window.location.pathname);
scrollPosition = JSON.parse(scrollPosition)

$(document).ready(function () {
    if (scrollPosition != null && scrollPosition.pageY > 0) {
        setTimeout(function () {
            window.scrollTo({
                top: scrollPosition.pageY,
                left: scrollPosition.pageX,
                behavior: 'smooth'
            });
        }, 2000)

    }
    localStorage.setItem('ListScroll' + window.location.pathname, JSON.stringify({"pageX": 0, "pageY": 0}))
})

