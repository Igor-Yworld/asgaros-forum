<?php

if (!defined('ABSPATH')) {
    exit;
}

class AsgarosForumAppearance {
	private $asgarosforum   = null;
	public $options         = array();
	public $options_default = array(
		'custom_color'                  => '#256db3',
		'custom_accent_color'           => '#054d98',
        'custom_text_color'             => '#444444',
		'custom_text_color_light'       => '#888888',
		'custom_link_color'             => '#256db3',
        'custom_background_color'       => '#ffffff',
		'custom_background_color_alt'   => '#fafafa',
        'custom_border_color'           => '#eeeeee',
		'custom_read_indicator_color'   => '#a2a2a2',
		'custom_unread_indicator_color' => '#256db3',
		'custom_font'                   => 'Verdana, Tahoma, sans-serif',
		'custom_font_size'              => '13px',
		'custom_css'                    => '',
	);

	public function __construct($asgarosForumObject) {
		$this->asgarosforum = $asgarosForumObject;

		add_action('init', array($this, 'initialize'));
	}

	public function initialize() {
		$this->load_options();

		add_filter('mce_css', array($this, 'add_editor_css'));
		add_action('wp_enqueue_scripts', array($this, 'add_css'), 20);
		add_action('wp_head', array($this, 'set_header'), 1);
	}

	public function load_options() {
		// Load options.
		$this->options = array_merge($this->options_default, get_option('asgarosforum_appearance', array()));
	}

	public function save_options($options) {
		update_option('asgarosforum_appearance', $options);

		// Reload options after saving them.
		$this->load_options();
	}

