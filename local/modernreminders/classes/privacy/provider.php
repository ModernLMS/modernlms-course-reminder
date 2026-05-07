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

namespace local_modernreminders\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for local_modernreminders.
 *
 * @package    local_modernreminders
 * @copyright  2026 ModernLMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Describe the type of data stored by this plugin.
     *
     * @param collection $collection The privacy metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_modernreminders_log',
            [
                'userid' => 'privacy:metadata:local_modernreminders_log:userid',
                'courseid' => 'privacy:metadata:local_modernreminders_log:courseid',
                'enrolmenttime' => 'privacy:metadata:local_modernreminders_log:enrolmenttime',
                'remindersenttime' => 'privacy:metadata:local_modernreminders_log:remindersenttime',
                'status' => 'privacy:metadata:local_modernreminders_log:status',
                'errormessage' => 'privacy:metadata:local_modernreminders_log:errormessage',
                'timecreated' => 'privacy:metadata:local_modernreminders_log:timecreated',
            ],
            'privacy:metadata:local_modernreminders_log'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user data.
     *
     * @param int $userid The user ID.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {local_modernreminders_log} lml ON lml.courseid = ctx.instanceid
                 WHERE ctx.contextlevel = :contextlevel
                   AND lml.userid = :userid";

        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $userid,
        ]);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist to populate.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();

        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }

        $sql = "SELECT lml.userid
                  FROM {local_modernreminders_log} lml
                 WHERE lml.courseid = :courseid";

        $userlist->add_from_sql('userid', $sql, ['courseid' => $context->instanceid]);
    }

    /**
     * Export all user data for the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_COURSE) {
                continue;
            }

            $records = $DB->get_records('local_modernreminders_log', [
                'courseid' => $context->instanceid,
                'userid' => $userid,
            ]);

            if (empty($records)) {
                continue;
            }

            $data = [];
            foreach ($records as $record) {
                $data[] = (object)[
                    'courseid' => $record->courseid,
                    'enrolmenttime' => \core_privacy\local\request\transform::datetime($record->enrolmenttime),
                    'remindersenttime' => \core_privacy\local\request\transform::datetime($record->remindersenttime),
                    'status' => $record->status,
                    'errormessage' => $record->errormessage,
                    'timecreated' => \core_privacy\local\request\transform::datetime($record->timecreated),
                ];
            }

            writer::with_context($context)->export_data(
                [get_string('pluginname', 'local_modernreminders')],
                (object)['reminders' => $data]
            );
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The context.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }

        $DB->delete_records('local_modernreminders_log', ['courseid' => $context->instanceid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_COURSE) {
                continue;
            }

            $DB->delete_records('local_modernreminders_log', [
                'courseid' => $context->instanceid,
                'userid' => $userid,
            ]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user list.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }

        $userids = $userlist->get_userids();

        if (empty($userids)) {
            return;
        }

        [$insql, $inparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $inparams['courseid'] = $context->instanceid;

        $DB->delete_records_select(
            'local_modernreminders_log',
            "courseid = :courseid AND userid {$insql}",
            $inparams
        );
    }
}
