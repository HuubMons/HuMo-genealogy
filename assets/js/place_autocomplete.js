$(function () {
    $(".place-autocomplete").autocomplete({
        source: "../include/AutocompletePlace.php",
        minLength: 2
    });
});