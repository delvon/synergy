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
 * Display list of users on the site
 *
 * @package    local
 * @subpackage completion_status
 * @copyright  2019 Onwards Delvon Forrester
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/completion_status/index.php');
$PAGE->set_title("Completion status Report");
$PAGE->set_heading("List of Site Users");
echo $OUTPUT->header();
global $DB;
require_login();
if (!is_siteadmin()) {
    print_error('nopermission', 'local_completion_status');
    die();
}
//Retriece the users from DB and create tble
if ($users = $DB->get_records('user', array('deleted' => 0), 'id,firstname,lastname,email')) {
    $table = new html_table();
    $table->width = "95%";
    $table->head = array('Firstname', 'Lastname', 'Email');
    foreach ($users as $id => $u) {
        $url = new moodle_url('/local/completion_status/view.php', array('id' => $u->id));
        $table->data[] = array(
            '<a href="' . $url . '">' . $u->firstname . '</a>',
            '<a href="' . $url . '">' . $u->lastname . '</a>',
            $u->email,
        );
    }
    //Add a nice Moodle box to put the table in
    echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
    echo html_writer::table($table);
    echo $OUTPUT->box_end();
} else {
    //In case no active users on the site then notify. Although it's a user on the page now
    echo $OUTPUT->notification(get_string('nousers', 'local_completion_status'));
}
echo $OUTPUT->footer();