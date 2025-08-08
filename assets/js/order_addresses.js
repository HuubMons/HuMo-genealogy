// Order items using drag and drop using jquery and jqueryui.
document.querySelectorAll('.sortable_addresses').forEach(function (list) {
    $(list).sortable({
        handle: '.handle'
    }).bind('sortupdate', function () {
        var orderstring = "";
        // Only get handles within this list
        var handles = list.querySelectorAll('.handle');
        handles.forEach(function (handle) {
            orderstring += handle.id + ";";
        });
        orderstring = orderstring.slice(0, -1);
        $.ajax({
            url: "include/drag.php?drag_kind=addresses&order=" + orderstring,
            success: function (data) { },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.status);
                alert(thrownError);
            }
        });
    });
});
