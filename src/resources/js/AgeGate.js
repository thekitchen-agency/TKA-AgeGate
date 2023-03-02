document.addEventListener('DOMContentLoaded', function() {
    console.log('loaded');

    if(document.getElementById('agegate')) {
        document.body.classList.add('agegate-active');

        document.querySelector('#agegate .decline-ag').addEventListener('click', function(e) {
            e.preventDefault();
            // document.querySelector('#agegate').classList.add('hidden');
            window.location = window.agegatesettings.declineUrl;
        });
        document.querySelector('#agegate .confirm-ag').addEventListener('click', function(e) {
            e.preventDefault();
            setCookie();
            document.body.classList.remove('agegate-active');

            if(window.agegatesettings.displayType === 'modal') {
                document.getElementById('agegate').remove();
                document.querySelector('.ag-verifyOverlay').remove();
            }

            if(window.agegatesettings.displayType === 'redirect') {
                window.location = window.originalSrcUrl;
            }
        });
    }
});

function setCookie() {
    let cookieName = window.agegatesettings.cookieName;
    document.cookie = `${cookieName}=1; SameSite=none; Secure; path=/`;
}
