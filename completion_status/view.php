<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Display course completion status for user
 *
 * @package    local
 * @subpackage completion_status
 * @copyright  2019 Onwards Delvon Forrester
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$userid = required_param('id', PARAM_INT);

$user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
require_login();
if (!is_siteadmin()) {
    print_error('nopermission', 'local_completion_status');
    die();
}
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/completion_status/view.php', array('id' => $user->id));
$PAGE->set_title("Completion status Report");
$PAGE->set_heading("Completion status Report");
echo $OUTPUT->header();

// Get all courses the user is enrolled in and their completion status.
$sql = "SELECT DISTINCT c.id AS id, c.fullname, cc.timecompleted
        FROM {course} c
        INNER JOIN {context} con ON con.instanceid = c.id
        INNER JOIN {role_assignments} ra ON ra.contextid = con.id
        INNER JOIN {enrol} e ON c.id = e.courseid
        INNER JOIN {user_enrolments} ue
        ON e.id = ue.enrolid AND ra.userid = ue.userid
        AND ra.userid = {$user->id}
        INNER JOIN {course_completions} cc ON cc.course = c.id
        AND cc.userid = {$user->id}";

// Get roles that are tracked by course completion.
if ($roles = $CFG->gradebookroles) {
    $sql .= ' AND ra.roleid IN (' . $roles . ')';
}

$sql .= ' WHERE con.contextlevel = ' . CONTEXT_COURSE . '
          AND c.enablecompletion = 1
          ORDER BY c.fullname ASC';

// Check if result is empty.
if ($results = $DB->get_records_sql($sql)) {
    $table = new html_table();
    $table->width = "95%";
    $table->head = array('Course name', 'Completion status', 'Time of completion');
    foreach ($results as $k => $c) {
        $status = $c->timecompleted ? 'complete' : 'not complete';
        $userdate = $c->timecompleted ? userdate($c->timecompleted) : '';
        $url = new moodle_url('/course/view.php', array('id' => $c->id));
        $table->data[] = array(
            '<a href="' . $url . '">' . $c->fullname . '</a>',
            $status,
            $userdate
        );
    }
    //Add a nice Moodle box to put the table in
    echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
    echo html_writer::table($table);
    echo $OUTPUT->box_end();
} else {
    //If no course even tracking completion then notify nothing to show
    echo $OUTPUT->notification(get_string('nothingtoshow', 'local_completion_status'));
}
echo $OUTPUT->footer();
