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
$id=optional_param("id");
$op=optional_param("op", 'list');

if(isadmin())
{
  switch($op)
  {
  case 'list':
  
   if ($id) {
        if (! $cm = get_record("course_modules", "id", $id)) {
            error("Course Module ID was incorrect");
        }
    
        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }
    
        if (! $mail = get_record("mail", "id", $cm->instance)) {
            error("Course module is incorrect");
        }
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

  
  
  
  
     $messages = get_records("mail_audit_trail");
     die("REPRESSED");
     $table = &New stdClass or die("Help help");
     $table->head = array(get_string('sender','mail'), get_string('recipient', 'mail'),get_string('subject', 'mail'), get_string('senton', 'mail'),'&nbsp;') or die("Help help");
     $table->width = '100%';
     $table->size = array('20%', '20%', '20%', '25%','15%');
     $table->align = array('LEFT', 'CENTER', 'CENTER', 'CENTER');
     $table->data = array();
     var_dump($messages);
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
                      <input type=\"hidden\" name=\"id\" value=\"$message->id\"></form>");
    
     }
    var_dump($table);
    print_table($table); 
  break;
  
  case 'view':
  
     if ($id) {
        if (! $cm = get_record("course_modules", "id", $id)) {
            error("Course Module ID was incorrect");
        }
    
        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }
    
     //   if (! $mail = get_record("mail", "id", $cm->instance)) {
      //      error("Course module is incorrect");
      //  }
     }
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

     
     
     
     
     
     $message = get_record('mail_audit_trail','id',$id);
     
     echo '<p><table bgcolor="#ffffff" align="center"><tr><td colspan="2">Following message was received at '.userdate($message->timesent).'</td></tr><tr><td>';
     echo 'From IP address:</td><td> '. $message->remote_host .'</td></tr><tr>';
     echo '<td>From:</td><td align="left">'. $message->fromuser .'</td></tr><tr>';
     echo '<td>To:</td><td align="left">'. $message->touser .'</td></tr><tr>';
     if($message->attachment_filename)
     {
       echo "<td>Attachment:</td><td align=\"left\"><a href=\"audit.php?op=getit&id=$message->id\" target=\"_blank\">$message->attachment_filename</a> </td></tr><tr>";
     }
     echo '<td colspan="2">Message:</td></tr><tr>';
     echo '<td colspan="2">'. $message->messagetext .'</td></tr><tr>';
     
          
  
  break;
  
  case 'getit':
  
     $message = get_record('mail_audit_trail','id',$id);
     
     Header("Content-type: $message->attachment_type");
     header("Content-Disposition: attachment; filename=\"$message->attachment_filename\"");
     echo $message->attachment_data;
  
  break;
  
  }

}
else
{
  notify("No good");
}


?>
