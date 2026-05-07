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
 * Language strings for local_modernreminders.
 *
 * @package    local_modernreminders
 * @copyright  2026 ModernLMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'ModernLMS Course Reminders';
$string['modernreminders:manage'] = 'Manage course reminder settings';
$string['modernreminders:view'] = 'View course reminder settings';

// Form fields.
$string['enabled'] = 'Activate Email Reminders';
$string['enabled_help'] = 'Enable or disable reminder emails for this course.';
$string['reminderdays'] = 'Reminder Days After Enrollment';
$string['reminderdays_help'] = 'Within how many days should the email reminder be sent to users after enrollment if the course is not completed?';
$string['reminderdays_unit'] = 'Days';
$string['emailsubject'] = 'Email Subject';
$string['emailsubject_help'] = 'The subject line for the reminder email. You can use placeholders: {firstname}, {lastname}, {fullname}, {coursename}, {courseurl}, {enrolmentdate}, {daysafterenrolment}, {sitename}.';
$string['emailtemplate'] = 'Email Template';
$string['emailtemplate_help'] = 'The body of the reminder email. You can use the following placeholders: {firstname}, {lastname}, {fullname}, {coursename}, {courseurl}, {enrolmentdate}, {daysafterenrolment}, {sitename}.';

// Form buttons and actions.
$string['savesettings'] = 'Save settings';
$string['sendtestemail'] = 'Send Test Email';
$string['sendtestemail_help'] = 'Send a test reminder email to yourself using the current template.';

// Messages.
$string['settingssaved'] = 'Reminder settings saved successfully.';
$string['testemailsent'] = 'Test email sent to {$a}.';
$string['testemailfailed'] = 'Failed to send test email. Please check your email configuration.';
$string['reminderdaysrequired'] = 'Reminder days is required when reminders are activated.';
$string['reminderdayspositive'] = 'Reminder days must be a positive number (minimum 1).';
$string['emailsubjectrequired'] = 'Email subject is required when reminders are activated.';
$string['emailtemplaterequired'] = 'Email template is required when reminders are activated.';

// Scheduled task.
$string['taskname'] = 'Send ModernLMS course reminder emails';

// Default template.
$string['defaultsubject'] = 'Reminder: Complete your course - {coursename}';
$string['defaulttemplate'] = 'Hello {firstname},

This is a friendly reminder to continue and complete the course: {coursename}.

You were enrolled on {enrolmentdate}, and the course is still not marked as completed.

You may access the course here:
{courseurl}

Thank you.

ModernLMS';

// Log and status.
$string['remindersentto'] = 'Reminder sent to {$a}';
$string['reminderfailedfor'] = 'Failed to send reminder to {$a}';
$string['completionnotenabled'] = 'Course completion is not enabled for course {$a}. Skipping reminders.';
$string['noremindercourses'] = 'No courses with active reminders found.';
$string['processingcourse'] = 'Processing reminders for course: {$a}';
$string['alreadysent'] = 'Reminder already sent to user {$a->userid} for course {$a->courseid}. Skipping.';
$string['coursenotcompleted'] = 'User {$a->userid} has not completed course {$a->courseid} after {$a->days} days. Sending reminder.';
$string['coursecompleted'] = 'User {$a->userid} has completed course {$a->courseid}. Skipping.';
$string['notyetdue'] = 'User {$a->userid} enrolled {$a->elapsed} days ago, reminder threshold is {$a->days} days. Not yet due.';

// Page titles.
$string['pagetitle'] = 'Course Reminders';
$string['pageheading'] = 'ModernLMS Course Reminders';

// Privacy.
$string['privacy:metadata:local_modernreminders_log'] = 'Log of reminder emails sent to users.';
$string['privacy:metadata:local_modernreminders_log:userid'] = 'The ID of the user who received the reminder.';
$string['privacy:metadata:local_modernreminders_log:courseid'] = 'The course for which the reminder was sent.';
$string['privacy:metadata:local_modernreminders_log:enrolmenttime'] = 'The time the user was enrolled in the course.';
$string['privacy:metadata:local_modernreminders_log:remindersenttime'] = 'The time the reminder email was sent.';
$string['privacy:metadata:local_modernreminders_log:status'] = 'Whether the reminder was sent successfully or failed.';
$string['privacy:metadata:local_modernreminders_log:errormessage'] = 'The error message if the reminder failed to send.';
$string['privacy:metadata:local_modernreminders_log:timecreated'] = 'The time the log record was created.';

// Placeholders info.
$string['placeholdersinfo'] = 'Available placeholders';
$string['placeholderslist'] = '{firstname} - User first name, {lastname} - User last name, {fullname} - User full name, {coursename} - Course name, {courseurl} - Course URL, {enrolmentdate} - Enrolment date, {daysafterenrolment} - Number of days configured, {sitename} - Site name';
