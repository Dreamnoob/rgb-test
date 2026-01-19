
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.wpcf7-submit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            showLoadingIndicator();
        });
    });
});

document.addEventListener('wpcf7mailsent', function (event) {
    hideLoadingIndicator();
}, false);

// Обробники подій Contact Form 7
document.addEventListener('wpcf7mailfailed', function (event) {
    hideLoadingIndicator();
}, false);

document.addEventListener('wpcf7mailsent', function (event) {
    hideLoadingIndicator();
}, false);

document.addEventListener('wpcf7invalid', function (event) {
    hideLoadingIndicator();
}, false);

document.addEventListener('wpcf7spam', function (event) {
    hideLoadingIndicator();
}, false);

document.addEventListener('wpcf7aborted', function (event) {
    hideLoadingIndicator();
}, false);

document.addEventListener('wpcf7invalid', function (event) {
    hideLoadingIndicator();
}, false);

function showLoadingIndicator() {
    document.querySelectorAll('.form-loader').forEach(function (loader) {
        loader.classList.add('active');
    });
}

function hideLoadingIndicator() {
    document.querySelectorAll('.form-loader').forEach(function (loader) {
        loader.classList.remove('active');
    });
}
