<?PHP

function mail_upgrade($oldversion) {
/// This function does anything necessary to upgrade 
/// older versions to match current functionality 

    global $CFG;

    if ($oldversion < 2003092800) {

       # Do something ...

    }

    return true;
}

?>
