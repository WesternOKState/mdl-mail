#$Id: mysql.sql,v 1.6 2007/01/23 22:04:59 bcarp Exp $
#$Log: mysql.sql,v $
#Revision 1.6  2007/01/23 22:04:59  bcarp
#
#allot closer. messages seperated by courses now.
#
#Revision 1.5  2005/05/19 20:24:10  bcarp
#deleted extra table.
#
#Revision 1.4  2005/05/19 20:22:46  bcarp
#attachments no longer stored as files. In mysql tables now.
#
#Revision 1.3  2005/05/19 16:00:02  bcarp
#added audit trail to messages.
#
#Revision 1.2  2005/05/05 19:47:46  bcarp
#added cvs tags.
#
#moving privmsgs tables under mail module's banner.
#

# This file contains a complete database schema for all the 
# tables used by this module, written in SQL

# It may also contain INSERT statements for particular data 
# that may be used, especially new entries in the table log_display



#use moodle;

CREATE TABLE prefix_mail (
  id int(10) unsigned NOT NULL auto_increment,
  name varchar(30),  
  PRIMARY KEY  (id),
  UNIQUE KEY id (id)
) TYPE=MyISAM;

CREATE TABLE `prefix_mail_privmsgs` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `course` int(11) unsigned NOT NULL,
  `folder` int(11) NOT NULL default '0',
  `subject` varchar(255) NOT NULL default '0',
  `fromuser` int(11) NOT NULL default '0',
  `touser` int(11) NOT NULL default '0',
  `textid` int(10) unsigned NOT NULL default '0',
  `timesent` int(11) NOT NULL default '0',
  `isread` tinyint(4) NOT NULL default '0',
  `attachment_filename` varchar(255) NOT NULL default '0',
  `attachment_type` varchar(50) NOT NULL default '0',
  `attachment_data` mediumblob,
  PRIMARY KEY  (`id`),
  KEY `fromuser` (`fromuser`),
  KEY `touser` (`touser`)
) TYPE=MyISAM COMMENT='Mail messages';

CREATE TABLE `prefix_mail_text` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `format` int(4) NOT NULL default '0',
  `message` text,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Text of Mail messages';




CREATE TABLE `prefix_mail_audit_trail` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `timesent` int(11) NOT NULL default '0',
  `remote_host` varchar(17) NOT NULL default '0',
  `touser` varchar(255) NOT NULL default '0',
  `fromuser` varchar(255) NOT NULL default '0',
  `subject`  varchar(255) NOT NULL default '0',
  `messagetext` text,
  `attachment_filename` varchar(255) NOT NULL default '0',
  `attachment_type` varchar(50) NOT NULL default '0',
  `attachment_data` mediumblob,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Audit of messages';


