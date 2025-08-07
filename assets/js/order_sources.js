// Order sources using drag and drop using jquery and jqueryui.
document.querySelectorAll('.sortable').forEach(function (sortableList) {
    var handleClass = sortableList.getAttribute('data-handle');
    $('#' + sortableList.id).sortable({
        handle: '.' + handleClass
    }).bind('sortupdate', function () {
        var orderstring = "";
        var order_arr = document.getElementsByClassName(" " + handleClass);
        for (var z = 0; z < order_arr.length; z++) {
            orderstring += order_arr[z].id + ";";
        }
        orderstring = orderstring.substring(0, orderstring.length - 1);
        $.ajax({
            url: "include/drag.php?drag_kind=sources&sourcestring=" + orderstring,
            success: function (data) { },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.status);
                alert(thrownError);
            }
        });
    });
});