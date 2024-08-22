<?php

if (!defined('ABSPATH')) {
    exit;
}

class AsgarosForumMap {
    private $asgarosforum = null;

    public function __construct($asgarosForumObject) {
        $this->asgarosforum = $asgarosForumObject;

        add_action('asgarosforum_bottom_navigation', array($this, 'show_map_navigation'), 20, 1);
//        add_action('asgarosforum_prepare_topic', array($this, 'render_map'));
        add_action('asgarosforum_prepare_forum', array($this, 'render_map'));
    }



    public function show_map_navigation($current_view) {

            switch ($current_view) {
                case 'forum':
					$slug = $this->asgarosforum->get_link('forum', $this->asgarosforum->current_forum);
                    if(substr($slug, -1)=='/'){
                    $link = $this->asgarosforum->get_link('forum', $this->asgarosforum->current_forum).'sitemap.xml';
                    }else {
                    $link = $this->asgarosforum->get_link('forum', $this->asgarosforum->current_forum).'/sitemap.xml';
                    }
                    echo '<!--noindex--><span class="fas fa-sitemap"></span>';
                    echo '<a href="'.esc_url($link).'" target="_blank" rel="nofollow">'.esc_html__('Xml Sitemap', 'asgaros-forum').'</a><!--/noindex-->';
                    break;

        }
    }

    public function render_map() {
		$currentUrl= ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$slug = $this->asgarosforum->get_link('forum', $this->asgarosforum->current_forum);
        if(substr($slug, -1)=='/'){
        $link = $this->asgarosforum->get_link('forum', $this->asgarosforum->current_forum).'sitemap.xml';
        }else {
        $link = $this->asgarosforum->get_link('forum', $this->asgarosforum->current_forum).'/sitemap.xml';
        }
        if ($currentUrl == $link) {
            // Abort feed creation when an error occured.
            if ($this->asgarosforum->error !== false) {
                return;
            }

            header('Content-type: application/xml');

            echo '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
            echo '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
//            echo '<url>'.PHP_EOL;

            if ($this->asgarosforum->current_view === 'forum') {
				echo '<url>'.PHP_EOL;
                echo '<loc>'.esc_url($this->asgarosforum->rewrite->get_link('forum', absint($this->asgarosforum->current_forum))).'</loc>'.PHP_EOL;
            } else if ($this->asgarosforum->current_view === 'topic') {
                echo '<loc>'.esc_url($this->asgarosforum->rewrite->get_link('topic', absint($this->asgarosforum->current_topic))).'</loc>'.PHP_EOL;
            }

            echo '<lastmod>'.esc_html(mysql2date('Y-m-d', gmdate('Y-m-d'), false)).'</lastmod>'.PHP_EOL;
            echo '</url>'.PHP_EOL;
            $feed_data = false;

            if ($this->asgarosforum->current_view === 'forum') {
                $query_post_content = "SELECT p.text FROM {$this->asgarosforum->tables->posts} AS p WHERE p.parent_id = t.id ORDER BY p.id ASC LIMIT 1";
                $query_post_date    = "SELECT p.date FROM {$this->asgarosforum->tables->posts} AS p WHERE p.parent_id = t.id ORDER BY p.id ASC LIMIT 1";

                $feed_data = $this->asgarosforum->db->get_results("SELECT t.id, t.name, ({$query_post_content}) AS text, ({$query_post_date}) AS date, t.author_id FROM {$this->asgarosforum->tables->topics} AS t WHERE t.parent_id = {$this->asgarosforum->current_forum} AND t.approved = 1 ORDER BY t.id DESC LIMIT 0, 500;");
            } else if ($this->asgarosforum->current_view === 'topic') {
                $feed_data = $this->asgarosforum->db->get_results("SELECT p.id, p.parent_id, t.name, p.date, p.text, p.author_id FROM {$this->asgarosforum->tables->posts} AS p, {$this->asgarosforum->tables->topics} AS t WHERE p.parent_id = {$this->asgarosforum->current_topic} AND t.id = p.parent_id ORDER BY p.id DESC LIMIT 0, 500;");
            }

            if (!empty($feed_data)) {
                foreach ($feed_data as $element) {
                    echo '<url>'.PHP_EOL;

                        if ($this->asgarosforum->current_view === 'forum') {
                            echo '<loc>'.esc_url($this->asgarosforum->rewrite->get_link('topic', absint($element->id))).'</loc>'.PHP_EOL;
                        } else if ($this->asgarosforum->current_view === 'topic') {
                            echo '<loc>'.esc_url($this->asgarosforum->rewrite->get_post_link($element->id, absint($element->parent_id))).'</loc>'.PHP_EOL;
                        }

                        echo '<lastmod>'.esc_html(mysql2date('Y-m-d', $element->date, false)).'</lastmod>'.PHP_EOL;

                        
                    echo '</url>'.PHP_EOL;
                }
            }

            echo '</urlset>'.PHP_EOL;

            exit;
        }
    }
}
