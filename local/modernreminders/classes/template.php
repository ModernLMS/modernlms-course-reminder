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
 * Template rendering helper for email placeholders.
 *
 * @package    local_modernreminders
 * @copyright  2026 ModernLMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class template {

    /**
     * Render an email template by replacing placeholders with actual data.
     *
     * @param string $templatetext The template text with placeholders.
     * @param \stdClass $user The user object.
     * @param \stdClass $course The course object.
     * @param int $enrolmenttime The enrolment timestamp.
     * @param int $reminderdays The configured number of days.
     * @return string The rendered text.
     */
    public function render_template(string $templatetext, \stdClass $user, \stdClass $course,
            int $enrolmenttime, int $reminderdays): string {
        global $CFG;

        $courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);
        $sitename = format_string(get_site()->fullname);

        $replacements = [
            '{firstname}' => format_string($user->firstname),
            '{lastname}' => format_string($user->lastname),
            '{fullname}' => fullname($user),
            '{coursename}' => format_string($course->fullname),
            '{courseurl}' => $courseurl->out(false),
            '{enrolmentdate}' => userdate($enrolmenttime, get_string('strftimedatefull', 'langconfig')),
            '{daysafterenrolment}' => $reminderdays,
            '{sitename}' => $sitename,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $templatetext);
    }
}
