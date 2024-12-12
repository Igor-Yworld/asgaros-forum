<?php

if (!defined('ABSPATH')) {
    exit;
}

class AsgarosForumMicrodata {
    private $asgarosforum = null;

    public function __construct($asgarosForumObject) {
        $this->asgarosforum = $asgarosForumObject;

        add_action('asgarosforum_wp_head', array($this, 'render_microdata'));

    }

    public function is_microdata_enabled() {		
		$is_microdata_enabled = false;

		// Return false, if the functionality is disabled.
		if ($this->asgarosforum->options['enable_microdata'] === true) {
			$is_microdata_enabled = true;
		}
		
		$is_microdata_enabled = apply_filters('asgarosforum_overwrite_is_feed_enabled', $is_microdata_enabled);

		return $is_microdata_enabled;
	}

    public function render_microdata() {
    if ($this->is_microdata_enabled()) {
            // Abort microdata creation when an error occured.
            if ($this->asgarosforum->error !== false) {
                return;
            }

            $microdata_data1 = false;
			
			if ($this->asgarosforum->current_view === 'topic') {
                $microdata_data1 = $this->asgarosforum->db->get_results("SELECT p.id, p.parent_id, t.name, p.date, p.text, p.date_edit, p.author_edit, p.author_id FROM {$this->asgarosforum->tables->posts} AS p, {$this->asgarosforum->tables->topics} AS t WHERE p.parent_id = {$this->asgarosforum->current_topic} AND t.id = p.parent_id ORDER BY p.id DESC LIMIT 0, 50;");
            }
			if ($this->asgarosforum->current_view === 'topic' && $this->asgarosforum->current_element) {
			$first_post = $this->asgarosforum->content->get_first_post($this->asgarosforum->current_element);
			$image_url = $this->asgarosforum->extract_image_url($first_post->text);
			}
			if (!empty($microdata_data1)) {
				$post_comment = array();
				$datemodified = '';
                foreach ($microdata_data1 as $element) {
					if ($element->author_edit) {
					$datemodified = esc_html(mysql2date('D, d M Y H:i:s +03:00', $element->date_edit, false));
					}
			 $page_content = preg_replace('/<(pre)(?:(?!<\/\1).)*?<\/\1>/s','',$element->text);
			 $page_content = preg_replace('/\s+/', ' ', $page_content);
			 //			 $page_content = preg_replace( '/"([^"]*)"/', "«$1»", $page_content );
			 $page_content = wp_kses_stripslashes( $page_content );
			 $page_content = str_replace("\'","\"",$page_content);
			 $post = '"mainEntityOfPage": "'.esc_url($this->asgarosforum->rewrite->get_link('topic', absint($this->asgarosforum->current_topic))).'",
            "headline": "'.esc_html(stripslashes($this->asgarosforum->current_topic_name)).'",
            "text": "'.esc_html(wp_strip_all_tags($page_content)).'",
            "url": "'.esc_url($this->asgarosforum->rewrite->get_link('topic', absint($this->asgarosforum->current_topic))).'",
			"image": "'.esc_url($image_url).'",
            "author": {
            "@type": "Person",
            "name": "'.esc_html($this->asgarosforum->get_plain_username($element->author_id)).'",
            "url": "'.get_author_posts_url( 5, wp_get_current_user()->user_login ).'"
            },
			"datePublished": "'.esc_html(mysql2date('D, d M Y H:i:s +03:00', $element->date, false)).'",
			"dateModified": "'.$datemodified.'"';

              }
            }
			
           $microdata_data = false;
		   
            if ($this->asgarosforum->current_view === 'topic') {
                $microdata_data = $this->asgarosforum->db->get_results("SELECT p.id, p.parent_id, t.name, p.date, p.text, p.date_edit, p.author_edit, p.author_id FROM {$this->asgarosforum->tables->posts} AS p, {$this->asgarosforum->tables->topics} AS t WHERE p.parent_id = {$this->asgarosforum->current_topic} AND t.id = p.parent_id ORDER BY p.id ASC LIMIT 0, 50;");
            }

            if (!empty($microdata_data)) {
				$post_comment = array();
				$post_comment_end = '';
				$datemodified = '';
                foreach ($microdata_data as $element) {
					if ($element->author_edit ) {
					$datemodified = esc_html(mysql2date('D, d M Y H:i:s +03:00', $element->date_edit, false));
					}
			 $page_content = preg_replace('/<(pre)(?:(?!<\/\1).)*?<\/\1>/s','',$element->text);
			 $page_content = preg_replace('/\s+/', ' ', $page_content);
//			 $page_content = preg_replace( '/"([^"]*)"/', "«$1»", $page_content );
			 $page_content = wp_kses_stripslashes( $page_content );
			 $page_content = str_replace("\'","\"",$page_content);
			 $post_comment[]= '	
		     ,"comment": [{
			"@type": "Comment",
            "text": "'.esc_html(wp_strip_all_tags($page_content)).'",
            "author": {
            "@type": "Person",
            "name": "'.esc_html($this->asgarosforum->get_plain_username($element->author_id)).'",
            "url": "'.get_author_posts_url( 5, wp_get_current_user()->user_login ).'"
            },
            "datePublished": "'.esc_html(mysql2date('D, d M Y H:i:s +03:00', $element->date, false)).'",
			"dateModified": "'.$datemodified.'"';

              }
            }
			if (!empty($microdata_data)) {
				$post_comment_end = array();
                foreach ($microdata_data as $element) {
			    $post_comment_end[]= ' }]';

              }
            }
			
			/**
 * Function to strip away a given string
 **/
 switch ($this->asgarosforum->current_view) {
       case 'topic':
function remove_nbsp($string){
    $string_to_remove = "&nbsp;";
    return str_replace($string_to_remove, "", $string);
}

        //Strip_tags() will remove the HTML tags
        $post_comment = array_map("remove_nbsp", $post_comment);
        //Our custom function will remove the &nbsp; character
        $post_comment = array_filter($post_comment);
        //Array_filter() will remove any blank array values
	   
        $post_comment = array_slice($post_comment, 1);
//		$post_comment = array_filter(array_map('trim', $post_comment));
		$post_comment_end = array_slice($post_comment_end, 1);

//        foreach($post_comment as $dirty_step){
//        if(!$clean_step = trim(str_replace("&nbsp;", "", strip_tags($dirty_step)))){
//        //Ignore empty steps
//        continue;
//    }
//       $clean_steps[] = $clean_step;
//}

       			
	   $out = '<script type="application/ld+json">
      {
      "@context": "https://schema.org",
      "@type": "DiscussionForumPosting",
      '.$post.'
      '.implode( '', $post_comment ).'
	  '.implode( ' ', $post_comment_end ).'
   }
      </script>';		
      echo $out.PHP_EOL;
	  break;
	
           }

       }
	
	}

}