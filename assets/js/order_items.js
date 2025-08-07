// Order items using drag and drop using jquery and jqueryui.
$('#sortable_items').sortable({
    handle: '.handle'
}).bind('sortupdate', function () {
    var orderstring = "";
    var order_arr = document.getElementsByClassName("handle");
    for (var z = 0; z < order_arr.length; z++) {
        orderstring = orderstring + order_arr[z].id + ";";
        //document.getElementById('ordernum' + order_arr[z].id).innerHTML = (z + 1);
    }

    orderstring = orderstring.substring(0, orderstring.length - 1);
    $.ajax({
        url: url_start + "&order=" + orderstring,
        success: function (data) { },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.status);
            alert(thrownError);
        }
    });
});
