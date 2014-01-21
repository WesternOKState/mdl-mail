<?PHP  // $Id: lib.php,v 1.16 2008/05/16 19:05:40 bcarp Exp $


//require_once("$CFG->dirroot/mod/mail/mimetypes.php"); //for attachments
require_once("$CFG->dirroot/lib/weblib.php");
$mail_CONSTANT = 7;     /// for example


function mail_add_instance($mail) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will create a new instance and return the id number 
/// of the new instance.
    $course = required_param("course",PARAM_INT);
    $mail->timemodified = time();
    $mail->course = $course;
    # May have to add extra stuff in here #
    $res = insert_record("mail", $mail);
//    echo "res = " . $res;
    
    return $res;
}




function mail_update_instance($mail) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will update an existing instance with new data.

    $mail->timemodified = time();
    $mail->id = $mail->instance;

    # May have to add extra stuff in here #

    return update_record("mail", $mail);
}


function mail_delete_instance($id) {
/// Given an ID of an instance of this module, 
/// this function will permanently delete the instance 
/// and any data that depends on it.  

    if (! $mail = get_record("mail", "id", "$id")) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #

    if (! delete_records("mail", "id", "$mail->id")) {
        $result = false;
    }

    return $result;
}

function mail_user_outline($course, $user, $mod, $mail) {
/// Return a small object with summary information about what a 
/// user has done with a given particular instance of this module
/// Used for user activity reports.
/// $return->time = the time they did it
/// $return->info = a short text description

    return $return;
}

function mail_user_complete($course, $user, $mod, $mail) {
/// Print a detailed representation of what a  user has done with 
/// a given particular instance of this module, for user activity reports.

    return true;
}

