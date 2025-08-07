// Show gray bar in name box. Graphical indication of number of names.
var tbl = document.getElementsByClassName("nametbl")[0];
var rws = tbl.rows;
for (var i = 0; i < rws.length; i++) {
    var tbs = rws[i].getElementsByClassName("namenr");
    var nms = rws[i].getElementsByClassName("namelst");
    for (var x = 0; x < tbs.length; x++) {
        var percentage = parseInt(tbs[x].innerHTML, 10);
        percentage = (percentage * 100) / baseperc;
        if (percentage > 0.1) {
            nms[x].style.backgroundImage = "url(images/lightgray.png)";
            nms[x].style.backgroundSize = percentage + "%" + " 100%";
            nms[x].style.backgroundRepeat = "no-repeat";
            nms[x].style.color = "rgb(0, 140, 200)";
        }
    }
}