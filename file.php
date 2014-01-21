<?PHP //$Id: file.php,v 1.1 2005/05/19 20:22:11 bcarp Exp $

  require_once("../../config.php");
//    require_once("lib.php");





/// Print the main part of the page
$id=  required_param("id",PARAM_INT);

     $message = $DB->get_record('mail_privmsgs',array('id'=>$id));
     
     Header("Content-type: $message->attachment_type");
     //Header("Content-type: application/octet-stream");
     header("Content-Disposition: attachment; filename=\"$message->attachment_filename\"");
     //header('Content-Transfer-Encoding: binary');
     header('Content-Encoding: identity');
     echo base64_decode($message->attachment_data);




?>