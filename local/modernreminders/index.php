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
 * Course-level reminder settings page.
 *
 * @package    local_modernreminders
 * @copyright  2026 ModernLMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_modernreminders\form\course_settings_form;
use local_modernreminders\manager;

$courseid = required_param('id', PARAM_INT);

// Validate course and context.
$course = get_course($courseid);
$context = context_course::instance($courseid);

require_login($course);
require_capability('local/modernreminders:view', $context);

$canmanage = has_capability('local/modernreminders:manage', $context);

// Page setup.
$pageurl = new moodle_url('/local/modernreminders/index.php', ['id' => $courseid]);
$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('pagetitle', 'local_modernreminders'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_secondary_active_tab('modernreminders');

// Load existing settings.
$settings = manager::get_settings($courseid);

// Prepare form data.
$formdata = new stdClass();
$formdata->id = $courseid;

if ($settings) {
    $formdata->enabled = $settings->enabled;
    $formdata->reminderdays = $settings->reminderdays;
    $formdata->emailfrequency = $settings->emailfrequency ?? 'daily';
    $formdata->emailsubject = $settings->emailsubject;
    $formdata->emailtemplate = $settings->emailtemplate;
}

// Create the form.
$form = new course_settings_form($pageurl, ['canmanage' => $canmanage]);
$form->set_data($formdata);

// Handle form submission.
if ($form->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
}

if ($data = $form->get_data()) {
    require_capability('local/modernreminders:manage', $context);
    require_sesskey();

    // Check if the test email button was pressed.
    if (!empty($data->sendtestemail)) {
        // Save settings first.
        manager::save_settings($data);

        // Send test email.
        $result = manager::send_test_email($courseid, $data->emailsubject, $data->emailtemplate);
        if ($result) {
            $message = get_string('testemailsent', 'local_modernreminders', $USER->email);
            $messagetype = \core\output\notification::NOTIFY_SUCCESS;
        } else {
            $message = get_string('testemailfailed', 'local_modernreminders');
            $messagetype = \core\output\notification::NOTIFY_ERROR;
        }
        redirect($pageurl, $message, null, $messagetype);
    }

    // Save settings.
    manager::save_settings($data);
    redirect($pageurl, get_string('settingssaved', 'local_modernreminders'), null,
        \core\output\notification::NOTIFY_SUCCESS);
}

// Output.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pageheading', 'local_modernreminders'));

if ($canmanage) {
    $form->display();
} else {
    // View-only: display settings as read-only information.
    if ($settings) {
        $table = new html_table();
        $table->attributes['class'] = 'generaltable';
        $table->data[] = [
            get_string('enabled', 'local_modernreminders'),
            $settings->enabled ? get_string('yes') : get_string('no'),
        ];
        $table->data[] = [
            get_string('reminderdays', 'local_modernreminders'),
            $settings->reminderdays . ' ' . get_string('reminderdays_unit', 'local_modernreminders'),
        ];
        $frequencykey = 'frequency_' . ($settings->emailfrequency ?? 'daily');
        $table->data[] = [
            get_string('emailfrequency', 'local_modernreminders'),
            get_string($frequencykey, 'local_modernreminders'),
        ];
        $table->data[] = [
            get_string('emailsubject', 'local_modernreminders'),
            format_string($settings->emailsubject),
        ];
        $table->data[] = [
            get_string('emailtemplate', 'local_modernreminders'),
            nl2br(format_text($settings->emailtemplate, FORMAT_PLAIN)),
        ];
        echo html_writer::table($table);
    } else {
        echo $OUTPUT->notification(get_string('noremindercourses', 'local_modernreminders'), 'info');
    }
}

echo $OUTPUT->footer();
