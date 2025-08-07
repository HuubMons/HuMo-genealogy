// Show lightgray bars.
var tbl = document.getElementsByClassName("nametbl")[0];
var rws = tbl.rows;
for (var i = 0; i < rws.length; i++) {
    var m_tbs = rws[i].getElementsByClassName("m_namenr");
    var m_nms = rws[i].getElementsByClassName("m_namelst");
    var f_tbs = rws[i].getElementsByClassName("f_namenr");
    var f_nms = rws[i].getElementsByClassName("f_namelst");
    for (var x = 0; x < m_tbs.length; x++) {
        if (parseInt(m_tbs[x].innerHTML, 10) != NaN && parseInt(m_tbs[x].innerHTML, 10) > 0) {
            var percentage = parseInt(m_tbs[x].innerHTML, 10);
            percentage = (percentage * 100) / m_baseperc;
            m_nms[x].style.backgroundImage = "url(images/lightgray.png)";
            m_nms[x].style.backgroundSize = percentage + "%" + " 100%";
            m_nms[x].style.backgroundRepeat = "no-repeat";
            m_nms[x].style.color = "rgb(0, 140, 200)";
        }
    }
    for (var x = 0; x < f_tbs.length; x++) {
        if (parseInt(m_tbs[x].innerHTML, 10) != NaN && parseInt(m_tbs[x].innerHTML, 10) > 0) {
            var percentage = parseInt(f_tbs[x].innerHTML, 10);
            percentage = (percentage * 100) / f_baseperc;
            f_nms[x].style.backgroundImage = "url(images/lightgray.png)";
            f_nms[x].style.backgroundSize = percentage + "%" + " 100%";
            f_nms[x].style.backgroundRepeat = "no-repeat";
            f_nms[x].style.color = "rgb(0, 140, 200)";
        }
    }
}