<?PHP  // $Id: view.php,v 1.23 2008/05/16 19:05:40 bcarp Exp $


/// This page prints a particular instance of mail
/// (Replace mail with the name of your module)

    require_once("../../config.php");
   // require_once("mimetypes.php");
    require_once("lib.php");
    require_once("locallib.php");

    $id = optional_param('id',0,PARAM_INT);    // Course Module ID, or
    $a = optional_param('a',0,PARAM_INT);     // mail ID

    if ($id) {
      $cm         = get_coursemodule_from_id('mail', $id, 0, false, MUST_EXIST);
      $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
      $mail  = $DB->get_record('mail', array('id' => $cm->instance), '*', MUST_EXIST);
    } elseif ($a) {
         $mail  = $DB->get_record('mail', array('id' => $a), '*', MUST_EXIST);
         $course     = $DB->get_record('course', array('id' => $mail->course), '*', MUST_EXIST);
         $cm         = get_coursemodule_from_instance('mail', $mail->id, $course->id, false, MUST_EXIST);
    } else {
        error('You must specify a course_module ID or an instance ID');
    }

require_login($course, true, $cm);

//add_to_log($course->id, 'mail', 'view', "view.php?id=$cm->id", $newmodule->name, $cm->id);
    
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

/// Print the page header
/*    if(isadmin())
      { 
        $auditbutton = "&nbsp;<input type=\"button\" value=\"Audit Messages\" onClick=javascript:window.location=\"audit.php?id=$cm->id\">";
      }
    else {
      $auditbutton = "";
      }
*/
    $navigation = build_navigation('',$cm);
    print_header_simple(format_string($mail->name),"",$navigation,"","",true,"",navmenu($course,$cm));    

    if ($course->category) {
        $navigation = "<A HREF=\"../../course/view.php?id=$course->id\">$course->shortname</A> ->";
    }
/*
    $strmails = get_string("modulenameplural", "mail");
    $strmail  = get_string("modulename", "mail");
*/

/// Print the main part of the page



$id=optional_param("id",null,PARAM_INT);
$op=optional_param("op", 'inbox',PARAM_TEXT);
//$attachment=optional_param('attachment');
$attachment = isset($_FILES['attachment']) ? $_FILES['attachment'] : NULL;
$coursenumber = $course->id;

//add_to_log($course->id, "user", "view", "view.php?id=$user->id&course=$course->id", "$user->id");

require_login(0, false);

//if (! $course = $DB->get_record("course", "id", $course) ) {
//    error("No such course id");
//}
//$recip = optional_param("recipients",0,PARAM_INT);
if(empty($_REQUEST['recipients'])) {
    $recip = null;
}
else {

	$recip = $_REQUEST['recipients'];
}
    
if(is_array($recip)) {
    //$recipients = $DB->get_records_list('user', 'id', implode(',', $recip));
    $recipients = $DB->get_records_list('user', 'id',$recip);
}
else {

    $rec = $DB->get_record('user', array('id'=>$recip));
    $recipients = array();
    if(!empty($rec)) {

	$recipients[] = $rec;
    }
}

$fullname = fullname($USER);
$participants = get_string("participants");
$privatemessages = get_string('privatemessages', 'mail');

if ($usehtmleditor = can_use_html_editor()) {
    $defaultformat = FORMAT_HTML;
} else {
    $defaultformat = FORMAT_MOODLE;
}

//$message->attachment = isset($_FILES['attachment']) ? $_FILES['attachment'] : NULL;
//echo "priv  $privmsgs->attachment<br> mess  $message->attachment<br> attach  $attachment<br>";
$width = '90%';

$tabs = &New stdClass;
$tabs->names = array(get_string('inbox', 'mail'), get_string('outbox', 'mail'), get_string('compose', 'mail'));
$tabs->urls = array("view.php?id=" .$id . "&amp;op=inbox",
                    "view.php?id=" . $id . "&amp;op=outbox",
                    "view.php?id=" . $id . "&amp;op=compose",
                    );
$tabs->width = $width;

switch($op) {
    case 'delete':
    break;
    case 'inbox':
        $tabs->highlight = 0;
    break;
    case 'outbox':
        $tabs->highlight = 1;
    break;
    case 'reply':
    case 'compose':
        $tabs->highlight = 2;
    break;
}