	public function set_header() {
		// SEO stuff.
		if ($this->asgarosforum->executePlugin) {
			echo '<!-- Asgaros Forum - SEO: BEGIN -->'.PHP_EOL;

			$link  = ($this->asgarosforum->current_page > 0) ? $this->asgarosforum->get_link('current') : esc_url(remove_query_arg('part', $this->asgarosforum->get_link('current', false, false, '', false)));
			$part_description = ($this->asgarosforum->getMetaTitle()) ? $this->asgarosforum->getMetaTitle() : get_the_title();
			// Проверяем, содержит ли строка символ "|"
            if (strpos($part_description, '|') !== false) {
           // Регулярное выражение для удаления текста до |
           $result = preg_replace('/^.*?\|/', '', $part_description);
    
           // Удаляем пробелы в начале строки, если они остались
           $result = '|'.ltrim($result);
    
          // Вывод результата
          $result; // Вывод: "и оставить только это часть текста"
          } else {
         // Если символа "|" нет, ничего не выводим
          $result = '';
          }
			$title = ($this->asgarosforum->getMetaTitle()) ? $this->asgarosforum->getMetaTitle() : get_the_title();

			// By default use the page title as description.
			$description = $title;

			// In forum overview use the forum description if available.
			$forum_description = stripslashes($this->asgarosforum->options['forum_description']);

			if ($this->asgarosforum->current_view === 'overview' && !empty($forum_description)) {
				$description = $forum_description;
			} else if ($this->asgarosforum->current_description && $this->asgarosforum->error === false) {
				$description = $this->asgarosforum->current_description;
			}

			// Prevent indexing of some views, when there is an error or for other configurations.
			$prevent_indexing                = false;
			$blocked_views_for_searchengines = array('addtopic', 'movetopic', 'addpost', 'editpost', 'search');

			if (in_array($this->asgarosforum->current_view, $blocked_views_for_searchengines, true)) {
				$prevent_indexing = true;
			}

			if ($this->asgarosforum->error !== false) {
				$prevent_indexing = true;
			}

			$profile_views = array('profile', 'history');

			if ($this->asgarosforum->options['hide_profiles_from_guests'] && in_array($this->asgarosforum->current_view, $profile_views, true)) {
				$prevent_indexing = true;
			}

			if ($prevent_indexing) {
				header("HTTP/1.0 404 Not Found");
				echo '<meta name="robots" content="noindex, follow" />'.PHP_EOL;
			}

			// Create meta-tags.
			echo '<link rel="canonical" href="'.esc_url($link).'" />'.PHP_EOL;			
			echo '<meta name="description" content="'.stripslashes(esc_attr($description)).''.$result.'" />'.PHP_EOL;
			echo '<meta name="robots" content="follow, index, max-snippet:-1, max-video-preview:-1, max-image-preview:large"/>'.PHP_EOL;
			echo '<meta property="og:type" content="website" />'.PHP_EOL;
			echo '<meta property="og:url" content="'.esc_url($link).'" />'.PHP_EOL;
			echo '<meta property="og:title" content="'.esc_attr($title).'" />'.PHP_EOL;
			echo '<meta property="og:description" content="'.stripslashes(esc_attr($description)).'" />'.PHP_EOL;
			echo '<meta property="og:site_name" content="'.esc_attr(get_bloginfo('name')).'" />'.PHP_EOL;

            // Try to set og:image-tag when we are in a topic. A check for the element-ID
            // is required to prevent an error in case that this topic does not exist.
			if ($this->asgarosforum->current_view === 'topic' && $this->asgarosforum->current_element) {
				$first_post = $this->asgarosforum->content->get_first_post($this->asgarosforum->current_element);

				if ($first_post) {
					$image_url = $this->asgarosforum->extract_image_url($first_post->text);
                    if ($image_url != '') {
					$imagedetails = getimagesize($image_url);
                    $width = $imagedetails[0];
                    $height = $imagedetails[1];
					}

					if ($image_url) {
						echo '<link rel="preload" href="'.esc_url($image_url).'" as="image">'.PHP_EOL;
						echo '<meta property="og:image" content="'.esc_url($image_url).'" />'.PHP_EOL;
						echo '<meta property="og:image:secure_url" content="'.esc_url($image_url).'" />'.PHP_EOL;
						if ($image_url != '') {
						echo '<meta property="og:image:width" content="'.$width.'" />'.PHP_EOL;
                        echo '<meta property="og:image:height" content="'.$height.'" />'.PHP_EOL;
						}
					}
				}
			}
			
            echo '<meta name="twitter:title" content="'.esc_attr($title).'" />'.PHP_EOL;
			echo '<meta name="twitter:description" content="'.stripslashes(esc_attr($description)).'" />'.PHP_EOL;
			echo '<meta name="twitter:site" content="'.esc_attr(stripslashes($this->asgarosforum->options['twitter_creator'])).'" />'.PHP_EOL;
            echo '<meta name="twitter:creator" content="'.esc_attr(stripslashes($this->asgarosforum->options['twitter_creator'])).'" />'.PHP_EOL;
			$image_url ??= '';
			if ($image_url != '') {
			echo '<meta name="twitter:card" content="summary_large_image" />'.PHP_EOL;
            echo '<meta name="twitter:image" content="'.esc_url($image_url).'" />'.PHP_EOL;
			}

			do_action('asgarosforum_wp_head');

			echo '<!-- Asgaros Forum - SEO: END -->'.PHP_EOL;
		}
	}
	public function add_css() {
		if ($this->asgarosforum->executePlugin) {
			// Set path to custom CSS file.
			$custom_css_path = $this->asgarosforum->plugin_path.'skin/custom.css';

			// Only run custom CSS logic when the default appearance settings have been changed.
			if ($this->options != $this->options_default) {
				// Load the custom CSS definitions with the adjusted values first.
				$custom_css = $this->generate_custom_css();

				// Only continue when our custom CSS is not empty.
				if (!empty($custom_css)) {
					// Flag to decide if we need to load the custom CSS inline.
					$inline = false;

					// Generate hash of the custom CSS.
					$hash_new = md5($custom_css);

					// Check if a custom CSS file exists.
					if (file_exists($custom_css_path) && filesize($custom_css_path)) {
						// Try to read the current custom CSS file.
						$current_css = $this->asgarosforum->read_file($custom_css_path);

						// If we were not able to read the file, we have to load the CSS inline.
						if (!$current_css) {
							$inline = true;
						} else {
							// Generate hash of the current CSS.
							$hash_current = md5($current_css);

							// If the hash values are different, we have to update our custom CSS file.
							if ($hash_new != $hash_current) {
								$file_check = $this->asgarosforum->create_file($custom_css_path, $custom_css);

								// If we were not able to update the file, we have to load the CSS inline.
								if (!$file_check) {
									$inline = true;
								}
							}
						}
					} else {
						// The file does not exists so try to create it.
						$file_check = $this->asgarosforum->create_file($custom_css_path, $custom_css);

						// If we were not able to create the file, we have to load the CSS inline.
						if (!$file_check) {
							$inline = true;
						}
					}

					// Load CSS (inline or as a file).
					if ($inline) {
						// Remove special characters from inline CSS.
						$custom_css = preg_replace('|[\r\n\t]+|', '', $custom_css);

						// Load CSS inline.
						wp_add_inline_style('af-style', $custom_css);
					} else {
						// Load CSS as file.
						wp_enqueue_style('af-custom-color', $this->asgarosforum->plugin_url.'skin/custom.css', array(), $this->asgarosforum->version);
					}
				}
			} else {
				// Otherwise try to delete the custom CSS file.
				$this->asgarosforum->delete_file($custom_css_path);
			}
		}
	}

