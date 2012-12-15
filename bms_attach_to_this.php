<?php
/**
 * @author Mike Lathrop
 * @version 0.0.1
*/

// show admin message
function bms_att_showAdminMessages()
{
	global $post;
	if (current_user_can('edit_post', $post->ID) and $_GET['bms_att_alert'] ) {
		$msg = $_GET['bms_att_alert'];
		echo "<div id='message' class='updated fade'><p><strong>$msg</strong></p></div>";
	}
}

add_action('admin_notices', 'bms_att_showAdminMessages');  

// ========================================================== 

// add link to media browser
function bms_att_attachment_fields_to_edit($form_fields, $post) {

	// only do this stuff if it was called with the query-attachments action.
	if ($_POST['action'] == "query-attachments") {
		
		$post_id = $_POST['post_id'];
		$return_post = $_POST['post_id'];
		$media_parent_id = $post->post_parent;
		$media_id = $post->ID;
		
		if ($post_id == $media_parent_id) {
			$my_label = "This media item is currently attached to this post";
			$my_link = "Unattach it.";
			$my_action= "unattach&bms_att_return_post=$return_post";
		} else if ($media_parent_id == 0) {
			$my_label = "This media item is currently not attached to a post.";
			$my_link = "Attach it to this one.";
			$my_action = "attach&bms_att_id=$post_id&bms_att_return_post=$return_post";
		} else {
			$media_parent_post = get_post($media_parent_id);
			$media_parent_title = $media_parent_post->post_title;
			$my_label = "This media item is currently attached to <em>'$media_parent_title'</em>, id: $media_parent_id.";
			$my_link = "Unattach it from <em>'$media_parent_title'</em>, and attach it to this one instead.";
			$my_action = "attach&bms_att_id=$post_id&bms_att_return_post=$return_post";
		}
		$form_fields["bms_att"]["label"] =  $my_label ;
		$form_fields["bms_att"]["input"] = "html";
		$form_fields["bms_att"]["html"] = '<a href="/?bms_att_media_id='.$media_id.'&bms_att_action='.$my_action.'" >'.$my_link.'</a>';
	}
	return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'bms_att_attachment_fields_to_edit', 10, 2 );

//process stuff if appropriate

add_action('init', 'bms_att_init');

function bms_att_init() {
	if ( $_GET['bms_att_action']) {
		$action = $_GET['bms_att_action'];
		
		// have capability to be here?
		if (current_user_can( 'edit_posts' ) and isset($_GET['bms_att_media_id'])) {
			
			$media_id = $_GET['bms_att_media_id'];
			
            switch ( $action ){
				case ("unattach") :
					$media_post = array();
					$media_post['ID'] = $media_id;
					$media_post['post_parent'] = 0;
					if (wp_update_post($media_post) != 0) {
						$alert_text = 'Media Library Item Unattached';
					} else {
						$alert_text = 'There was a problem unattaching the Media Library Item from this post';
					}
				break;
				case ("attach") :
					if (isset($_GET['bms_att_id'])) {
						$post_id = $_GET['bms_att_id'];
						$media_post = array();
						$media_post['ID'] = $media_id;
						$media_post['post_parent'] = $post_id;
						if (wp_update_post($media_post) != 0) {
							$alert_text = 'Media Library Item now attached to Current Post';
						} else {
							$alert_text = 'There was a problem attaching the Media Library Item to this post';
						}
						
					} else {
						$alert_text = 'incomplete arguement supplied.';
					}
				break;
			}
			if (isset($_GET['bms_att_return_post'])) wp_redirect( admin_url() . 'post.php?post=' . $_GET['bms_att_return_post'] . '&action=edit' . '&bms_att_alert='.urlencode($alert_text)); 
			die;
		} 
		// current user can't edit posts but bms_att_action is set. Hack attempt alert!
		wp_redirect( home_url(), 301 );
	} 
	// bms_att_action is not set. carry on - nothing to see here.
}
?>