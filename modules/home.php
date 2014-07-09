<div id='search_container'>
	<input type='search' id='search' placeholder='Enter book title or author...' />
	<div id='results'></div>
</div>
<div id='shelf'>
    <div id='list'>
        <?php
		if (empty($_SESSION['userid'])) {
			?>
            <span style='color:#ddd; font-size:50px'>LOGIN / REGISTER TO BEGIN...</span>
            <?php
		}
		?>
    </div>
</div>
<div id='related'></div>

<?php
if (!empty($_SESSION['userid'])) {
	?>
	<script>
	$(document).ready(function() {
		list.getShelf();
	});
	</script>
	<?php
}
?>