	// Add a custom stylesheet to the TinyMCE editor.
	public function add_editor_css($mce_css) {
		if (!empty($mce_css)) {
			$mce_css .= ',';
		}

		$mce_css .= $this->asgarosforum->plugin_url.'skin/editor.css?ver='.$this->asgarosforum->version;

		return $mce_css;
	}

	// Generates the custom CSS based on the appearance settings.
	public function generate_custom_css() {
		$custom_css = '';

		if ($this->options['custom_color'] != $this->options_default['custom_color'] && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $this->options['custom_color'])) {
			$custom_css     .= '#af-wrapper #forum-profile .display-name,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .post-author .topic-author,'.PHP_EOL;
			$custom_css     .= '#af-wrapper input[type="checkbox"]:checked:before {'.PHP_EOL;
				$custom_css .= 'color: '.$this->options['custom_color'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;

			$custom_css     .= '#af-wrapper .button-normal,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .title-element,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #forum-header,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #profile-header .background-avatar,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #profile-navigation,'.PHP_EOL;
			$custom_css     .= '#af-wrapper input[type="radio"]:checked:before {'.PHP_EOL;
				$custom_css .= 'background-color: '.$this->options['custom_color'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;

			$custom_css     .= '#af-wrapper .button-neutral {'.PHP_EOL;
				$custom_css .= 'background-color: '.$this->options['custom_color'].'B0 !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;

			$custom_css     .= '#af-wrapper .post-author .topic-author {'.PHP_EOL;
				$custom_css .= 'background-color: '.$this->options['custom_color'].'40 !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;

			$custom_css     .= '#af-wrapper input[type="radio"]:focus,'.PHP_EOL;
			$custom_css     .= '#af-wrapper input[type="checkbox"]:focus,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #profile-header {'.PHP_EOL;
				$custom_css .= 'border-color: '.$this->options['custom_color'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;
		}

		if ($this->options['custom_accent_color'] != $this->options_default['custom_accent_color'] && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $this->options['custom_accent_color'])) {
			$custom_css     .= '#af-wrapper .title-element,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #forum-navigation a,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #forum-navigation-mobile a {'.PHP_EOL;
				$custom_css .= 'border-color: '.$this->options['custom_accent_color'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;

			$custom_css     .= '#af-wrapper .button-normal:hover,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #profile-navigation a.active {'.PHP_EOL;
				$custom_css .= 'background-color: '.$this->options['custom_accent_color'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;

			$custom_css     .= '#af-wrapper .button-neutral:hover {'.PHP_EOL;
				$custom_css .= 'background-color: '.$this->options['custom_accent_color'].'B0 !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;
		}

		if ($this->options['custom_text_color'] != $this->options_default['custom_text_color'] && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $this->options['custom_text_color'])) {
			$custom_css     .= '#af-wrapper,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #forum-breadcrumbs a:hover,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .main-title {'.PHP_EOL;
			    $custom_css .= 'color: '.$this->options['custom_text_color'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;
		}

		if ($this->options['custom_text_color_light'] != $this->options_default['custom_text_color_light'] && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $this->options['custom_text_color_light'])) {
			$custom_css     .= '#af-wrapper .main-title:before,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .editor-row-uploads .upload-hints,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .forum-stats,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .topic-stats,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .action-panel-description,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #forum-breadcrumbs,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #forum-breadcrumbs a,'.PHP_EOL;
            $custom_css     .= '#af-wrapper .forum-post-date,'.PHP_EOL;
            $custom_css     .= '#af-wrapper .forum-post-date a,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .post-footer,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .post-footer a,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .signature,'.PHP_EOL;
			$custom_css     .= '#af-wrapper span.mention-nice-name,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .post-reactions .reaction,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #poll-results .poll-result-numbers,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #poll-results .poll-result-total,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #poll-warning,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .topic-icon:before,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .report-link,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .report-content:before,'.PHP_EOL;
			$custom_css     .= '#af-wrapper input::placeholder,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .activity-time,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .unread-time {'.PHP_EOL;
			    $custom_css .= 'color: '.$this->options['custom_text_color_light'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;
		}

		if ($this->options['custom_link_color'] != $this->options_default['custom_link_color'] && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $this->options['custom_link_color'])) {
			$custom_css     .= '#af-wrapper a:not(a.button):not(a.highlight-admin):not(a.highlight-moderator),'.PHP_EOL;
			$custom_css     .= '#af-wrapper .forum-post-menu a,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #bottom-navigation {'.PHP_EOL;
			    $custom_css .= 'color: '.$this->options['custom_link_color'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;
		}

		if ($this->options['custom_background_color'] != $this->options_default['custom_background_color'] && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $this->options['custom_background_color'])) {
			$custom_css     .= '#af-wrapper .content-container,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .report-element,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #statistics,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .post-wrapper,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #profile-header .background-contrast,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #poll-results .poll-result-bar,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #memberslist-filter {'.PHP_EOL;
			    $custom_css .= 'background-color: '.$this->options['custom_background_color'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;
		}

		if ($this->options['custom_background_color_alt'] != $this->options_default['custom_background_color_alt'] && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $this->options['custom_background_color_alt'])) {
			$custom_css     .= '#af-wrapper .content-element:nth-child(even),'.PHP_EOL;
			$custom_css     .= '#af-wrapper .topic-sticky,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .topic-sticky .topic-poster,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .forum-post-header-container,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .editor-element,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #statistics-online-users,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #profile-layer,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .spoiler .spoiler-head,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .report-content,'.PHP_EOL;
            $custom_css     .= '#af-wrapper #poll-panel,'.PHP_EOL;
            $custom_css     .= '#af-wrapper .post-reactions-summary .reaction-names,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #profile-content {'.PHP_EOL;
			    $custom_css .= 'background-color: '.$this->options['custom_background_color_alt'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;
		}

		if ($this->options['custom_border_color'] != $this->options_default['custom_border_color'] && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $this->options['custom_border_color'])) {
			$custom_css     .= '#af-wrapper input,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .forum-post-header-container,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .forum-poster,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .topic-poster,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .member-last-seen,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .editor-element,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .content-container,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .forum-post-header,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #statistics-body,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .statistics-element,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #statistics-online-users,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .editor-row,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .editor-row-subject,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .signature,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .forum-subforums,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .uploaded-file img,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .action-panel-option,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .topic-sticky .topic-poster,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #profile-layer,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #profile-layer .pages-and-menu:first-of-type,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #profile-content,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #profile-content .profile-row,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .history-element,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #memberslist-filter,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .content-element,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .ad-forum,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .ad-topic,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .spoiler,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .spoiler .spoiler-body,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .report-element,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .report-source,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .report-content,'.PHP_EOL;
			$custom_css     .= '#af-wrapper .report-actions,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #profile-content .profile-section-header,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #poll-options,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #poll-panel,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #poll-panel #poll-headline,'.PHP_EOL;
            $custom_css     .= '#af-wrapper #poll-results .poll-result-bar,'.PHP_EOL;
            $custom_css     .= '#af-wrapper .post-reactions-summary .reaction-names,'.PHP_EOL;
			$custom_css     .= '#af-wrapper #usergroups-filter {'.PHP_EOL;
			    $custom_css .= 'border-color: '.$this->options['custom_border_color'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;

			$custom_css     .= '#af-wrapper .post-element {'.PHP_EOL;
				$custom_css .= 'box-shadow: #fff 0px 0px 0px 0px, '.$this->options['custom_border_color'].' 0px 0px 0px 1px, #0000 0px 0px 0px 0px !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;
		}

		if ($this->options['custom_read_indicator_color'] != $this->options_default['custom_read_indicator_color'] && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $this->options['custom_read_indicator_color'])) {
			$custom_css     .= '#af-wrapper .read {'.PHP_EOL;
				$custom_css .= 'color: '.$this->options['custom_read_indicator_color'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;

			$custom_css     .= '#af-wrapper #read-unread .read {'.PHP_EOL;
				$custom_css .= 'background-color: '.$this->options['custom_read_indicator_color'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;
		}

		if ($this->options['custom_unread_indicator_color'] != $this->options_default['custom_unread_indicator_color'] && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $this->options['custom_unread_indicator_color'])) {
			$custom_css     .= '#af-wrapper .unread {'.PHP_EOL;
				$custom_css .= 'color: '.$this->options['custom_unread_indicator_color'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;

			$custom_css     .= '#af-wrapper #read-unread .unread {'.PHP_EOL;
				$custom_css .= 'background-color: '.$this->options['custom_unread_indicator_color'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;
		}

		if ($this->options['custom_font'] != $this->options_default['custom_font']) {
			$custom_css     .= '#af-wrapper {'.PHP_EOL;
			    $custom_css .= 'font-family: '.$this->options['custom_font'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;
		}

		if ($this->options['custom_font_size'] != $this->options_default['custom_font_size']) {
			$custom_css     .= '#af-wrapper {'.PHP_EOL;
			    $custom_css .= 'font-size: '.$this->options['custom_font_size'].' !important;'.PHP_EOL;
			$custom_css     .= '}'.PHP_EOL;
		}

		if ($this->options['custom_css'] != $this->options_default['custom_css']) {
			$custom_css .= $this->options['custom_css'];
		}

		return $custom_css;
	}
}
