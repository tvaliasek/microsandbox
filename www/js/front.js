jQuery(function(){
    $.nette.init();
    
    function deobfuscateMails(){
        var elements = document.querySelectorAll('[href*="mailto:"]');
        if(elements!=null && elements!='undefined'){
            for(var i=0; i < elements.length; i++){
                var href = elements[i].getAttribute('href');
                var content = elements[i].innerHTML;
                elements[i].setAttribute('href', href.replace('#zavinac#', '@'));
                elements[i].innerHTML = content.replace('#zavinac#', '@');
            }
        }
    }
    
    deobfuscateMails();
});