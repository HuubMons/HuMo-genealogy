$(function () {
    $(".place-autocomplete").autocomplete({
        source: autocompleteSource,
        minLength: 2
    });
});