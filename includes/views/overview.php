<?php

if (!defined('ABSPATH')) exit;

echo '<h1 class="main-title">'.__('Forum', 'asgaros-forum').'</h1>';

if ($categories) {
    $forumsAvailable = false;

    foreach ($categories as $category) {
        echo '<div class="title-element" id="forum-category-'.$category->term_id.'">';
            echo $category->name;
            echo '<span class="last-post-headline">'.__('Last post:', 'asgaros-forum').'</span>';
        echo '</div>';
        echo '<div class="content-element">';
            $forums = $this->get_forums($category->term_id);
            if (empty($forums)) {
                echo '<div class="notice">'.__('In this category are no forums yet!', 'asgaros-forum').'</div>';
            } else {
                $elementMarker = '';
                $forumsCounter = 0;
                foreach ($forums as $forum) {
                    $forumsAvailable = true;
                    $forumsCounter++;
                    $elementMarker = ($forumsCounter & 1) ? 'odd' : 'even';
                    require('forum-element.php');
                }
            }
        echo '</div>';
    }

    if ($forumsAvailable) {
        AsgarosForumUnread::showUnreadControls();
    }
} else {
    echo '<div class="notice">'.__('There are no categories yet!', 'asgaros-forum').'</div>';
}
