<?PHP // $Id: version.php,v 1.2 2007/01/23 22:04:59 bcarp Exp $

/////////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of NEWMODULE
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$module->version  = 2014031000;  // The current module version (Date: YYYYMMDDXX)
//$module->cron     = 21600;           // Period for cron to check this module (secs)
$module->cron     = 1;           // Period for cron to check this module (secs)
?>
