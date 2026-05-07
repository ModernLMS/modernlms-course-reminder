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

namespace local_modernreminders\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Course reminder settings form.
 *
 * @package    local_modernreminders
 * @copyright  2026 ModernLMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_settings_form extends \moodleform {

    /**
     * Define the form elements.
     */
    protected function definition() {
        $mform = $this->_form;

        // Course ID (hidden).
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Activate Email Reminders.
        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_modernreminders'));
        $mform->addHelpButton('enabled', 'enabled', 'local_modernreminders');
        $mform->setType('enabled', PARAM_BOOL);

        // Reminder Days After Enrollment.
        $mform->addElement(
            'text',
            'reminderdays',
            get_string('reminderdays', 'local_modernreminders')
        );
        $mform->addHelpButton('reminderdays', 'reminderdays', 'local_modernreminders');
        $mform->setType('reminderdays', PARAM_INT);
        $mform->setDefault('reminderdays', 7);
        $mform->disabledIf('reminderdays', 'enabled', 'notchecked');

        // Email Subject.
        $mform->addElement('text', 'emailsubject', get_string('emailsubject', 'local_modernreminders'), ['size' => 80]);
        $mform->addHelpButton('emailsubject', 'emailsubject', 'local_modernreminders');
        $mform->setType('emailsubject', PARAM_TEXT);
        $mform->setDefault('emailsubject', get_string('defaultsubject', 'local_modernreminders'));
        $mform->disabledIf('emailsubject', 'enabled', 'notchecked');

        // Email Template.
        $mform->addElement(
            'textarea',
            'emailtemplate',
            get_string('emailtemplate', 'local_modernreminders'),
            ['rows' => 15, 'cols' => 80]
        );
        $mform->addHelpButton('emailtemplate', 'emailtemplate', 'local_modernreminders');
        $mform->setType('emailtemplate', PARAM_RAW);
        $mform->setDefault('emailtemplate', get_string('defaulttemplate', 'local_modernreminders'));
        $mform->disabledIf('emailtemplate', 'enabled', 'notchecked');

        // Placeholders info.
        $mform->addElement(
            'static',
            'placeholdersinfo',
            get_string('placeholdersinfo', 'local_modernreminders'),
            get_string('placeholderslist', 'local_modernreminders')
        );

        // Action buttons.
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savesettings', 'local_modernreminders'));

        // Send Test Email button (only for users with manage capability).
        if (!empty($this->_customdata['canmanage'])) {
            $buttonarray[] = $mform->createElement(
                'submit',
                'sendtestemail',
                get_string('sendtestemail', 'local_modernreminders')
            );
        }

        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Validate the form data.
     *
     * @param array $data The form data.
     * @param array $files The uploaded files.
     * @return array Validation errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['enabled'])) {
            // Reminder days must be a positive integer >= 1.
            if (empty($data['reminderdays']) || $data['reminderdays'] < 1) {
                $errors['reminderdays'] = get_string('reminderdayspositive', 'local_modernreminders');
            }

            // Email subject is required.
            if (empty(trim($data['emailsubject']))) {
                $errors['emailsubject'] = get_string('emailsubjectrequired', 'local_modernreminders');
            }

            // Email template is required.
            if (empty(trim($data['emailtemplate']))) {
                $errors['emailtemplate'] = get_string('emailtemplaterequired', 'local_modernreminders');
            }
        }

        return $errors;
    }
}