function mail_print_recent_activity($course, $isteacher, $timestart) {
/// Given a course and a time, this module should find recent activity 
/// that has occurred in mail activities and print it out. 
/// Return true if there was output, or false is there was none.

    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

function mail_cron () {
/// Function to be run periodically according to the moodle cron
/// This function searches for things that need to be done, such 
/// as sending out mail, toggling flags etc ... 

    global $CFG;

    return true;
}

function mail_grades($mailid) {
/// Must return an array of grades for a given instance of this module, 
/// indexed by user.  It also returns a maximum allowed grade.
///
///    $return->grades = array of grades;
///    $return->maxgrade = maximum allowed grade;
///
///    return $return;

   return NULL;
}

function mail_get_participants($mailid) {
//Must return an array of user records (all data) who are participants
//for a given instance of mail. Must include every user involved
//in the instance, independient of his role (student, teacher, admin...)
//See other modules as example.

    return false;
}

function mail_scale_used ($mailid,$scaleid) {
//This function returns if a scale is being used by one mail
//it it has support for grading and scales. Commented code should be
//modified if necessary. See forum, glossary or journal modules
//as reference.
   
    $return = false;

    //$rec = get_record("mail","id","$mailid","scale","-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}
   
    return $return;
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other mail functions go here.  Each of them must have a name that 
/// starts with mail_


define('PRIVMSGS_FOLDER_INBOX',		0);
define('PRIVMSGS_FOLDER_OUTBOX',	1);

require_once($CFG->dirroot.'/lib/weblib.php');
require_once($CFG->dirroot.'/course/lib.php');

function mail_count_unread($user = NULL) {
    if($user === NULL) {
        global $USER;
        if(!$USER->id) {
            return 0;
        }
        $user = $USER->id;
    }
    $msgs = get_records_select('mail_privmsgs', 'touser = '.$user.' AND isread = 0 AND folder = '.PRIVMSGS_FOLDER_INBOX);
    return empty($msgs) ? 0 : count($msgs);
}

function mail_delete_messages($messages) {
    global $USER;

    $textids = array();
    foreach($messages as $message) {
        $folder = mail_message_folder($message, $USER);
        if($folder === false) {
            unset($messages[$message->id]);
        }
        else {
            // Key is specified to ignore duplicates
            $textids[$message->textid] = $message->textid;
        }
    }

    if(empty($messages)) {
        // Access denied for all messages
        return false;
    }

    // Metallica: Kill 'em All (1983)
    $select_msgs = 'id IN ('.implode(',', array_keys($messages)).')';
    delete_records_select('mail_privmsgs', $select_msgs);

    $select = 'textid IN ('.implode(',', array_keys($textids)).')';
    $remaining = get_records_select('mail_privmsgs', $select, '', 'textid, textid');

    if($remaining === false) {
        $remaining = array();
    }

    // Now, we want to kill texts with IDs that are in $textids BUT NOT in $remaining
    $textids = array_diff($textids, array_keys($remaining));

    if(!empty($textids)) {
        $select_text = 'id IN ('.implode(',', array_keys($textids)).')';
        delete_records_select('mail_text', $select_text);
    }

    // Successfully killed
    return true;
}

function mail_print_message($messageid, $course) {
    global $CFG, $THEME, $USER;
    $message = get_record('mail_privmsgs','id',$messageid->id,'','','','',"id,course,folder,subject,fromuser,touser,textid,timesent,isread,attachment_filename,attachment_type");
    //var_export($message);
    $text = get_record('mail_text', 'id', $message->textid);
    //var_export($text);
    $options = new stdClass;
    $options->filter=true;
    $output = format_text($text->message,$text->format,$options,$course->id);
    if($text === false) {
      	error('Invalid message textid');
    }

    $sender = get_record('user', 'id', $message->fromuser);
    $recipient = get_record('user', 'id', $message->touser);
    if($sender === false || $recipient === false) {
        return false;
    }

    echo '<p style="text-align: center;">';
    echo '<table style="width: 80%; margin: auto;" class="forumheaderlist">';
    echo '<tr><td rowspan="2" style="background-color: '.$THEME->cellcontent2.'; width: 35px; vertical-align: top;">';
	print_user_picture($sender->id, $course->id, $sender->picture, false);
	echo '</td>';
	echo "<td bgcolor=\"$THEME->cellheading2\" class=\"forumpostheadertopic\" width=\"100%\">";
	echo "<p>";
    echo "<font size=3><b>$message->subject</b></font><br />";
    echo "<font size=2>";

    $senderfullname = fullname($sender, isteacher($course->id, $sender->id));
    $recipientfullname = fullname($recipient, isteacher($course->id, $recipient->id));
    $msg->sender = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$sender->id.'&amp;course='.$course->id.'">'.$senderfullname.'</a>';
    $msg->recipient = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$recipient->id.'&amp;course='.$course->id.'">'.$recipientfullname.'</a>';
    $msg->date = userdate($message->timesent);

    print_string('fromtodate', 'mail', $msg);
    
    $attachmenthtml = '';
    if($message->attachment_filename) {
       
       //$message->course = $course->id;
      // $filearea = mail_file_area_name($message);
       $filename = $message->attachment_filename;
       //$file = $filearea .'/'.$filename;
       $icon = '<img src="'.$CFG->wwwroot.'/mod/mail/paperclip.png">';
       $alt_attachment = get_string('alt_attachment','mail');
       $image = "$icon ";
       $attachmenthtml = '<div align="right"><a href="file.php?id='.$message->id.'">'.$image.$filename.'</a></div>';
    }
    
    echo $attachmenthtml;
    echo "</font></p></td></tr>";
    echo "<tr><td bgcolor=\"$THEME->cellcontent\" class=\"forumpostmessage\">\n";
    
    //var_export($output);
    echo format_text($text->message,$text->format,null,null);
    echo '</td></tr></table>';
    echo '</p>';




    return true;
}

function mail_print_compose($course, $form, $err) {
    global $USER;
    if(isteacher($course->id))
     {
       $users = get_course_students($course->id,"lastname");
     }
    if(isstudent($course->id))
    {
       $users = get_course_teachers($course->id);
    }
    //$users = get_course_users($course->id);
    $usermenu = array();
    unset($users[$USER->id]);
    foreach($users as $uid => $user) {
        $usermenu[$uid] = fullname($user);
    }
    print_side_block_start(get_string('newmessage', 'mail'), '100%');
    //echo "ffgg";
    include('mail.html');
    print_side_block_end();
}

function mail_mark_as_read($message) {
    if($message->isread) {
        return true;
    }
    $message->isread = 1;
    return set_field('mail_privmsgs', 'isread', 1, 'id', $message->id);
}

function mail_yesno($message, $vars = array()) {
    global $THEME;
    print_simple_box_start('center', '60%', $THEME->cellheading);
    echo '<p style="text-align: center; margin-top: 1em;"><strong>'.$message.'</strong></p>';
    echo '<form method="get" action="view.php">';
    echo '<p style="text-align: center;">';
    foreach($vars as $name => $value) {
        if(is_array($value)) {
            foreach($value as $val) {
                echo '<input type="hidden" name="'.$name.'[]" value="'.$val.'" />';
            }
        }
        else {
            echo '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
        }
    }
    echo '<input style="margin-left: 2em;" type="submit" name="yes" value=" '.get_string('yes').' " /> ';
    echo '<input style="margin-left: 2em;" type="submit" name="no" value=" '.get_string('no').' " /> ';
    echo '</p>';
    echo '</form>';
    print_simple_box_end();
}

function mail_file_area_name($privmsgs) {
//  Creates a directory file name, suitable for make_upload_directory()
    global $CFG;

    return "$privmsgs->course/$CFG->moddata/mail/attachments";
}

function mail_file_area($privmsgs) {
    return make_upload_directory( mail_file_area_name($privmsgs) );
}


function mail_delete_old_attachments($privmsgs, $exception="") {
// Deletes all the user files in the attachments area for a post
// EXCEPT for any file named $exception

    if ($basedir = mail_file_area($privmsgs)) {
        if ($files = get_directory_list($basedir)) {
            foreach ($files as $file) {
                if ($file != $exception) {
                    unlink("$basedir/$file");
                    notify("Existing file '$file' has been deleted!");
                }
            }
        }
        if (!$exception) {  // Delete directory as well, if empty
            rmdir("$basedir");
        }
    }
}


function mail_get_attachment_sql($privmsgs){
//incomplete do not use
     global $CFG;

     $message = get_record('mail_audit_trail','id',$id);
     
     Header("Content-type: $message->attachment_type");
     header("Content-Disposition: attachment; filename=\"$message->attachment_filename\"");
     echo $message->attachment_data;
}


function mail_add_attachment_sql($privmsgs, $newfile) {
// $post is a full post record, including course and forum
// $newfile is a full upload array from $_FILES
// If successful, this function returns the name of the file

    global $CFG;

    if (empty($newfile['name'])) {
        return "";

    }
    //if (!$privm = get_record("privmsgs", "id", $privmsgs->id)) {
    //    return "";
    //}

   

    $maxbytes = get_max_upload_file_size($CFG->maxbytes, $course->maxbytes, $privmsgs->maxbytes);

    $newfile_name = clean_filename($newfile['name']);

    if (valid_uploaded_file($newfile))
     {
        if ($maxbytes and $newfile['size'] > $maxbytes) 
        {
            return "";
        }
        if (! $newfile_name) 
        {
            notify("This file had a wierd filename and couldn't be uploaded");

        } 
        else 
        {
            if ($data = addslashes(fread(fopen($newfile['tmp_name'], "r"), filesize($newfile['tmp_name'])))) 
            {
               $type = $newfile['type'];
               
            }
            else
            {
                notify("An error happened while saving the file on the server");
                $newfile_name = "";
            }
        }
    }
    else 
    {
        $newfile_name = "";
    }

    return array($newfile_name,$data,$type);
}


function mail_add_attachment($privmsgs, $newfile) {
// $post is a full post record, including course and forum
// $newfile is a full upload array from $_FILES
// If successful, this function returns the name of the file

    global $CFG;

    if (empty($newfile['name'])) {
        return "";

    }
    //if (!$privm = get_record("privmsgs", "id", $privmsgs->id)) {
    //    return "";
    //}

   

    $maxbytes = get_max_upload_file_size($CFG->maxbytes, $course->maxbytes, $privmsgs->maxbytes);

    $newfile_name = clean_filename($newfile['name']);

    if (valid_uploaded_file($newfile))
     {
        if ($maxbytes and $newfile['size'] > $maxbytes) 
        {
            return "";
        }
        if (! $newfile_name) 
        {
            notify("This file had a wierd filename and couldn't be uploaded");

        } 
        else if (! $dir = mail_file_area($privmsgs))
        {
            notify("Attachment could not be stored");
            $newfile_name = "";

        } 
        else 
        {
            if (move_uploaded_file($newfile['tmp_name'], "$dir/$newfile_name")) 
            {
                chmod("$dir/$newfile_name", $CFG->directorypermissions);
             //   mail_delete_old_attachments($privmsgs, $newfile_name);
            }
            else
            {
                notify("An error happened while saving the file on the server");
                $newfile_name = "";
            }
        }
    }
    else 
    {
        $newfile_name = "";
    }

    return $newfile_name;
}




function mail_message_folder($messageid, $user) {
    $message = get_record('mail_privmsgs','id',$messageid->id);
    if($message->fromuser == $user->id && $message->folder == PRIVMSGS_FOLDER_OUTBOX) {
        return PRIVMSGS_FOLDER_OUTBOX;
    }
    if($message->touser == $user->id && $message->folder == PRIVMSGS_FOLDER_INBOX) {
        return PRIVMSGS_FOLDER_INBOX;
    }
    return false;
}

function mail_get_inbox($user = NULL) {
        global $COURSE;
	if($user === NULL) {
		global $USER, $COURSE;
		if(!$USER->id) {
			return array();
		}
		$user = $USER->id;
                $course_id = $COURSE->id;
	}
	return get_records_select('mail_privmsgs', 'touser = '.$user.' AND course = '.$course_id.' AND folder = '.PRIVMSGS_FOLDER_INBOX, 'timesent DESC');
}

function mail_get_outbox($user = NULL) {
	if($user === NULL) {
		global $USER, $COURSE;
		if(!$USER->id) {
			return array();
		}
		$user = $USER->id;
                $course_id = $COURSE->id;
	}
	return get_records_select('mail_privmsgs', 'fromuser = '.$user.' AND course = '.$course_id.' AND folder = '.PRIVMSGS_FOLDER_OUTBOX, 'timesent DESC');
}

function mail_cache_getuser($id) {
	static $users = array();

	if(isset($users[$id])) {
		return $users[$id];
	}
	if (!$user = get_record('user', 'id', $id) ) {
    		error("No such user in this course");
	}
	return $users[$user->id] = $user;
}

//////////////////////////////////////////////////////////////////////////////////////
function print_tabbed_heading($tabs) {
// Prints a tabbed heading where one of the tabs highlighted.
// $tabs is an object with several properties.
// 		$tabs->names      is an array of tab names
//		$tabs->urls       is an array of links
// 		$tabs->align     is an array of column alignments (defaults to "center")
// 		$tabs->size      is an array of column sizes
// 		$tabs->wrap      is an array of "nowrap"s or nothing
// 		$tabs->highlight    is an index (zero based) of "active" heading .
// 		$tabs->width     is an percentage of the page (defualts to 80%)
// 		$tabs->cellpadding    padding on each cell (defaults to 5)

	global $CFG, $THEME;

    if (isset($tabs->names)) {
        foreach ($tabs->names as $key => $name) {
            if (!empty($tabs->urls[$key])) {
				$url =$tabs->urls[$key];
				if ($tabs->highlight == $key) {
					$tabcontents[$key] = "<b>$name</b>";
				} else {
					$tabcontents[$key] = "<a class= \"dimmed\" href=\"$url\"><b><font color=\"black\">$name</font></b></a>";
				}
            } else {
                $tabcontents[$key] = "<b>$name</b>";
            }
        }
    }

    if (empty($tabs->width)) {
        $tabs->width = "80%";
    }

    if (empty($tabs->cellpadding)) {
        $tabs->cellpadding = "5";
    }

    // print_simple_box_start("center", "$table->width", "#ffffff", 0);
    echo "<table width=\"$tabs->width\" border=\"0\" valign=\"top\" align=\"center\" ";
    echo " cellpadding=\"$tabs->cellpadding\" cellspacing=\"0\" class=\"generaltable\">\n";

    if (!empty($tabs->names)) {
        echo "<tr>";
		echo "<td  class=\"generaltablecell\">".
			"<img width=\"10\" src=\"$CFG->wwwroot/pix/spacer.gif\" alt=\"\"></td>\n";
        foreach ($tabcontents as $key => $tab) {
            if (isset($align[$key])) {
				$alignment = "align=\"$align[$key]\"";
			} else {
                $alignment = "align=\"center\"";
            }
            if (isset($size[$key])) {
                $width = "width=\"$size[$key]\"";
            } else {
				$width = "";
			}
            if (isset($wrap[$key])) {
				$wrapping = "no wrap";
			} else {
                $wrapping = "";
            }
			if ($key == $tabs->highlight) {
				echo "<td valign=top class=\"generaltabselected\" $alignment $width $wrapping bgcolor=\"$THEME->cellheading2\">$tab</td>\n";
			} else {
				echo "<td valign=top class=\"generaltab\" $alignment $width $wrapping bgcolor=\"$THEME->cellheading\">$tab</td>\n";
			}
		echo "<td  class=\"generaltablecell\">".
			"<img width=\"10\" src=\"$CFG->wwwroot/pix/spacer.gif\" alt=\"\"></td>\n";
        }
        echo "</tr>\n";
    } else {
		echo "<tr><td>No names specified</td></tr>\n";
	}
	// bottom stripe
	$ncells = count($tabs->names)*2 +1;
	$height = 2;
	echo "<tr><td colspan=\"$ncells\" bgcolor=\"$THEME->cellheading2\">".
		"<img height=\"$height\" src=\"$CFG->wwwroot/pix/spacer.gif\" alt=\"\"></td></tr>\n";
    echo "</table>\n";
	// print_simple_box_end();

    return true;
}

function choose_from_menu_multi ($options, $name, $selected='', $nothing='choose', $script='',
                           $nothingvalue='0', $return=false, $disabled=false, $tabindex=0) {

    if ($nothing == 'choose') {
        $nothing = get_string('choose') .'...';
    }

    $attributes = ($script) ? 'onchange="'. $script .'"' : '';
    if ($disabled) {
        $attributes .= ' disabled="disabled"';
    }

    if ($tabindex) {
        $attributes .= ' tabindex="'.$tabindex.'"';
    }

    $output = '<select id="menu'.$name.'" name="'. $name .'" '. $attributes .' multiple>' . "\n";
    if ($nothing) {
        $output .= '   <option value="'. $nothingvalue .'"'. "\n";
        if ($nothingvalue === $selected) {
            $output .= ' selected="selected"';
        }
        $output .= '>'. $nothing .'</option>' . "\n";
    }
    if (!empty($options)) {
        foreach ($options as $value => $label) {
            $output .= '   <option value="'. $value .'"';
            if ((string)$value == (string)$selected) {
                $output .= ' selected="selected"';
            }
            if ($label === '') {
                $output .= '>'. $value .'</option>' . "\n";
            } else {
                $output .= '>'. $label .'</option>' . "\n";
            }
        }
    }
    $output .= '</select>' . "\n";

    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}



?>
