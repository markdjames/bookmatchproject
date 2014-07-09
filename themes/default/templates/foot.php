   
    <div style='height:30px; clear:both'></div>
  
    <footer>
      
    </footer>
  
</div> <!--! end of #container -->

<div id='screener'></div>
<div id='modal_wrap'><div id='modal'><div id='modal_inner'></div></div></div>

<script src="<?=BASE?>/js/libs/jquery-ui-timepicker.js"></script>

<?php if ($is_admin && !MOBILE) { ?>
<script>
CKEDITOR_BASEPATH = "<?=BASE?>/core/tools/ckeditor/";
</script>
<script src="<?=BASE?>/tools/ckeditor/ckeditor.js?v=<?=date('H')?>"></script>
<script>
images.path = '<?=$_GET['id']?>';
$(window).load(function() {
	$('.page_image').each(function() {
		//images.setForEdit($(this));
	});
});
</script>
<?php } ?>
<script src="<?=BASE?>/tools/mediaelement/build/mediaelement-and-player.min.js"></script>

<?php if (!empty($_SESSION['error'])) { ?>
<script>
	alert("<?=$_SESSION['error']?>");
</script>
<?php } ?>

<?php if (!isset($_SESSION["cookies_disabled"])) { ?>
<script>
	
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-45287589-1']);
	_gaq.push(['_trackPageview']);
	(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	
	ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
	
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
	
</script>
<?php } ?>

</body>
</html>
