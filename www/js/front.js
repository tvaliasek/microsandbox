jQuery(function () {

    if (window.hasOwnProperty('toastr')) {
        toastr.options = {'closeButton': true, 'progressBar': true};

        /* Flash messages */
        if ($('#flashContainer > li').length > 0) {
            $('#flashContainer > li').each(function () {
                var type = $(this).data('flash-type');
                var text = $(this).text();
                switch (type) {
                    case 'success':
                        toastr.success(text);
                        break;
                    case 'error':
                        toastr.error(text);
                        break;
                    case 'warning':
                        toastr.warning(text);
                        break;
                    default:
                        toastr.info(text);
                }
                $(this).remove();
            });
        }
    }

    /* Nette ajax init */
    if ($.hasOwnProperty('nette')) {
        $.nette.init();
    }

    /* Deobfuscate emails hidden in format <a href="mailto:someone#zavinac#somewhere.com">someone#zavinac#somewhere.com</a> */
    function deobfuscateMails() {
        var elements = document.querySelectorAll('[href*="mailto:"]');
        if (elements != null && elements != 'undefined') {
            for (var i = 0; i < elements.length; i++) {
                var href = elements[i].getAttribute('href');
                var content = elements[i].innerHTML;
                elements[i].setAttribute('href', href.replace('#zavinac#', '@'));
                elements[i].innerHTML = content.replace('#zavinac#', '@');
            }
        }
    }

    deobfuscateMails();
});