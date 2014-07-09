<div id="container">
	
	<header>
    	
    	<div id='user_controls'>
            <?php if ($is_admin) require_once resolve('/lib/admin.php');?>
			<?php
				if ($is_logged_in) { ?>
					<a href='<?=DIR?>/profile'>Your Profile</a> 
					<a onclick="$('#logout').submit();">Logout</a> 
					<? 
				} else {
					?>
					<a href='<?=DIR?>/register'>Register</a> 
					<a rel='modal'  href='<?=DIR?>/login' onclick="modal(0, 'login')">Login</a>
					<?
				}
            ?>
            <form id='logout' method='POST' action=''>
                <input type='hidden' name='token' value='<?=$_SESSION['token']?>'>
                <input type='hidden' name='function' value='logout'>
            </form>
    	</div>
        
	</header>
	