// Sortable pages
document.querySelectorAll('.sortable-pages').forEach(function(list) {
    $(list).sortable({
        handle: '.handle'
    }).bind('sortupdate', function() {
        let orderstring = "";
        list.querySelectorAll('.handle').forEach(function(item) {
            orderstring += item.id + ";";
        });
        orderstring = orderstring.slice(0, -1);
        $.ajax({
            url: "include/drag.php?drag_kind=cms_pages&order=" + orderstring,
            success: function(data) {},
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status);
                alert(thrownError);
            }
        });
    });
});

// Sortable categories
const categoriesList = document.getElementById('sortable_categories');
if (categoriesList) {
    $(categoriesList).sortable({
        handle: '.handle'
    }).bind('sortupdate', function() {
        let orderstring = "";
        categoriesList.querySelectorAll('.handle').forEach(function(item) {
            orderstring += item.id + ";";
        });
        orderstring = orderstring.slice(0, -1);
        $.ajax({
            url: "include/drag.php?drag_kind=cms_categories&order=" + orderstring,
            success: function(data) {},
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status);
                alert(thrownError);
            }
        });
    });
}