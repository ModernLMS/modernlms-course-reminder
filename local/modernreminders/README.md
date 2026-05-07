# ModernLMS Course Reminders (local_modernreminders)

A Moodle 5+ local plugin that allows course managers and teachers to configure automated email reminders for learners who have enrolled in a course but have not completed it after a defined number of days.

## Features

- Course-level reminder configuration (each course has its own settings)
- Customizable email subject and body templates with placeholder support
- Scheduled task that runs daily to send reminders automatically
- Duplicate prevention via a reminder log table
- Send test email functionality for course managers
- Privacy API implementation for GDPR compliance
- Course navigation integration (appears under course More menu)

## Requirements

- Moodle 5.0+ (version 2024100700 or later)
- Course completion tracking enabled for target courses

## Installation

1. Copy the `local/modernreminders` directory into your Moodle installation's `local/` directory:
   ```bash
   cp -r local/modernreminders /path/to/moodle/local/modernreminders
   ```

2. Log in to your Moodle site as an administrator.

3. Navigate to **Site administration > Notifications** to trigger the plugin installation.

4. The plugin will create the required database tables automatically.

## How to Enable Course Reminders

1. Navigate to a course.
2. Click the **More** menu in the course secondary navigation.
3. Click **ModernLMS Course Reminders**.
4. Check **Activate Email Reminders**.
5. Set the number of days after enrollment to send reminders.
6. Customize the email subject and template as needed.
7. Click **Save settings**.

## Email Template Placeholders

The following placeholders can be used in both the email subject and body:

| Placeholder | Description |
|---|---|
| `{firstname}` | User's first name |
| `{lastname}` | User's last name |
| `{fullname}` | User's full name |
| `{coursename}` | Course full name |
| `{courseurl}` | Direct URL to the course |
| `{enrolmentdate}` | Date the user was enrolled |
| `{daysafterenrolment}` | Configured number of reminder days |
| `{sitename}` | Site name |

## Running the Scheduled Task Manually

From the Moodle CLI:

```bash
php admin/cli/scheduled_task.php --execute='\local_modernreminders\task\send_reminders'
```

Or via the admin interface:
1. Go to **Site administration > Server > Scheduled tasks**.
2. Find **Send ModernLMS course reminder emails**.
3. Click **Run now**.

## Capabilities

| Capability | Description | Default Roles |
|---|---|---|
| `local/modernreminders:manage` | Manage course reminder settings | Manager, Course creator, Editing teacher |
| `local/modernreminders:view` | View course reminder settings | Manager, Course creator, Editing teacher, Teacher |

## Plugin Structure

```
local/modernreminders/
├── version.php                          # Plugin version info
├── lib.php                              # Navigation hook
├── index.php                            # Course settings page
├── db/
│   ├── install.xml                      # Database schema (XMLDB)
│   ├── access.php                       # Capability definitions
│   ├── tasks.php                        # Scheduled task definitions
│   └── upgrade.php                      # Upgrade steps
├── lang/en/
│   └── local_modernreminders.php        # Language strings
├── classes/
│   ├── form/
│   │   └── course_settings_form.php     # Moodle form for settings
│   ├── task/
│   │   └── send_reminders.php           # Scheduled task
│   ├── privacy/
│   │   └── provider.php                 # Privacy API provider
│   ├── manager.php                      # Business logic manager
│   └── template.php                     # Email template renderer
└── README.md
```

## Testing Checklist

- [ ] Install the plugin via Site administration > Notifications
- [ ] Navigate to a course and verify "ModernLMS Course Reminders" appears in the More menu
- [ ] Open the settings page and verify the form loads correctly
- [ ] Enable reminders and set Reminder Days to 1
- [ ] Customize the email subject and template
- [ ] Click "Send Test Email" and verify you receive an email
- [ ] Enrol a test learner in the course
- [ ] Enable course completion tracking for the course
- [ ] Run the scheduled task manually: `php admin/cli/scheduled_task.php --execute='\local_modernreminders\task\send_reminders'`
- [ ] Verify the reminder email is sent to the learner
- [ ] Run the task again and verify no duplicate email is sent
- [ ] Mark the learner as course complete
- [ ] Run the task again and verify no reminder is sent
- [ ] Disable reminders and verify no emails are sent on next task run
- [ ] Test with a course that has completion tracking disabled — verify it is skipped gracefully
- [ ] Verify view-only access for the Teacher role (no edit form, just a read-only display)

## Assumptions

1. **Course completion must be enabled**: The plugin requires Moodle course completion tracking to be enabled for each course where reminders are configured. If completion tracking is disabled, the course is skipped during cron processing.

2. **One reminder per user per course**: Each user receives at most one reminder email per course enrolment. The log table prevents duplicates.

3. **Enrolment time source**: The plugin uses `user_enrolments.timestart` as the primary enrolment time, falling back to `user_enrolments.timecreated` if `timestart` is 0.

4. **Email delivery**: The plugin relies on Moodle's `email_to_user()` function and the site's configured email delivery system (SMTP, etc.).

5. **Daily task execution**: The scheduled task is configured to run once daily at 6:00 AM server time by default. This can be adjusted in Site administration > Server > Scheduled tasks.

## License

GNU GPL v3 or later — http://www.gnu.org/copyleft/gpl.html
