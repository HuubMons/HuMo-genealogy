/**
 * Automatically opens and closes Bootstrap dropdowns on hover.
 * Aug. 2025 added by Huub.
 */
document.querySelectorAll('.dropdown').forEach(function (drop) {
    drop.addEventListener('mouseenter', function () {
        let btn = drop.querySelector('[data-bs-toggle="dropdown"]');
        if (btn) btn.click();
    });
    drop.addEventListener('mouseleave', function () {
        let menu = drop.querySelector('.dropdown-menu');
        if (menu && menu.classList.contains('show')) {
            btn = drop.querySelector('[data-bs-toggle="dropdown"]');
            if (btn) btn.click();
        }
    });
});
