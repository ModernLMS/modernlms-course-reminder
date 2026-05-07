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

namespace local_modernreminders;

/**
 * Manager class for course reminder operations.
 *
 * @package    local_modernreminders
 * @copyright  2026 ModernLMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /**
     * Get reminder settings for a course.
     *
     * @param int $courseid The course ID.
     * @return \stdClass|false The settings record or false if not found.
     */
    public static function get_settings(int $courseid) {
        global $DB;
        return $DB->get_record('local_modernreminders', ['courseid' => $courseid]);
    }

    /**
     * Save reminder settings for a course.
     *
     * @param \stdClass $data The form data.
     * @return int The record ID.
     */
    public static function save_settings(\stdClass $data): int {
        global $DB, $USER;

        $now = time();
        $existing = self::get_settings($data->id);

        $record = new \stdClass();
        $record->courseid = $data->id;
        $record->enabled = !empty($data->enabled) ? 1 : 0;
        $record->reminderdays = (int)($data->reminderdays ?? 7);
        $record->emailsubject = $data->emailsubject ?? get_string('defaultsubject', 'local_modernreminders');
        $record->emailtemplate = $data->emailtemplate ?? get_string('defaulttemplate', 'local_modernreminders');
        $record->emailtemplateformat = FORMAT_PLAIN;
        $record->timemodified = $now;
        $record->usermodified = $USER->id;

        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record('local_modernreminders', $record);
            return $record->id;
        }

        $record->timecreated = $now;
        return $DB->insert_record('local_modernreminders', $record);
    }

    /**
     * Get all courses with reminders enabled.
     *
     * @return array Array of reminder settings records with course data.
     */
    public static function get_enabled_courses(): array {
        global $DB;
        return $DB->get_records('local_modernreminders', ['enabled' => 1]);
    }

    /**
     * Check if a reminder has already been sent to a user for a specific course enrolment.
     *
     * @param int $courseid The course ID.
     * @param int $userid The user ID.
     * @return bool True if already sent.
     */
    public static function is_reminder_sent(int $courseid, int $userid): bool {
        global $DB;
        return $DB->record_exists('local_modernreminders_log', [
            'courseid' => $courseid,
            'userid' => $userid,
            'status' => 'sent',
        ]);
    }

    /**
     * Log a reminder email that was sent or failed.
     *
     * @param int $courseid The course ID.
     * @param int $userid The user ID.
     * @param int $enrolmenttime The enrolment timestamp.
     * @param string $status The status ('sent' or 'failed').
     * @param string|null $errormessage Optional error message.
     */
    public static function log_reminder(int $courseid, int $userid, int $enrolmenttime,
            string $status, ?string $errormessage = null): void {
        global $DB;

        $record = new \stdClass();
        $record->courseid = $courseid;
        $record->userid = $userid;
        $record->enrolmenttime = $enrolmenttime;
        $record->remindersenttime = time();
        $record->status = $status;
        $record->errormessage = $errormessage;
        $record->timecreated = time();

        $DB->insert_record('local_modernreminders_log', $record);
    }

    /**
     * Get the earliest enrolment time for a user in a course.
     *
     * @param int $courseid The course ID.
     * @param int $userid The user ID.
     * @return int The enrolment timestamp, or 0 if not found.
     */
    public static function get_enrolment_time(int $courseid, int $userid): int {
        global $DB;

        $sql = "SELECT MIN(ue.timestart) AS enroltime
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE e.courseid = :courseid
                   AND ue.userid = :userid
                   AND ue.status = :active";

        $params = [
            'courseid' => $courseid,
            'userid' => $userid,
            'active' => ENROL_USER_ACTIVE,
        ];

        $result = $DB->get_record_sql($sql, $params);

        if ($result && $result->enroltime > 0) {
            return (int)$result->enroltime;
        }

        // Fallback: use timecreated if timestart is 0.
        $sql = "SELECT MIN(ue.timecreated) AS enroltime
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE e.courseid = :courseid
                   AND ue.userid = :userid
                   AND ue.status = :active";

        $result = $DB->get_record_sql($sql, $params);

        return ($result && $result->enroltime) ? (int)$result->enroltime : 0;
    }

    /**
     * Get enrolled users in a course who are active.
     *
     * @param int $courseid The course ID.
     * @return array Array of user objects.
     */
    public static function get_enrolled_users(int $courseid): array {
        $context = \context_course::instance($courseid);
        return get_enrolled_users($context, '', 0, 'u.*', null, 0, 0, true);
    }

    /**
     * Check if a user has completed a course.
     *
     * @param int $courseid The course ID.
     * @param int $userid The user ID.
     * @return bool True if the course is completed.
     */
    public static function is_course_completed(int $courseid, int $userid): bool {
        $completion = new \completion_info(\get_course($courseid));
        return $completion->is_course_complete($userid);
    }

    /**
     * Check if course completion is enabled for a course.
     *
     * @param int $courseid The course ID.
     * @return bool True if completion tracking is enabled.
     */
    public static function is_completion_enabled(int $courseid): bool {
        $course = get_course($courseid);
        $completion = new \completion_info($course);
        return $completion->is_enabled();
    }

    /**
     * Send a test email to the current user.
     *
     * @param int $courseid The course ID.
     * @param string $subject The email subject template.
     * @param string $body The email body template.
     * @return bool True if the email was sent successfully.
     */
    public static function send_test_email(int $courseid, string $subject, string $body): bool {
        global $USER;

        $course = get_course($courseid);
        $templatehelper = new template();
        $renderedsubject = $templatehelper->render_template($subject, $USER, $course, time(), 0);
        $renderedbody = $templatehelper->render_template($body, $USER, $course, time(), 0);

        $noreplyuser = \core_user::get_noreply_user();

        return email_to_user(
            $USER,
            $noreplyuser,
            $renderedsubject,
            $renderedbody,
            nl2br($renderedbody)
        );
    }
}
