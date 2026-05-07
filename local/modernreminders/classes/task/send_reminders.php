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

namespace local_modernreminders\task;

use local_modernreminders\manager;
use local_modernreminders\template;

/**
 * Scheduled task to send course reminder emails.
 *
 * @package    local_modernreminders
 * @copyright  2026 ModernLMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_reminders extends \core\task\scheduled_task {

    /**
     * Get the task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('taskname', 'local_modernreminders');
    }

    /**
     * Execute the scheduled task.
     */
    public function execute(): void {
        $enabledcourses = manager::get_enabled_courses();

        if (empty($enabledcourses)) {
            mtrace(get_string('noremindercourses', 'local_modernreminders'));
            return;
        }

        $templatehelper = new template();
        $noreplyuser = \core_user::get_noreply_user();

        foreach ($enabledcourses as $settings) {
            $this->process_course($settings, $templatehelper, $noreplyuser);
        }
    }

    /**
     * Process a single course for reminders.
     *
     * @param \stdClass $settings The reminder settings for the course.
     * @param template $templatehelper The template rendering helper.
     * @param \stdClass $noreplyuser The noreply user for sending emails.
     */
    private function process_course(\stdClass $settings, template $templatehelper, \stdClass $noreplyuser): void {
        $courseid = $settings->courseid;

        // Check if the course still exists.
        try {
            $course = get_course($courseid);
        } catch (\Exception $e) {
            mtrace("  Course {$courseid} not found. Skipping.");
            return;
        }

        mtrace(get_string('processingcourse', 'local_modernreminders', format_string($course->fullname)));

        // Check if completion tracking is enabled.
        if (!manager::is_completion_enabled($courseid)) {
            mtrace('  ' . get_string('completionnotenabled', 'local_modernreminders', format_string($course->fullname)));
            return;
        }

        // Get enrolled users.
        $users = manager::get_enrolled_users($courseid);

        if (empty($users)) {
            mtrace("  No enrolled users found.");
            return;
        }

        $now = time();
        $reminderdays = $settings->reminderdays;
        $thresholdseconds = $reminderdays * DAYSECS;

        foreach ($users as $user) {
            $this->process_user($user, $course, $settings, $templatehelper, $noreplyuser, $now, $thresholdseconds);
        }
    }

    /**
     * Process a single user for a course reminder.
     *
     * @param \stdClass $user The user object.
     * @param \stdClass $course The course object.
     * @param \stdClass $settings The reminder settings.
     * @param template $templatehelper The template rendering helper.
     * @param \stdClass $noreplyuser The noreply user.
     * @param int $now The current timestamp.
     * @param int $thresholdseconds The reminder threshold in seconds.
     */
    private function process_user(\stdClass $user, \stdClass $course, \stdClass $settings,
            template $templatehelper, \stdClass $noreplyuser, int $now, int $thresholdseconds): void {
        $courseid = $course->id;
        $userid = $user->id;

        // Check if reminder was already sent.
        if (manager::is_reminder_sent($courseid, $userid)) {
            mtrace('  ' . get_string('alreadysent', 'local_modernreminders', (object)[
                'userid' => $userid,
                'courseid' => $courseid,
            ]));
            return;
        }

        // Get enrolment time.
        $enrolmenttime = manager::get_enrolment_time($courseid, $userid);
        if ($enrolmenttime <= 0) {
            return;
        }

        // Check if the reminder threshold has been reached.
        $elapsed = $now - $enrolmenttime;
        $elapseddays = floor($elapsed / DAYSECS);

        if ($elapsed < $thresholdseconds) {
            mtrace('  ' . get_string('notyetdue', 'local_modernreminders', (object)[
                'userid' => $userid,
                'elapsed' => $elapseddays,
                'days' => $settings->reminderdays,
            ]));
            return;
        }

        // Check course completion.
        if (manager::is_course_completed($courseid, $userid)) {
            mtrace('  ' . get_string('coursecompleted', 'local_modernreminders', (object)[
                'userid' => $userid,
                'courseid' => $courseid,
            ]));
            return;
        }

        // Send the reminder email.
        $subject = $templatehelper->render_template(
            $settings->emailsubject, $user, $course, $enrolmenttime, $settings->reminderdays
        );
        $body = $templatehelper->render_template(
            $settings->emailtemplate, $user, $course, $enrolmenttime, $settings->reminderdays
        );
        $htmlbody = nl2br(s($body));

        try {
            $result = email_to_user($user, $noreplyuser, $subject, $body, $htmlbody);

            if ($result) {
                manager::log_reminder($courseid, $userid, $enrolmenttime, 'sent');
                mtrace('  ' . get_string('remindersentto', 'local_modernreminders', fullname($user)));
            } else {
                manager::log_reminder($courseid, $userid, $enrolmenttime, 'failed', 'email_to_user returned false');
                mtrace('  ' . get_string('reminderfailedfor', 'local_modernreminders', fullname($user)));
            }
        } catch (\Exception $e) {
            manager::log_reminder($courseid, $userid, $enrolmenttime, 'failed', $e->getMessage());
            mtrace('  ' . get_string('reminderfailedfor', 'local_modernreminders', fullname($user))
                . ': ' . $e->getMessage());
        }
    }
}
