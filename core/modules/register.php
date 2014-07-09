<?php
if (empty($_SESSION['userid'])) {   
	?>
	<form id='register' action="" method="post" style='padding:2%;' onsubmit='return false;'>
		
        <label style='width:47%; margin-right:4%; float:left'>Firstname<br />
		<input name="firstname" type="text" class='required' value=""  /></label>
        
        <label style='width:47%; clear:none; float:left'>Surname<br />
		<input name="surname" type="text" class='required' value=""  /></label>
        <div style='clear:both'></div>
        <label>Email<br />
		<input name="register_email" type="text" class='required' value="<?=(isset($_SESSION['register_email']))?$_SESSION['register_email']:"";?>"  /></label>
		<label>Choose Password<br />
		<input name="register_password" type="password" autocomplete="off" /></label>
		<label>Confirm Password<br />
		<input name="register_password_confirm" type="password" autocomplete="off" /></label>
		        
        <div style='clear:both; padding:20px 0'>
        	<p><strong>Data Protection</strong> <a style='font-size:13px;' rel='data_protection' class='help'>info</a></p>
            <p>Please let us know if you are happy for us to contact you in the following ways:</p>
            <label style='font-size:13px'><input name="mailinglist" type="checkbox" value='1' /> by Email</label>
            <label style='font-size:13px'><input name="post_mailinglist" type="checkbox" value='1' /> by Post</label>
            <label style='font-size:13px'><input name="phone_mailinglist" type="checkbox" value='1' /> by Phone/SMS</label>
            <label style='font-size:13px; margin-top:20px;'><input name="share_data" type="checkbox" value='1' /> Are you happy for us to share your data with other like-minded arts organisations?</label>
      	</div>
                
        <p style='margin-top:30px; font-size:12px; clear:both;'>Before registering please make sure you are aware of our <a href="<?=DIR?>/privacy">privacy policy</a> and <a href="<?=DIR?>/terms_and_conditions">terms and conditions</a>.</p>
					
		<input type="hidden" name="function" id="function" value="" data-val="user_register" />
		<input type="hidden" name="token" id="token" value="<?=$_SESSION['token']?>" />
		<input type="button" value="Register" alt="Register" onclick="$('#function').val($('#function').data('val')); document.getElementById('register').onsubmit=''; checkForm('register');" />
	</form>
	<?php
} else {
	?>
	<p><em>You are already registered</em></p>
	<?php
}
?>
<div style='clear:both'></div>
