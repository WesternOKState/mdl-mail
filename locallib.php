<?php

/*
 * Internal library of functions for module mail
 *
 * All the mail specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod
 * @subpackage mail
 * @copyright  2011 Brian Carpenter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Does something really useful with the passed things
 *
 * @param array $things
 * @return object
 */
//function mail_do_something_useful(array $things) {
//    return new stdClass();
//}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other mail functions go here.  Each of them must have a name that 
/// starts with mail
define('PRIVMSGS_FOLDER_INBOX',		0);
define('PRIVMSGS_FOLDER_OUTBOX',	1);

require_once($CFG->dirroot.'/mod/mail/lib.php');
require_once($CFG->dirroot.'/lib/weblib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/lib/accesslib.php');

function mail_isteacher($courseid,$userid){
    global $USER;
    

}


function mail_count_unread($user = NULL) {
    if($user === NULL) {
        global $USER,$DB;
        if(!$USER->id) {
            return 0;
        }
        $user = $USER->id;
    }
    $msgs = $DB->get_records_select('mail_privmsgs', 'touser = '.$user.' AND isread = 0 AND folder = '.PRIVMSGS_FOLDER_INBOX);
    return empty($msgs) ? 0 : count($msgs);
}

function mail_delete_messages($messages) {
    global $USER,$DB;

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
    $DB->delete_records_select('mail_privmsgs', $select_msgs);

    $select = 'textid IN ('.implode(',', array_keys($textids)).')';
    $remaining = $DB->get_records_select('mail_privmsgs', $select,null, 'textid, textid');

    if($remaining === false) {
        $remaining = array();
    }

    // Now, we want to kill texts with IDs that are in $textids BUT NOT in $remaining
    $textids = array_diff($textids, array_keys($remaining));

    if(!empty($textids)) {
        $select_text = 'id IN ('.implode(',', array_keys($textids)).')';
        $DB->delete_records_select('mail_text', $select_text);
    }

    // Successfully killed
    return true;
}

function mail_print_message($messageid, $course) {
    global $CFG, $THEME, $USER,$DB,$PAGE,$OUTPUT;
    $message = $DB->get_record('mail_privmsgs',array('id'=>$messageid->id),"id,course,folder,subject,fromuser,touser,textid,timesent,isread,attachment_filename,attachment_type");
    //var_export($message);
    $text = $DB->get_record('mail_text', array('id'=>$message->textid));
    //var_export($text);
    $options = new stdClass;
    $options->filter=true;
    $output = format_text($text->message,$text->format,$options,$course->id);
    if($text === false) {
      	error('Invalid message textid');
    }

    $sender = $DB->get_record('user', array('id'=>$message->fromuser));
    $recipient = $DB->get_record('user', array('id'=>$message->touser));
    if($sender === false || $recipient === false) {
        return false;
    }

    echo '<p style="text-align: center;">';
    echo '<table style="width: 80%; margin: auto;" class="forumheaderlist">';
    echo '<tr><td rowspan="2" style="background-color: '.$THEME->cellcontent2.'; width: 35px; vertical-align: top;">';
        //$userpic = new moodle_user_picture();
        //$userpic->user = $sender->id;
        //$userpic->courseid = $course->id;
        //echo $OUTPUT->user_picture($userpic);
        //print_user_picture($sender->id, $course->id, $sender->picture, false);
        echo $OUTPUT->user_picture($sender,array('courseid'=>$course->id));
	echo '</td>';
	echo "<td bgcolor=\"$THEME->cellheading2\" class=\"forumpostheadertopic\" width=\"100%\">";
	echo "<p>";
    echo "<font size=3><b>$message->subject</b></font><br />";
    echo "<font size=2>";

    //$senderfullname = fullname($sender, has_capability($capability, $context));
    $senderfullname = fullname($sender);
    //$recipientfullname = fullname($recipient, isteacher($course->id, $recipient->id));
    $recipientfullname = fullname($recipient);
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
    echo "<tr><td  class=\"forumpostmessage\">\n";
    
    //var_export($output);
    echo format_text($text->message);
    echo '</td></tr></table>';
    echo '</p>';




    return true;
}

