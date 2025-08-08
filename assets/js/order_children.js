// Order children using drag and drop (using jquery and jqueryui).
document.querySelectorAll('.sortable-children').forEach(function (list) {
    var familyId = list.getAttribute('data-family-id');
    $(list).sortable({
        handle: '.child-handle'
    }).bind('sortupdate', function () {
        var childstring = "";
        var handles = list.querySelectorAll('.child-handle');
        handles.forEach(function (handle, idx) {
            childstring += handle.id + ";";
            var chldnum = document.getElementById('chldnum' + handle.id);
            if (chldnum) chldnum.innerHTML = (idx + 1);
        });
        childstring = childstring.slice(0, -1);
        $.ajax({
            url: "include/drag.php?drag_kind=children&chldstring=" + childstring + "&family_id=" + familyId,
            success: function (data) { },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.status);
                alert(thrownError);
            }
        });
    });
});