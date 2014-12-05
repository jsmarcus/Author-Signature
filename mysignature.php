<?php
/*
Plugin Name: Author Signature
Plugin URI: https://wordpress.org/plugins/author-signature/
Description: It displays the author signature after each post.
Version: 1.1
Author: Zakir Sajib
Author URI: https://www.odesk.com/users/%7E0173a11de60c8f353e
License: GPL2


 Copyright 2014  Zakir Sajib  (email : zakirsajib@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



if (!class_exists("MySignature")){
			class MySignature {
					function __construct(){
						# Actions
							add_action('admin_menu', array($this,'mysignature_menu'));
						# Filters
							add_filter('the_content', array($this, 'post_signature'));
					}
					
					# what this plugin does
					function post_signature($content){
									global $post;
									$options = get_option('adminPage-group');
									$author_id = $post->post_author;
									$author = get_userdata($author_id);
									$sig = '<p class="my-signature">&mdash; '.$author->display_name.'</p>';
								
									if (($post->post_type == 'page') && $options['pages'])
										$content .= $sig;
									if (($post->post_type == 'post') && $options['posts'])
										$content .= $sig;
							
									return $content;
					}
					

					# Settings page
					function adminPage(){					
							?>							
							<div class="wrap">
								<h2><?php _e('Author Signature Settings', 'mysignature'); ?></h2>
								<form method="post" id="mysignature" action="options.php">
									<?php settings_fields('adminPage-group'); ?>
									<?php do_settings_sections('adminPage-group'); ?>
									<?php $options = get_option('adminPage-group'); ?>
									
												<table class="form-table">
													<tr valign="top">
													<th scope="row"><?php _e('Display', 'adminPage-group');?></th>
													<td><p><input type="checkbox" name="adminPage-group[posts]" value="1" <?php checked(1, $options['posts']) ?> />
													<?php _e("Add the author's signature to posts.", 'mysignature'); ?></p>
													
													<p><input type="checkbox" name="adminPage-group[pages]" value="1" <?php checked(1, $options['pages']) ?> />
													<?php _e("Add the author's signature to pages.", 'mysignature'); ?></p>
													</td>
													</tr>
												</table>
												
											<p class="submit">
													<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'mysignature') ?>" />
											</p>
								</form>
							</div>
							<?php
					}
					
					# admin panel into Settings menu
					function mysignature_menu(){
							if(function_exists('add_options_page')){
							
							# add_options_page will create a sub menu page to the Settings menu.
							#add_options_page('My Signature', 'Author Signature', 9, basename(__FILE__), array($this, 'displayAdminPage'));
							
							add_menu_page('My Signature', 'Author Signature Settings', 'administrator', __FILE__, array($this, 'adminPage'), plugins_url('/images/icon.png', __FILE__));
							
							
							# set defaults
							$options = array(
											'posts' => 1,
											'pages' => 0
							);
							add_option('adminPage-group', $options, '', 'yes'); 
							add_action('admin_init', array($this, 'register_settings'));
							}
					}
					
					# Wordpress internal registration
					function register_settings(){
								register_setting('adminPage-group', 'adminPage-group');
								register_setting('adminPage-group', 'adminPage-group');
					}
					
			}// End Class
}




# Object Creation here: Important
if (class_exists("MySignature")){		
		$signature_obj = new MySignature();
}

?>


