<?php

require_once('engine/main.php');
require_once('Facebook/autoload.php');

add_theme_support('post-thumbnails');

//add_image_size( 'homepage-standard', 200, 0, true );

add_image_size('w100', 1200, 0, true);

register_nav_menu('primary', 'Menu 1');
register_nav_menu('primary2', 'Menu 2');
register_nav_menu('footer', 'Menu stopka');


engine_register_partners_type('Partnerzy', 'partners', array('title', 'editor'), true);
engine_register_project_type('Projekty', 'projects', array('title', 'editor', 'author'), true);
engine_register_post_type('Spotkania', 'cities', array('title', 'editor'), true);


//engine_register_taxonomy('Technologia', 'technology', array('projects'));
//engine_register_taxonomy('Status projektu', 'status', array('projects'));

engine_register_taxonomy('Filtry', 'filters', array('projects'));

show_admin_bar(false);


if (function_exists('acf_add_options_page')) {

	acf_add_options_page(array(
			'page_title' => 'Ogólne',
			'menu_title' => 'Ogólne',
			'menu_slug' => 'theme-general-settings',
			'capability' => 'edit_users',
			'redirect' => false
	));
}

function get_city_name($city) {

	$cities = array(
			'Poznan' => 'Poznań',
			'Srodmiescie' => 'Warszawa',
			'Gdansk' => 'Trójmiasto',
			'Gdynia' => 'Trójmiasto',
			'Sopot' => 'Trójmiasto',
			'Wroclaw' => 'Wrocław'
	);

	if (array_key_exists($city, $cities)) {
		return $cities[$city];
	} else {
		return $city;
	}
}

function add_roles_kdp() {
	add_role('project-leader', 'Project leader', array('read' => true, 'level_0' => false, 'level_1' => true));

	$role = get_role('project-leader');
	$role->add_cap('level_1');
	
}

add_action('admin_init', 'add_roles_kdp');


add_filter('authenticate', function($user, $email, $password) {

	//Check for empty fields
	if (empty($email) || empty($password)) {
		//create new error object and add errors to it.
		$error = new WP_Error();

		if (empty($email)) { //No email
			$error->add('empty_username', __('<strong>ERROR</strong>: Email field is empty.'));
		} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { //Invalid Email
			$error->add('invalid_username', __('<strong>ERROR</strong>: Email is invalid.'));
		}

		if (empty($password)) { //No password
			$error->add('empty_password', __('<strong>ERROR</strong>: Password field is empty.'));
		}

		return $error;
	}

	//Check if user exists in WordPress database
	$user = get_user_by('email', $email);

	//bad email
	if (!$user) {
		$error = new WP_Error();
		$error->add('invalid', 'Błędny e-mail lub hasło');
		return $error;
	} else { //check password
		if (!wp_check_password($password, $user->user_pass, $user->ID)) { //bad password
			$error = new WP_Error();
			$error->add('invalid', 'Błędny e-mail lub hasło');
			return $error;
		} else {
			return $user; //passed
		}
	}
}, 20, 3);

function login_function() {
	add_filter('gettext', 'username_change', 20, 3);

	function username_change($translated_text, $text, $domain) {
		if ($text === 'Username') {
			$translated_text = 'E-mail';
		}
		return $translated_text;
	}
}
add_action('login_head', 'login_function');


add_filter('avatar_defaults', 'newgravatar');
function newgravatar($avatar_defaults) {
	$myavatar = get_bloginfo('template_directory') . '/images/blank-person.png';
	$avatar_defaults[$myavatar] = "KDP blank";
	return $avatar_defaults;
}

add_filter( 'map_meta_cap', 'my_map_meta_cap', 10, 4 );

function my_map_meta_cap( $caps, $cap, $user_id, $args ) {

	if ( 'edit_project' == $cap || 'delete_project' == $cap || 'read_project' == $cap ) {
		$post = get_post( $args[0] );
		$post_type = get_post_type_object( $post->post_type );

		$caps = array();
	}

	if ( 'edit_project' == $cap ) {
		if ( $user_id == $post->post_author )
			$caps[] = $post_type->cap->edit_posts;
		else
			$caps[] = $post_type->cap->edit_others_posts;
	}

	elseif ( 'delete_project' == $cap ) {
		if ( $user_id == $post->post_author )
			$caps[] = $post_type->cap->delete_posts;
		else
			$caps[] = $post_type->cap->delete_others_posts;
	}

	elseif ( 'read_project' == $cap ) {

		if ( 'private' != $post->post_status )
			$caps[] = 'read';
		elseif ( $user_id == $post->post_author )
			$caps[] = 'read';
		else
			$caps[] = $post_type->cap->read_private_posts;
	}
	
	if ( 'edit_post2' == $cap || 'delete_post2' == $cap || 'read_post2' == $cap ) {
		$post = get_post( $args[0] );
		$post_type = get_post_type_object( $post->post_type );

		$caps = array();
	}

	if ( 'edit_post2' == $cap ) {
		if ( $user_id == $post->post_author )
			$caps[] = $post_type->cap->edit_posts;
		else
			$caps[] = $post_type->cap->edit_others_posts;
	}

	elseif ( 'delete_post2' == $cap ) {
		if ( $user_id == $post->post_author )
			$caps[] = $post_type->cap->delete_posts;
		else
			$caps[] = $post_type->cap->delete_others_posts;
	}

	elseif ( 'read_post2' == $cap ) {

		if ( 'private' != $post->post_status )
			$caps[] = 'read';
		elseif ( $user_id == $post->post_author )
			$caps[] = 'read';
		else
			$caps[] = $post_type->cap->read_private_posts;
	}
	
	if ( 'edit_partner' == $cap || 'delete_partner' == $cap || 'read_partner' == $cap ) {
		$post = get_post( $args[0] );
		$post_type = get_post_type_object( $post->post_type );

		$caps = array();
	}

	if ( 'edit_partner' == $cap ) {
		if ( $user_id == $post->post_author )
			$caps[] = $post_type->cap->edit_posts;
		else
			$caps[] = $post_type->cap->edit_others_posts;
	}

	elseif ( 'delete_partner' == $cap ) {
		if ( $user_id == $post->post_author )
			$caps[] = $post_type->cap->delete_posts;
		else
			$caps[] = $post_type->cap->delete_others_posts;
	}

	elseif ( 'read_partner' == $cap ) {

		if ( 'private' != $post->post_status )
			$caps[] = 'read';
		elseif ( $user_id == $post->post_author )
			$caps[] = 'read';
		else
			$caps[] = $post_type->cap->read_private_posts;
	}

	return $caps;
}

function add_oembed_slideshare(){
wp_oembed_add_provider( 'http://www.slideshare.net/*', 'http://api.embed.ly/v1/api/oembed');
}
add_action('init','add_oembed_slideshare');