<div style="width:100%;height:100%;">
<noscript>
	<div>Hello, you either have JavaScript turned off or an old version of Macromedia\'s Flash Player.<br/> Get the latest Flash player.</div>
</noscript>
<div id="flashembed-<?php echo $data["module_id"]; ?>" class="flashembed"></div>
</div>
<style>
#flashembed-<?php echo $data["module_id"]; ?> {
	width: <?php echo $width; ?>px;
	height: <?php echo $height; ?>px;
}
</style>

<script>
jQuery(document).ready(function(){
	flashembed("flashembed-<?php echo $data["module_id"]; ?>", "<?php echo $flash; ?>");																										
});
</script>
