<?php

if (!defined('ABSPATH')) exit;

class AsgarosForumTaxonomies {
	public function __construct() {
		register_taxonomy(
            'asgarosforum-category',
            null,
            array(
                'labels' => array(
                    'name'          => __('Categories', 'asgaros-forum'),
                    'singular_name' => __('Category', 'asgaros-forum'),
                    'edit_item'     => __('Edit Category', 'asgaros-forum'),
                    'update_item'   => __('Update Category', 'asgaros-forum'),
                    'add_new_item'  => __('Add new Category', 'asgaros-forum'),
                    'search_items'  => __('Search Categories', 'asgaros-forum'),
                    'not_found'     => __('No Categories found.', 'asgaros-forum')
                ),
                'public' => false,
                'show_ui' => true,
                'rewrite' => false,
                'capabilities' => array(
                    'manage_terms' => 'edit_users',
					'edit_terms'   => 'edit_users',
					'delete_terms' => 'edit_users',
					'assign_terms' => 'edit_users'
				)
            )
        );
	}
}
