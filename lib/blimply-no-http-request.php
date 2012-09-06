<?php 
function blimply_admin_notice(){
    echo '<div class="error">
       <p>Blimply was activated, but it requires <a href="http://pear.php.net/package/HTTP_Request" target="_blank">HTTP_Request PEAR package</a> to be installed. If you\'re on shared hosting, contact your hosting provider and ask to enable it.</p>
    </div>';
	
}
add_action('admin_notices', 'blimply_admin_notice');