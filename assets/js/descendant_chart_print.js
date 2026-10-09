function showimg() {
    const config = document.getElementById('descendant-chart-print-config');
    const menubox = document.getElementById('menubox');
    const imageButton = document.getElementById('imgbutton');
    const png = document.getElementById('png');

    menubox.style.visibility = 'hidden';
    imageButton.style.visibility = 'hidden';
    document.getElementById('nav-tab').style.visibility = 'hidden';
    png.style.width = config.dataset.width + 'px';
    png.style.height = config.dataset.height + 'px';

    const elements = document.querySelectorAll('.ancestorName');
    elements.forEach((element) => {
        element.style.setProperty('box-shadow', 'none', 'important');
    });

    html2canvas(png).then((canvas) => {
        const image = canvas.toDataURL();

        menubox.style.visibility = 'visible';
        imageButton.style.visibility = 'visible';
        png.style.width = 'auto';
        png.style.height = 'auto';

        const newWindow = window.open();
        newWindow.document.open();
        newWindow.document.write('<!DOCTYPE html><head></head><body>');
        newWindow.document.write(config.dataset.instructions);
        newWindow.document.write('<a href="' + config.dataset.posterazorUrl + '" target="_blank">"' + config.dataset.posterazorLabel + '"</a><br>');
        newWindow.document.write(config.dataset.plotterInstructions);
        newWindow.document.write('<br><br><img src="' + image + '"></body></html>');
        newWindow.document.close();
    });
}
