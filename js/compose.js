$(function(){
    initCompose();
    resizeCompose();
    $('form[name=compose]').ajaxForm({
        target: '#composeInstance1',
        success:  function(){
            initCompose();
            resizeCompose();
        }
    });
}
