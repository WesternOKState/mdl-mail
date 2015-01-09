<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
defined('MOODLE_INTERNAL') || die;

    /**
     * Constructor
     * @param string $name unique ascii name, either 'mysetting' for settings that in config, or 'myplugin/mysetting' for ones in config_plugins.
     * @param string $visiblename localised
     * @param string $description long localised info
     * @param string|int $defaultsetting
     * @param array $choices array of $value=>$label for each selection
     */

$settings->add( new admin_setting_configselect( 'mail/MailisDeletable', get_string( 'MailisDeletable', 'mail' ), get_string( 'MailisDeletableDescription', 'mail'),"no",array("no"=>"no","yes"=>"yes")));


?>
