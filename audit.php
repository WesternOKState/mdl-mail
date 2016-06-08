<?PHP    // $Id: audit.php,v 1.5 2007/01/26 21:47:04 bcarp Exp $
// $Log: audit.php,v $
// Revision 1.5  2007/01/26 21:47:04  bcarp
// *** empty log message ***
//
// Revision 1.4  2005/05/24 16:17:20  bcarp
// fixing audit nav menu
//
// Revision 1.3  2005/05/19 20:28:06  bcarp
// sort list by time descending.
//
// Revision 1.2  2005/05/19 20:22:46  bcarp
// attachments no longer stored as files. In mysql tables now.
//

   require_once("../../config.php");
//    require_once("lib.php");



$CFG->debug = 6143;
$CFG->debugdisplay =1;

/// Print the main part of the page
$id=optional_param("id",0,PARAM_INT);
$mid=optional_param("mid",0,PARAM_INT);
$op=optional_param("op", 'list',PARAM_ALPHANUM);
$context = get_context_instance(CONTEXT_MODULE, $id);

if ($id) {
      $cm         = get_coursemodule_from_id('mail', $id, 0, false, MUST_EXIST);
      $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
      $mail  = $DB->get_record('mail', array('id' => $cm->instance), '*', MUST_EXIST);
    } else {
        error('You must specify a course_module ID or an instance ID');
    }

if(has_capability('mod/mail:audit', $context))
{
  switch($op)
  {
  case 'list':
  
   if ($id) {
      $cm         = get_coursemodule_from_id('mail', $id, 0, false, MUST_EXIST);
      $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
      $mail  = $DB->get_record('mail', array('id' => $cm->instance), '*', MUST_EXIST);
    } else {
        error('You must specify a course_module ID or an instance ID');
    }
    require_login($course->id);

    add_to_log($course->id, "mail", "view", "view.php?id=$cm->id", "$mail->id");

/// Print the page header

    if ($course->category) {
        $navigation = "<A HREF=\"../../course/view.php?id=$course->id\">$course->shortname</A> ->";
    }

    $strmails = get_string("modulenameplural", "mail");
    $strmail  = get_string("modulename", "mail");

   print_header("Mail: Audit", "Auditing Messages",
                 "$navigation <A HREF=index.php?id=$course->id>$strmails</A> -> $mail->name", 
                  "", "", true,"" , 
                  navmenu($course, $cm));

  
  
  
  
     $messages = $DB->get_records("mail_audit_trail",array("courseid"=>$course->id));
     //var_export($course); //die("REPRESSED");
     $table = new stdClass() or die("Help help");
     $table = new html_table();
     $table->head = array(get_string('sender','mail'), get_string('recipient', 'mail'),get_string('subject', 'mail'), get_string('senton', 'mail'),'&nbsp;') or die("Help help");
     $table->width = '100%';
     $table->size = array('20%', '20%', '20%', '25%','15%');
     $table->align = array('LEFT', 'CENTER', 'CENTER', 'CENTER');
     $table->data = array();
     //var_dump($messages);
     foreach ($messages as $message)
     {
    
      /*$user = privmsgs_cache_getuser($message->touser);
      $paperclipimg = '<img src="'.$CFG->wwwroot.'/pix/spacer.gif" width="11px">';
      if($message->attachment)
        {
           $paperclipimg = '<img src="'.$CFG->wwwroot.'/mod/mail/paperclip.png">';
        }*/
     $table->data[] = array($message->fromuser, $message->touser, $message->subject, userdate($message->timesent),
                      "<form action=\"audit.php\"><input value=\"View Message\" type=\"Submit\">
                      <input type=\"hidden\" name=\"op\" value=\"view\">
                      <input type=\"hidden\" name=\"id\" value=\"$id\">
                      <input type=\"hidden\" name=\"mid\" value=\"$message->id\"></form>");
    
     }
    echo html_writer::table($table);
    //var_dump($table);
    //print_table($table); 
    echo $OUTPUT->footer();
  break;
  
  case 'view':

    require_login($course->id);

    add_to_log($course->id, "mail", "view", "view.php?id=$cm->id", "$mail->id");

/// Print the page header

    if ($course->category) {
        $navigation = "<A HREF=\"../../course/view.php?id=$course->id\">$course->shortname</A> ->";
    }

    $strmails = get_string("modulenameplural", "mail");
    $strmail  = get_string("modulename", "mail");

   print_header("$course->shortname: $mail->name", "$course->fullname",
                 "$navigation <A HREF=index.php?id=$course->id>$strmails</A> -> $mail->name", 
                  "", "", true, update_module_button($cm->id, $course->id, $strmail), 
                  navmenu($course, $cm));

     
     
     
     
     
     $message = $DB->get_record('mail_audit_trail',array('id'=>$mid));
     
     echo '<p><table bgcolor="#ffffff" border=1 align="center"><tr><td colspan="2">Following message was received at '.userdate($message->timesent).'</td></tr><tr>';
     echo '<td width=15%>From IP address:</td><td> '. $message->remote_host .'</td></tr><tr>';
     echo '<td>From:</td><td align="left">'. $message->fromuser .'</td></tr><tr>';
     echo '<td>To:</td><td align="left">'. $message->touser .'</td></tr><tr>';
     if($message->attachment_filename)
     {
       echo "<td>Attachment:</td><td align=\"left\"><a href=\"audit.php?op=getit&mid=$message->id&id=$id\" target=\"_blank\">$message->attachment_filename</a> </td></tr><tr>";
     }
     echo '<td colspan="2">Message:</td></tr><tr>';
     echo '<td colspan="2">'. $message->messagetext .'</td></tr><tr>';
     echo "</tr></table>";
     echo $OUTPUT->footer();
          
  
  break;
  
  case 'getit':
  
     $message = $DB->get_record('mail_audit_trail',array('id'=>$mid));
     
     Header("Content-type: $message->attachment_type");
     //Header("Content-type: application/octet-stream");
     header("Content-Disposition: attachment; filename=\"$message->attachment_filename\"");
     //header('Content-Transfer-Encoding: binary');
     header('Content-Encoding: identity');
     echo base64_decode($message->attachment_data);

  break;
  
  }

}
else
{
  notify("No good");
}


?>
