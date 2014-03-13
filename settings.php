<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
defined('MOODLE_INTERNAL') || die;

$settings->add( new admin_setting_configtext( 'MailisDeletable', get_string( 'MailisDeletable', 'mail' ), get_string( 'MailisDeletableDescription', 'mail'),0));


?>
