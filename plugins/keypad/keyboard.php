<?php
	if(!defined('SM_PATH')) {
	   define('SM_PATH', '../../');
	}
	global $jquery_version;
	if(is_null($jquery_version))
		$jquery_version = 'jquery-1.4.1.min'; 

	include_once SM_PATH . 'config/config.php';
	global $squirrelmail_default_language; 

if(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]),"msie") != 0){
?>
	<style type="text/css">
		 #keypad-div{width:420px !important;} 
	</style>
<?php
}
?>
<style type="text/css">
	.keypad-trigger{
		position:absolute;
		/*display:none;*/
	}
</style>
<link rel="stylesheet" href= "<?php echo SM_PATH;?>plugins/keypad/css/jquery.keypad.css" type="text/css">
<link rel="stylesheet" href="<?php echo SM_PATH;?>plugins/keypad/css/keypadstyle.css" type="text/css">
<script type="text/javascript" src='<?php echo SM_PATH  . "/include/jquery/$jquery_version.js'>";?></script> 
<script type="text/javascript" src="<?php echo SM_PATH;?>plugins/keypad/js/jquery.keypad.js"></script> 
<script type="text/javascript" src="<?php echo SM_PATH;?>plugins/keypad/js/jquery.keypad-en_US.js"></script>
<script type="text/javascript" src="<?php echo SM_PATH;?>plugins/keypad/js/jquery.keypad-pt_BR.js"></script>
<script type="text/javascript">
var posKey = 0;
var lang;
$(function(){	
	if($('[name=select_language]').val() == "0"){ 
		lang = '<?php echo $squirrelmail_default_language;?>';
    }else{
		lang = $('[name=select_language]').val();
	}
	if(lang == "en_US"){
		$('input:password').keypad($.extend({prompt: '',
		layout: $.keypad.qwertyLayout}, $.keypad.regional['en_US']));
	}else if(lang == "pt_BR"){
		$('input:password').keypad($.extend({prompt: '',
	    layout: $.keypad.azertyLayout}, $.keypad.regional['pt_BR']));
	}else{
		$('input:password').keypad($.extend({prompt: '',
        layout: $.keypad.qwertyLayout}, $.keypad.regional['en_US']));
	}

	$(window).resize(
		function(){			
                    positionIcon();
		}
	);
	
	$('input:password').focus(
		function(){
			positionIcon();			
		}
	);	

	positionIcon();

    $("[name=select_language]").change(function(){
        if($(this).val() == "0"){
            lang = '<?php echo $squirrelmail_default_language;?>';
    	}else{
            lang = $(this).val();
	}	
        if(lang == "en_US"){
            $('input:password').keypad('change', $.keypad.regional['en_US']);
            $('input:password').keypad('change', {layout: $.keypad.qwertyLayout});
        }else if(lang == "pt_BR"){
            $('input:password').keypad('change', $.keypad.regional['pt_BR']); 
            $('input:password').keypad('change', {layout: $.keypad.azertyLayout});
        }else{
            $('input:password').keypad('change', $.keypad.regional['en_US']);
            $('input:password').keypad('change', {layout: $.keypad.qwertyLayout});
        }
    });
});
function positionIcon(){
        //alert($('input:password').offset().left);
	//if(posKey == 0)
        //    posKey = $('input:password').offset().left + 155;   /*$('.keypad-trigger').offset().left - 25;*/
        
	//$('.keypad-trigger').css('left',posKey)
	//					.css('z-index','2');
}
</script>

