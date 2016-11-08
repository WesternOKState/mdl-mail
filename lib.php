<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants for module mail
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the mail specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod
 * @subpackage mail
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** example constant */
//define('NEWMODULE_ULTIMATE_ANSWER', 42);

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function mail_supports($feature) {
    switch($feature) {
        case FEATURE_GRADE_HAS_GRADE:   return false;
        case FEATURE_GRADE_OUTCOMES:    return false;
        case FEATURE_MOD_INTRO:         return false;
        default:                        return null;
    }
}

/**
 * Saves a new instance of the mail into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $mail An object from the form in mod_form.php
 * @param mod_mail_mod_form $mform
 * @return int The id of the newly inserted mail record
 */
function mail_add_instance(stdClass $mail, mod_mail_mod_form $mform = null) {
    global $DB;

    $mail->timecreated = time();
    $course = required_param("course",PARAM_INT);
    
    $mail->course = $course;

    # You may have to add extra stuff in here #
/*
 var_export($mail);
 var_export($mform);
*/
    return $DB->insert_record('mail', $mail);
}

/**
 * Updates an instance of the mail in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $mail An object from the form in mod_form.php
 * @param mod_mail_mod_form $mform
 * @return boolean Success/Fail
 */
function mail_update_instance(stdClass $mail, mod_mail_mod_form $mform = null) {
    global $DB;

    $mail->timemodified = time();
    $mail->id = $mail->instance;

    # You may have to add extra stuff in here #

    return $DB->update_record('mail', $mail);
}

/**
 * Removes an instance of the mail from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function mail_delete_instance($id) {
    global $DB;

    if (! $mail = $DB->get_record('mail', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records('mail', array('id' => $mail->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function mail_user_outline($course, $user, $mod, $mail) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $mail the module instance record
 * @return void, is supposed to echp directly
 */
function mail_user_complete($course, $user, $mod, $mail) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in mail activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function mail_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link mail_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function mail_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see mail_get_recent_mod_activity()}

 * @return void
 */
function mail_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * function to be ran from mail_cron to get all unread emails from courses visible
 */
function mail_cron_getUnread() {
    global $DB;
    
    //$allUnread = $DB->get_records_sql("SELECT id,touser FROM {mail_privmsgs} where folder=0 and isread=0 group by touser;");
    $allUnread = $DB->get_records_sql("SELECT p.id,touser FROM {mail_privmsgs} as p inner join {course} as t1 on course = t1.id where folder=0 and isread=0 and t1.visible = 1 group by touser;");
    //need to clean up allUnread and combine multi class students to one listing in array
    
    
    
    
    
    return $allUnread;    
    
}

/**
 * function to get all unread counts for a specific user 
 * and generate html and text for email body
 * @param stdClass $user
 * @return array html and text email body
 */
function mail_cron_email_for_user( stdClass  $user) {
    global $DB,$CFG;
    if($user){
      $allUnread = $DB->get_records_sql("SELECT  course,count(*) as number FROM {mail_privmsgs} as p inner join {course} as t1 on course = t1.id where folder=0 and isread=0 and t1.visible = 1 and touser=$user->id group by touser,course;");
    }
    $html ="<head></head><body id=email>";
    $html .= "<div>You have unread email in the following class(es):<hr/>";
    $text = "You have unread email in the following class(es):\n------------------------------";
    foreach($allUnread as $coursemsgs) {
        $course = $DB->get_record('course',array('id'=>$coursemsgs->course));
        $courseName = format_string($course->fullname);
        $html .= '<div><a target="_blank" href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'
                .$courseName.'</a>&nbsp; has '.$coursemsgs->number.'&nbsp; unread message(s) for you';
        $text .="\n$courseName has $coursemsgs->number unread message(s) for you\n";
    }
    $html .= "</div></body>";
    return array($html,$text);        
    
}

function mail_instant_notify( stdClass $message) {
    global $DB,$CFG;
    $site = get_site();
    if($message) {
        $userid = $message->fromuser;
        $fromuserObj = $DB->get_record('user',array('id'=>$userid));
        $touserObj = $DB->get_record('user',array('id'=>$message->touser));
        $course = $DB->get_record('course',array('id'=>$message->course));
        $textBody = "new Course Email message to you from ".fullname($fromuserObj)." in course: ".format_string($course->fullname);
        $htmlBody = "<head></head><body><div>"
                . "New Course Email message sent to you from ".fullname($fromuserObj)." in course "
                . "<a href=\"".$CFG->wwwroot."/course/view.php?id=".$course->id."\">".format_string($course->fullname)."</a>"
                . "</div></body>";
        email_to_user($touserObj,$site->shortname,"NEW Course email!",$textBody,$htmlBody);
    }
    
}


/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function mail_cron () {
    global $CFG,$USER,$DB;
    $site = get_site();
    $unreadMsgs = mail_cron_getUnread();
    $codifiedArray = array(array(array('course'=>0,'num'=>0)));
    foreach($unreadMsgs as $instance){
        /*
         echo "-------------\n";
        
        mtrace(json_encode($instance));
        echo "-------------\n";
         * 
         */
        $userid = $instance->touser;
                       
        $userObj = $DB->get_record('user',array('id'=>$userid));
        if($userObj) {
          list($html,$text) = mail_cron_email_for_user($userObj);
        //$courseObj =$DB->get_record('course',array('id'=>$instance->course));
        
       // mtrace($html);sleep(1);
           $mailresult = email_to_user($userObj,$site->shortname,"Unread Course email notification",$text,$html);
           if(!$mailresult) {
            mtrace("error $userObj->username notification not sent");
           }
        }
      
            
      
     }
     
    return true;
}

/**
 * Returns an array of users who are participanting in this mail
 *
 * Must return an array of users who are participants for a given instance
 * of mail. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $mailid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function mail_get_participants($mailid) {
    return false;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function mail_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of mail?
 *
 * This function returns if a scale is being used by one mail
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $mailid ID of an instance of this module
 * @return bool true if the scale is used by the given mail instance
 */
function mail_scale_used($mailid, $scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('mail', array('id' => $mailid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of mail.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any mail instance
 */
function mail_scale_used_anywhere($scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('mail', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give mail instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $mail instance object with extra cmidnumber and modname property
 * @param reset grades in the gradebook
 * @return void
 */
function mail_grade_item_update(stdClass $mail, $reset = false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $item = array();
    $item['itemname'] = clean_param($mail->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
   if ($mail->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $mail->grade;
        $item['grademin']  = 0;
    } else if ($mail->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$mail->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }
    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('mod/mail', $mail->course, 'mod', 'mail', $mail->id, 0, null, $item);
}

/**
 * Update mail grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $mail instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function mail_update_grades(stdClass $mail, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $grades = array(); // populate array of grade objects indexed by userid

    grade_update('mod/mail', $mail->course, 'mod', 'mail', $mail->id, 0, $grades);
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function mail_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * Serves the files from the mail file areas
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return void this should never return to the caller
 */
function mail_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding mail nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the mail module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function mail_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the mail settings
 *
 * This function is called when the context for the page is a mail module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $mailnode {@link navigation_node}
 */
function mail_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $mailnode=null) {
}
