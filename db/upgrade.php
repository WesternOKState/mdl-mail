<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * This file keeps track of upgrades to the newmodule module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod
 * @subpackage newmodule
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute newmodule upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_mail_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    // And upgrade begins here. For each one, you'll need one
    // block of code similar to the next one. Please, delete
    // this comment lines once this file start handling proper
    // upgrade code.

    // if ($oldversion < YYYYMMDD00) { //New version in version.php
    //
    // }
    
        if ($oldversion < 2012052900) {

        // Changing type of field attachment_type on table mail_privmsgs to text
        $table = new xmldb_table('mail_privmsgs');
        $field = new xmldb_field('attachment_type', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, 'attachment_filename');

        // Launch change of type for field attachment_type
        $dbman->change_field_type($table, $field);

        // mail savepoint reached
        upgrade_mod_savepoint(true, 2012052900, 'mail');
    }

    
        if ($oldversion < 2012053000) {

        // Changing type of field attachment_type on table mail_privmsgs to char
        $table = new xmldb_table('mail_privmsgs');
        $field = new xmldb_field('attachment_type', XMLDB_TYPE_CHAR, '600', null, XMLDB_NOTNULL, null, '0', 'attachment_filename');

        // Launch change of type for field attachment_type
        $dbman->change_field_type($table, $field);

        // mail savepoint reached
        upgrade_mod_savepoint(true, 2012053000, 'mail');
    }
    if ($oldversion < 2012053001) {

        // Changing type of field attachment_type on table mail_privmsgs to char
        $table = new xmldb_table('mail_privmsgs');
        $field = new xmldb_field('attachment_type', XMLDB_TYPE_CHAR, '600', null, XMLDB_NOTNULL, null, '0', 'attachment_filename');

        // Launch change of type for field attachment_type
        $dbman->change_field_type($table, $field);
        $table = new xmldb_table('mail_audit_trail');
        $field = new xmldb_field('attachment_type', XMLDB_TYPE_CHAR, '600', null, XMLDB_NOTNULL, null, '0', 'attachment_filename');

        // Launch change of type for field attachment_type
        $dbman->change_field_type($table, $field);

        // mail savepoint reached
        upgrade_mod_savepoint(true, 2012053001, 'mail');
    }
}
?>