function mail_print_compose($course, $form, $err) {
    global $USER;
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
    if(has_capability("mod/mail:teacher",$context))
     {
       $users = get_enrolled_users($context,"mod/mail:student");
     }
    if(has_capability("mod/mail:student",$context))
    {
       $users = get_enrolled_users($context,"mod/mail:teacher");
    }
    //$users = get_course_users($course->id);
    
    $usermenu = array();
    unset($users[$USER->id]);
    foreach($users as $uid => $user) {
        $usermenu[$uid] = fullname($user);
    }
    //print_side_block_start(get_string('newmessage', 'mail'), '100%');
    //echo "ffgg";
    //var_export($usermenu);
    include('mail.html');
    //print_side_block_end();
}

function mail_mark_as_read($message) {
    global $DB;
    if($message->isread) {
        return true;
    }
    $message->isread = 1;
    return $DB->set_field('mail_privmsgs', 'isread', 1,array('id'=>$message->id));
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
     global $DB;

     $message = $DB->get_record('mail_audit_trail','id',$id);
     
     Header("Content-type: $message->attachment_type");
     header("Content-Disposition: attachment; filename=\"$message->attachment_filename\"");
     echo $message->attachment_data;
}


function mail_add_attachment_sql($privmsgs, $newfile) {
// $post is a full post record, including course and forum
// $newfile is a full upload array from $_FILES
// If successful, this function returns the name of the file

    global $CFG;
    global $DB;

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
            if ($data = fread(fopen($newfile['tmp_name'], "r"), filesize($newfile['tmp_name']))) 
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
    $data = base64_encode($data);
    return array($newfile_name,$data,$type);
}


function mail_add_attachment($privmsgs, $newfile) {
// $post is a full post record, including course and forum
// $newfile is a full upload array from $_FILES
// If successful, this function returns the name of the file

    global $CFG;
    global $DB;

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
    global $DB;
    $message = $DB->get_record('mail_privmsgs',array('id'=>$messageid->id));
    if($message->fromuser == $user->id && $message->folder == PRIVMSGS_FOLDER_OUTBOX) {
        return PRIVMSGS_FOLDER_OUTBOX;
    }
    if($message->touser == $user->id && $message->folder == PRIVMSGS_FOLDER_INBOX) {
        return PRIVMSGS_FOLDER_INBOX;
    }
    return false;
}

function mail_get_inbox($user = NULL) {
        global $COURSE,$DB;
	if($user === NULL) {
		global $USER, $COURSE;
		if(!$USER->id) {
			return array();
		}
		$user = $USER->id;
                $course_id = $COURSE->id;
	}
	return $DB->get_records_select('mail_privmsgs', 'touser = '.$user.' AND course = '.$course_id.' AND folder = '.PRIVMSGS_FOLDER_INBOX,null,'timesent DESC');
}

function mail_get_outbox($user = NULL) {
    global $DB;
	if($user === NULL) {
		global $USER, $COURSE;
		if(!$USER->id) {
			return array();
		}
		$user = $USER->id;
                $course_id = $COURSE->id;
	}
	return $DB->get_records_select('mail_privmsgs', 'fromuser = '.$user.' AND course = '.$course_id.' AND folder = '.PRIVMSGS_FOLDER_OUTBOX,null, 'timesent DESC');
}

function mail_cache_getuser($id) {
        global $DB;
	static $users = array();

	if(isset($users[$id])) {
		return $users[$id];
	}
	if (!$user = $DB->get_record('user', array('id'=>$id))) {
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
				echo "<td valign=top class=\"generaltabselected\" $alignment $width $wrapping >$tab</td>\n";
			} else {
				echo "<td valign=top class=\"generaltab\" $alignment $width $wrapping >$tab</td>\n";
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
	echo "<tr><td colspan=\"$ncells\">".
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
