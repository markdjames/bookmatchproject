<?php
if (empty($_SESSION['userid'])) {
	?>
	<?= ""; //(isset($_SESSION['error']) && !empty($_SESSION['error'])) ? "<p class='error'>".$_SESSION['error']."</p>" : "" ;?>
	<form method='POST' action='' style='padding:2%'>
		<label>Enter your email address<br />
		<input type='email' placeholder='enter your email address here' value='<?=(isset($_SESSION['email']))?$_SESSION['email']:$_SESSION['register_email'];?>' name='forgotten_email' /></label>
		
		<input type='hidden' value='forgotten_password' name='function' />
		<input type='hidden' value='<?=$_SESSION['token']?>' name='token' />
		
		<input type='submit' value='Send Password' />
	</form>
	<?php	
} else {
	?>
    <script>
	document.location.href='/';
	</script>
    <?php
}