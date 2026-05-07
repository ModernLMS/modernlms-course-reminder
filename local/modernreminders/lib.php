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
 * Library functions for local_modernreminders.
 *
 * @package    local_modernreminders
 * @copyright  2026 ModernLMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Extend course navigation to add the ModernLMS Course Reminders link.
 *
 * This hook adds a navigation item to the course secondary navigation (More menu).
 *
 * @param navigation_node $parentnode The parent navigation node.
 * @param stdClass $course The course object.
 * @param context_course $context The course context.
 */
function local_modernreminders_extend_navigation_course(navigation_node $parentnode, stdClass $course, context_course $context) {
    if (has_capability('local/modernreminders:view', $context)) {
        $url = new moodle_url('/local/modernreminders/index.php', ['id' => $course->id]);
        $parentnode->add(
            get_string('pluginname', 'local_modernreminders'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'modernreminders',
            new pix_icon('i/email', '')
        );
    }
}
