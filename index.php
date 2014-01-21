<?PHP // $Id: index.php,v 1.3 2007/02/08 17:13:25 bcarp Exp $


/// This page lists all the instances of mail in a particular course
/// Replace mail with the name of your module

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);   // course

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);

add_to_log($course->id, 'mail', 'view all', 'index.php?id='.$course->id, '');

$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);

$PAGE->set_url('/mod/mail/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($coursecontext);

echo $OUTPUT->header();

if (! $mails = get_all_instances_in_course('mail', $course)) {
    notice(get_string('nomails', 'mail'), new moodle_url('/course/view.php', array('id' => $course->id)));
}
$table = new html_table();

if ($course->format == 'weeks') {
    $table->head  = array(get_string('week'), get_string('name'));
    $table->align = array('center', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array(get_string('topic'), get_string('name'));
    $table->align = array('center', 'left', 'left', 'left');
} else {
    $table->head  = array(get_string('name'));
    $table->align = array('left', 'left', 'left');
}

foreach ($mails as $mail) {
    if (!$mail->visible) {
        $link = html_writer::link(
            new moodle_url('/mod/mail/view.php', array('id' => $mail->coursemodule)),
            format_string($mail->name, true),
            array('class' => 'dimmed'));
    } else {
        $link = html_writer::link(
            new moodle_url('/mod/mail/view.php', array('id' => $mail->coursemodule)),
            format_string($mail->name, true));
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array($mail->section, $link);
    } else {
        $table->data[] = array($link);
    }
}

echo $OUTPUT->heading(get_string('modulenameplural', 'mail'), 2);
echo html_writer::table($table);
echo $OUTPUT->footer();

?>
