<?php

if ($user_ID || $this->allow_unreg())
{
  if (isset($_GET['quote']))
  {
    $quote_id = $this->check_parms($_GET['quote']);
    $text = $wpdb->get_row($wpdb->prepare("SELECT text, author_id, `date` FROM {$this->t_posts} WHERE id = %d", $quote_id));
    $user = get_userdata($text->author_id);
    $display_type = $this->options['forum_display_name'];
    $display_name = (!empty($user)) ? $user->$display_type : __('Guest', 'asgarosforum');
    $q = "[quote][quotetitle]" . __("Quote from", "asgarosforum") . " " . $display_name . " " . __("on", "asgarosforum") . " " . $this->format_date($text->date) . "[/quotetitle]\n" . $text->text . "[/quote]";
  }

  if (($_GET['forumaction'] == "postreply"))
  {
    $this->current_view = POSTREPLY;
    $thread = $this->check_parms($_GET['thread']);
    $out = "<form action='' name='addform' method='post' enctype='multipart/form-data'>";
    $out .= "<table class='wpf-table' width='100%'>
      <tr>
        <th colspan='2'>" . __("Post Reply:", "asgarosforum") . ' ' . $this->get_subject($thread) . "</th>
      </tr>";

    $out .= "<tr>
            <td valign='top'>" . __("Message:", "asgarosforum") . "</td>
            <td>";
              $out .= $this->form_buttons();
              $out .= "<br /><textarea rows='20' cols='80' name='message'></textarea>";
              $out .= '<input type="hidden" name="add_post_subject" value="'.$this->get_subject($thread).'" />';
              $out .= "
            </td>
          </tr>";
    $out .= apply_filters('wpwf_form_guestinfo', ''); //--weaver--
    $out .= $this->get_captcha();

    if ($this->options['forum_allow_image_uploads'])
    {
      $out .= "
          <tr>
            <td valign='top'>" . __("Images:", "asgarosforum") . "</td>
            <td colspan='2'>
              <input type='file' name='mfimage1' id='mfimage' /><br/>
              <input type='file' name='mfimage2' id='mfimage' /><br/>
              <input type='file' name='mfimage3' id='mfimage' /><br/>
            </td>
          </tr>";
    }
    $out .= "
      <tr>
        <td colspan='2'><input type='submit' id='wpf-post-submit' name='add_post_submit' value='" . __("Submit", "asgarosforum") . "' /></td>
        <input type='hidden' name='add_post_forumid' value='" . $this->check_parms($thread) . "'/>
      </tr>
      </table></form>";
    $this->o .= $out;
  }

  if (($_GET['forumaction'] == "editpost"))
  {
    $this->current_view = EDITPOST;
    $id = (isset($_GET['id']) && !empty($_GET['id'])) ? (int)$_GET['id'] : 0;
    $thread = $this->check_parms($_GET['t']);
    $t = $wpdb->get_row($wpdb->prepare("SELECT subject FROM {$this->t_threads} WHERE id = %d", $thread));
    $out = "";
    $post = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->t_posts} WHERE id = %d", $id));

    if (($user_ID == $post->author_id && $user_ID) || $this->is_moderator($user_ID)) //Make sure only admins/mods/post authors can edit posts
    {
      $out .= "<form action='' name='addform' method='post'>";
      $out .= "<table class='wpf-table' width='100%'>
        <tr>
          <th colspan='2'>" . __("Edit Post:", "asgarosforum") . " " . stripslashes($t->subject) . "</th>
        </tr>";

      if(false) //Need to enable this eventually if we're editing the first post in the thread
        $out .= "<tr>
              <td>" . __("Subject:", "asgarosforum") . "</td>
              <td><input size='50%' type='text' name='edit_post_subject' class='wpf-input' value='" . stripslashes($t->subject) . "'/></td>
            </tr>";

      $out .= "<tr>
              <td valign='top'>" . __("Message:", "asgarosforum") . "</td>
              <td>";
                $out .= $this->form_buttons();
                $out .= "<br /><textarea rows='20' cols='80' name='message'>" . stripslashes($post->text) . "</textarea>";
                $out .= "</td>
            </tr>
            <tr>
              <td colspan='2'><input type='submit' id='wpf-post-submit' name='edit_post_submit' value='" . __("Save Post", "asgarosforum") . "' /></td>
              <input type='hidden' name='edit_post_id' value='" . $post->id . "'/>
              <input type='hidden' name='thread_id' value='" . $thread . "'/>
              <input type='hidden' name='page_id' value='" . $this->curr_page . "'/>
            </tr>
          </table></form>";
      $this->o .= $out;
    }
    else
      wp_die("Hey, that's not vary nice ... didn't your mother raise you better?");
  }
}
else
  wp_die("You do not have permission.");
?>
