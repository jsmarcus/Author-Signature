<?php
/*
Plugin Name: Author Signature
Plugin URI: https://wordpress.org/plugins/author-signature/
Description: It displays the author signature after each post.
Version: 1.2
Author: Zakir Sajib
Author URI: https://www.odesk.com/users/%7E0173a11de60c8f353e
Contributor: Gideão Franco
Contributor URI: https://br.linkedin.com/pub/gideao-franco/35/705/852
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
							add_filter('the_excerpt', array($this, 'post_signature'));
					}
					
					# what this plugin does
					function post_signature($content){
									global $post;
									$options = get_option('adminPage-group');
									if ( (($post->post_type == 'page') && $options['pages']) || (($post->post_type == 'post') && $options['posts']) )
									{
										$author_id = $post->post_author;
										$author = get_userdata($author_id);
										$sig = '<p class="my-signature">&mdash; '.$author->display_name.'</p>';

										$tagSign = 'author_' . $author_id;
										$strXML = utf8_encode( base64_decode( $options['signatures'] ) );
										$xmlSign = new SimpleXMLElement( $strXML );
										$contentSign = $xmlSign->{$tagSign};
										if (!isset($contentSign) || trim($contentSign)=='') {
											$contentSign = $sig;
										}

										if (($post->post_type == 'page') && $options['pages'])
											$content .= $contentSign;
										if (($post->post_type == 'post') && $options['posts'])
											$content .= $contentSign;
									}

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
									<?php $usersAdm  = get_users( Array('role' => 'administrator', 'fields' => Array('ID', 'display_name')) ); ?>
									<?php $usersAuth = get_users( Array('role' => 'author', 'fields' => Array('ID', 'display_name')) ); ?>

												<table class="form-table">
													<tr valign="top">
													<th scope="row"><?php _e('Display', 'adminPage-group');?></th>
													<td><p><input type="checkbox" name="adminPage-group[posts]" value="1" <?php checked(1, $options['posts']) ?> />
													<?php _e("Add the author's signature to posts.", 'mysignature'); ?></p>

													<p><input type="checkbox" name="adminPage-group[pages]" value="1" <?php checked(1, $options['pages']) ?> />
													<?php _e("Add the author's signature to pages.", 'mysignature'); ?></p>
													</td>
													</tr>
													<tr valign="top">
													<th scope="row"><?php _e('Signatures', 'adminPage-group');?></th>
													<tr valign="top">
													<th scope="row"><?php _e('Author', 'adminPage-group');?></th>
													<td><select id="selAuthor">
														<option value="0"> - - Select an Author - - </option><?php

														foreach($usersAdm as $key => $val) {
															echo '<option value="' . $val->ID . '">' . $val->display_name . '</option>';
														}

														foreach($usersAuth as $key => $val) {
															echo '<option value="' . $val->ID . '">' . $val->display_name . '</option>';
														}

													?></select>
													</td>
													</tr>
													<tr valign="top">
													<th scope="row"><?php _e('Signature content', 'adminPage-group');?></th>
													<td>
													<textarea rows="8" cols="60" id="txtSignatureShow" form="mysignature" disabled="disabled"></textarea>
													<!--textarea rows="8" cols="60" id="txtSignature" form="mysignature"><?php echo $options['signatures']; ?></textarea-->
													<input type="hidden" id="txtSignaturesHide" name="adminPage-group[signatures]" value="<?php echo $options['signatures']; ?>" />
													<input type="hidden" id="txtSignatureIdOld" value="0" />
													</td>
													</tr>
													<th scope="row"><?php _e('Signature preview', 'adminPage-group');?></th>
													<td id="tdSignPrev" style="border: 1px solid #333333"></td>
													</tr>
												</table>

											<p class="submit">
													<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'mysignature') ?>" />
											</p>
								</form>
							</div>
							<script>

								function GetSignatureXMLDoc() {
									var strXml = atob(jQuery('#txtSignaturesHide').val());

									if (strXml == '') {
										strXml = '<\?xml version="1.0" encoding="UTF-8"\?><signatures><\/signatures>';
									}

									var xmlDoc = jQuery.parseXML(strXml);

									return jQuery(xmlDoc);
								}

								function SaveSignatureOnXML(pId, pContent) {

									if (pId == 0) return;

									var jXml = GetSignatureXMLDoc();

									var strAuthor = 'author_' + pId;

									var oElAuthor = jXml.find(strAuthor);

									if (oElAuthor.length == 0) // Not found
									{ 
										oElAuthor = jXml.find('signatures')
												.append('<' + strAuthor + '/>')
												.find(strAuthor);
									}

									oElAuthor.html('<![CDATA[' + pContent + ']]>');

									var xmlSer = new XMLSerializer();

									var xmlString = xmlSer.serializeToString(jXml[0]);
									jQuery('#txtSignaturesHide').val(btoa(xmlString));
								}

								jQuery('#selAuthor').on('change', function(){

									// Verify changes and salve
									var idAuthorOld = jQuery('#txtSignatureIdOld').val();
									SaveSignatureOnXML(idAuthorOld, jQuery('#txtSignatureShow').val());

									// Get signature from XML
									var idAuthor = jQuery(this).val();

									jQuery('#txtSignatureShow').val('');
									jQuery('#tdSignPrev').html('');
									jQuery('#txtSignatureIdOld').val(idAuthor);

									if (idAuthor == 0) {
										jQuery('#txtSignatureShow').attr('disabled', 'disabled');
									}
									else {
										jQuery('#txtSignatureShow').removeAttr('disabled');

										var jXml = GetSignatureXMLDoc();
										var strAuthor = 'author_' + idAuthor;

										jXml.find(strAuthor).each(function() {

											var strBad = jQuery(this).html();
											var strSignature;

											try{
											    // If the string is UTF-8, this will work and not throw an error.
											    strSignature = decodeURIComponent(escape(strBad));
											}catch(e){
											    // If it isn't, an error will be thrown, and we can asume that we have an ISO string.
											    strSignature = strBad;
											}

											strSignature = strSignature.replace('<![CDATA[', '').replace(']]>', '');

											jQuery('#txtSignatureShow').val( strSignature );
											jQuery('#tdSignPrev').html( strSignature );
										});
									}
								});

								jQuery('#txtSignatureShow').on('input', function(){
									jQuery('#tdSignPrev').html(jQuery(this).val());
								});

								jQuery('#txtSignatureShow').on('focusout', function(){
									// Verify changes and salve
									var idAuthor = jQuery('#selAuthor').val();
									SaveSignatureOnXML(idAuthor, jQuery('#txtSignatureShow').val());
								});

								jQuery('#mysignature').on('submit', function(){
									// Verify changes and salve
									var idAuthor = jQuery('#selAuthor').val();
									SaveSignatureOnXML(idAuthor, jQuery('#txtSignatureShow').val());
								});

							</script>
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
											'pages' => 0,
											'signatures' => ''
							);
							add_option('adminPage-group', $options, '', 'yes'); 
							add_action('admin_init', array($this, 'register_settings'));
							}
					}

					# Wordpress internal registration
					function register_settings(){
								register_setting('adminPage-group', 'adminPage-group');
								unregister_setting('adminPage-group', 'adminPage-group');
					}

			}// End Class
}




# Object Creation here: Important
if (class_exists("MySignature")){
		$signature_obj = new MySignature();
}

?>


