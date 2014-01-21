<?PHP 
//$Id: mail.php,v 1.11 2007/02/14 19:07:46 bcarp Exp $
//$Log: mail.php,v $
//Revision 1.11  2007/02/14 19:07:46  bcarp
//Added ability to send messages to multiple people.
//Tabs now more readable when they are dimmed.
//Fixed check for recipients... now checks to make sure recip is not null
//Unread messages now show as blue, read messages are dimmed
//
//Revision 1.10  2005/05/19 16:00:02  bcarp
//added audit trail to messages.
//
//Revision 1.9  2005/05/05 19:15:28  bcarp
//
//Added lines from defacer's privmsg lang file.
//
//Revision 1.8  2005/05/05 19:09:25  bcarp
//
//cvs tags
//

$string['modulename'] = 'Mail';
$string['pluginname'] = 'Mail';
$string['pluginadministration'] = 'Mail Administration';
$string['modulenameplural'] = 'Mail';
$string['newmodulename'] = 'Name for Mail';
$string['fromtodate'] = 'From {$a->sender} to {$a->recipient} - {$a->date}';
$string['confirmdeletemessage'] = 'Are you sure you want to delete the message {$a}?';
$string['confirmdeletemessages'] = 'Are you sure you want to delete these {$a} messages?';
$string['inbox'] = 'Inbox';
$string['outbox'] = 'Outbox';
$string['compose'] = 'Compose';
$string['nomessages'] = 'There are no messages in this folder';
$string['confirmation'] = 'Confirmation';
$string['privatemessages'] = 'Private Messages';
$string['newmessage'] = 'New Message';
$string['recipient'] = 'To';
$string['subject'] = 'Subject';
$string['message'] = 'Message';
$string['sendmessage'] = 'Send Message';
$string['messagesent'] = 'The message has been sent successfully';
$string['viewingprivatemsg'] = 'Viewing private message';
$string['delete'] = 'Delete';
$string['deletemessage'] = 'Delete Message';
$string['sender'] = 'From';
$string['senton'] = 'Sent On';
$string['thismessage'] = 'This message';
$string['selectedmessages'] = 'Selected messages';
$string['sendprivate'] = 'Send private message';
$string['reply'] = 'Reply';
$string['re'] = 'Re: ';
$string['errornosubject'] = 'You must specify a subject';
$string['errornomessage'] = 'You must write a message';
$string['errorbadrecipient'] = 'Invalid recipient';
$string['errornorecipient'] = 'No recipient selected';
$string['nonewmessages'] = 'You have no new messages';
$string['xnewmessages'] = 'You have <strong>{$a}</strong> new messages';
$string['onenewmessage'] = 'You have <strong>1</strong> new message';
$string['attachment'] = 'Attach File:<br>(optional)';

?>