if(isset($tabs->highlight)) {
    print_tabbed_heading($tabs);
}

switch($op) {
    case 'delete':
        
        $message = required_param('msg',PARAM_INT);
        redirect('view.php?id='.$id,"Deleting messages disabled temporarily",5);die();
        if(isset($_REQUEST['no'])) {
            // Cancel delete, so we won't check for permissions or anything
            redirect('view.php?id='.$id);
            die();
        }

        if(is_array($message)) {
            $select = 'id IN ('.implode(',', $message).')';
        }
        else {
            $select = 'id = '.intval($message);
        }
        $messages = $DB->get_records_select('mail_privmsgs', $select);

        if($messages === false) {
            // Invalid messages
            redirect('view.php?id=' . $id . '&amp;course='.$course->id);
            die();
        }

        if(isset($_REQUEST['yes'])) {
            if(!mail_delete_messages($messages)) {
                // Something failed
                redirect('view.php?id=' . $id . '&amp;course='.$course->id);
                die();
            }

            // Success
            redirect('view.php?id=' . $id . '&amp;course='.$course->id);
            die();
        }

        $count = count($messages);
        if($count == 1) {
            $message = reset($messages);
            print_heading(get_string('confirmation', 'mail'));
            mail_yesno(get_string('confirmdeletemessage', 'mail', $message->subject),
                array('id' => $id, 'op' => 'delete', 'course' => $course->id, 'msg' => $message->id));
        }
        else {
            print_heading(get_string('confirmation', 'mail'));
            mail_yesno(get_string('confirmdeletemessages', 'mail', $count),
                array('id' => $id, 'op' => 'delete', 'course' => $course->id, 'msg' => array_keys($messages)));
        }
    break;

    case 'read':
        $m_id = optional_param('message',0 ,PARAM_INTEGER);
        $message = $DB->get_record('mail_privmsgs', array('id'=>$m_id),"id");
        //var_export($message);
        if($message === false) {
            print_error('Invalid message id : '.$m_id);
        }

        $folder = mail_message_folder($message, $USER);
        if($folder === false) {
            error('Access denied');
        }

       
        print_heading(get_string('viewingprivatemsg', 'mail'));
        mail_print_message($message, $course);
        mail_mark_as_read($message);
       
        $folder = mail_message_folder($message, $USER);
        echo '<div style="text-align: center;">';
        echo '<table style="width: 80%; margin: auto;"><tr>';

        if($folder == PRIVMSGS_FOLDER_INBOX) {
            echo '<td style="text-align: right; width: 100%;">';
            echo '<form action="view.php" method="get">';
            echo '<input type="hidden" name="op" value="reply" />';
            echo '<input type="hidden" name="course" value="'.$course->id.'" />';
            echo '<input type="hidden" name="msg" value="'.$message->id.'" />';
            echo '<input type="hidden" name="id" value="'.$id.'" />';
            echo '<input type="submit" value="'.get_string('reply', 'mail').'" />';
            echo '</form></td>';
        }
        echo '<td style="text-align: right;">';
        echo '<form action="view.php" method="get">';
        echo '<input type="hidden" name="op" value="delete" />';
        echo '<input type="hidden" name="course" value="'.$course->id.'" />';
        echo '<input type="hidden" name="msg" value="'.$message->id.'" />';
        echo '<input type="hidden" name="id" value="'.$id.'" />';
        echo '<input type="submit" value="'.get_string('delete', 'mail').'" />';
        echo '</form></td>';
        echo '</tr></table></div>';
 
        

    break;


    case 'outbox':
        $messages = mail_get_outbox();
        if($messages === false) {
            print_simple_box_start('center', $width);
            print_heading('<p style="text-align: center;">'.get_string('nomessages', 'mail').'</p>');
            print_simple_box_end();
        }
        else {
            $table = new html_table();
            $table->head = array(get_string('subject', 'mail'), get_string('recipient', 'mail'), get_string('senton', 'mail'), '&nbsp;');
            $table->width = $width;
            $table->size = array('40%', '25%', '30%', '5%');
            $table->align = array('LEFT', 'CENTER', 'CENTER', 'CENTER');
            $table->data = array();
            foreach($messages as $message) {
                $user = mail_cache_getuser($message->touser);
		$paperclipimg = '<img src="'.$CFG->wwwroot.'/pix/spacer.gif" width="11px">';
		if($message->attachment_filename)
		   {
		     $paperclipimg = '<img src="'.$CFG->wwwroot.'/mod/mail/paperclip.png">';
		   }  
                $table->data[] = array($paperclipimg.'&nbsp;<a href="view.php?id=' . $id . '&amp;course='.$course->id.'&amp;op=read&amp;message='.$message->id.'">'.$message->subject.'</a>',
                                       '<a href="view.php?course='.$course->id.'&amp;id='.$id.'&amp;op=compose&amp;userid='.$user->id.'">'.fullname($user).'</a>',
                                       userdate($message->timesent),
                                       '<p><input type="checkbox" name="msg[]" value="'.$message->id.'" /></p>');
            }
            echo '<form action="view.php" method="post">';
            echo html_writer::table($table);
            echo '<div style="text-align: center;">';
            echo '<table style="width: '.$width.'; margin: auto;"><tr>';
            echo '<td style="text-align: right;">';
            echo '<input type="hidden" name="op" value="delete" />';
	    echo '<input type="hidden" name="id" value="  ' . $id . ' " />';
            echo '<input type="hidden" name="course" value="'.$course->id.'" />';
            echo '<input type="submit" value="'.get_string('delete', 'mail').'" />';
            echo '</td>';
            echo '</tr></table></div>';
            echo '</form>';
        }
    break;

    case 'inbox':
        $messages = mail_get_inbox();
        //var_export($messages);
        if($messages === false) {
            print_simple_box_start('center', $width);
            print_heading('<p style="text-align: center;">'.get_string('nomessages', 'mail').'</p>');
            print_simple_box_end();
        }
        else {
            $table = new html_table();
            $table->head = array(get_string('subject', 'mail'), get_string('sender', 'mail'), get_string('senton', 'mail'), '&nbsp;');
            $table->width = $width;
            $table->size = array('40%', '25%', '30%', '5%');
            $table->align = array('LEFT', 'CENTER', 'CENTER', 'CENTER');
            $table->data = array();
            foreach($messages as $message) {
                
                $user = mail_cache_getuser($message->fromuser);
                $paperclipimg = '<img src="'.$CFG->wwwroot.'/pix/spacer.gif" width="11px">';
                if($message->attachment_filename) {
		           $paperclipimg = '<img src="'.$CFG->wwwroot.'/mod/mail/paperclip.png">';
		           }  
                $class = $message->isread ? 'dimmed' : 'privmsg_unread';
                $table->data[] = array($paperclipimg.'&nbsp;'.
                    '<a class="'.$class.'" href="view.php?id=' . $id . '&amp;course='.$course->id.'&amp;op=read&amp;message='.$message->id.'">'.$message->subject.'</a>',
                    '<a href="view.php?course='.$course->id.'&amp;userid='.$user->id.'&amp;op=compose&amp;id=' .$id.'">'.fullname($user).'</a>',
                    userdate($message->timesent),
                    '<p><input type="checkbox" name="msg[]" value="'.$message->id.'" /></p>');
            }
            echo '<form action="view.php" method="post">';
            //var_export($table);
            
            echo html_writer::table($table);
            
            echo '<div style="text-align: center;">';
            echo '<table style="width: '.$width.'; margin: auto;"><tr>';
            echo '<td style="text-align: right;">';
            echo '<input type="hidden" name="op" value="delete" />';
	    echo '<input type="hidden" name="id" value="' . $id . '" />';
            echo '<input type="hidden" name="course" value="'.$course->id.'" />';
            echo '<input type="submit" value="'.get_string('delete', 'mail').'" />';
            echo '</td>';
            echo '</tr></table></div>';
            echo '</form>';
        }

    break;

    case 'reply':
        $msg = optional_param('msg', 0, PARAM_INT);
        if(!$message = $DB->get_record('mail_privmsgs', array('id'=>$msg))) {
            error('Invalid message id');
        }

        $folder = mail_message_folder($message, $USER);

        if($folder !== PRIVMSGS_FOLDER_INBOX) {
            error('Access denied');
        }

        
        if(($sender = $DB->get_record('user', array('id'=>$message->fromuser))) === false) {
            error('Invalid sender ID');
        }
        else if($sender->id == $USER->id) {
            error('You cannot reply to yourself');
        }

        $form = &New stdClass;
        $form->subject = get_string('re', 'mail').$message->subject;
        $form->message = '';
        $form->recipients = array($sender);
        $form->format = $defaultformat;
        echo '<div style="text-align: center;"><div style="width: '.$width.'; margin: auto;">';
        mail_print_compose($course, $form, array());
        echo '</div></div>';
    break;

    case 'compose':
        echo '<div style="text-align: center;"><div style="width: '.$width.'; margin: auto;">';
        $err = array();
        if(($message = data_submitted()) && empty($_REQUEST['defer'])) {
            // Check for recipient's wishes

            // Check for errors

            if(empty($message->subject)) {
                $err['subject'] = get_string('errornosubject', 'mail');
            }
            if(empty($message->message)) {
                $err['message'] = get_string('errornomessage', 'mail');
            }
            if($recip)
            {
            	foreach($recip as $mailgetters){
             	
                  if (!$user = $DB->get_record('user', array('id'=>$mailgetters))) {
                      $err['recipient'] = get_string('errorbadrecipient', 'mail');
                  }
                }
            
            }
            else{
            	$err['recipient'] = get_string('errornorecipient','mail');
            }
            
            	//var_export($_REQUEST['recipients']);	die;

            if(empty($err)) { //NO Errors
                $text = &New stdClass;
                $text->message = clean_text($message->message, $message->format);
                $text->format = $message->format;
                $text->id = $DB->insert_record('mail_text', $text, true);

                if($text->id === false) {
                    error('Could not insert message into the database');
                }

                $privmsg = &New stdClass;
                $privmsg->subject = clean_text($message->subject);
                $privmsg->textid = $text->id;
                $privmsg->fromuser = $USER->id;
                $privmsg->timesent = time();
                $privmsg->course = $course->id;
                $auditmsg = &New stdClass;
                
                $auditmsg->fromuser = fullname($USER);
                $auditmsg->subject = clean_text($message->subject);
                $auditmsg->messagetext = $text->message;
                $auditmsg->timesent = $privmsg->timesent;
                $auditmsg->remote_host = $_SERVER['REMOTE_ADDR'];

                $sentto = 0;
                foreach($recipients as $recipient) {
                    $privmsg->folder = PRIVMSGS_FOLDER_INBOX;
                    $privmsg->touser = $recipient->id;
                    $auditmsg->touser = fullname($recipient);

                    if(($attachment['size'])>0) 
                    {
                      $newfile = $attachment;
                                                                  
                      if(!(list ($privmsg->attachment_filename,$privmsg->attachment_data,$privmsg->attachment_type) = mail_add_attachment_sql($privmsg,$newfile)))
                        {
                          error("no attachment saved");
                        }
                       $auditmsg->attachment_filename = $privmsg->attachment_filename;
                       $auditmsg->attachment_type = $privmsg->attachment_type;                                  
                       $auditmsg->attachment_data = $privmsg->attachment_data;
                        
                    }

                    if($DB->insert_record('mail_privmsgs', $privmsg)) {
                        ++$sentto;
                    }
                    $privmsg->folder = PRIVMSGS_FOLDER_OUTBOX;
                    $DB->insert_record('mail_privmsgs', $privmsg);
                    
                    $DB->insert_record('mail_audit_trail',$auditmsg);
                    
                }
                print_simple_box_start('center', '100%');
                print_heading('<p style="text-align: center;">'.get_string('messagesent', 'mail').'</p>');
                print_continue('view.php?op=outbox&amp;course='.$course->id .'&amp;id=' . $id);
                print_simple_box_end();
                echo '</div></div>';
                break;
            }
            else {
                // Show the form again
                $form = $message;
                $form->recipients = $recipients;
            }
        }
        else {
            // No data submitted
            $form = &New stdClass;
            $form->subject = '';
            $form->message = '';
            $form->recipients = $recipients;
            $form->format = $defaultformat;
        }
        mail_print_compose($course, $form, $err);
        echo '</div></div>';
    break;
    default:
        error('Unknown operation');
    break;
}
/// Finish the page
    echo $OUTPUT->footer();

?>
