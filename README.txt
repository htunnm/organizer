=== Organizer ===
Contributors: organizer-team
Tags: event, rsvp, waitlist, training
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later

Event management made simple: registrations, reminders, and waitlists.

== Description ==

Organizer allows site owners to manage events and trainings without writing code.

**Features:**
* **Event Series:** Create recurring events (weekly/monthly) with a schedule builder.
* **Session Management:** Manage individual sessions within a series.
* **Registrations:** Accept registrations for specific sessions.
* **Waitlists:** Automatic waitlist management with promotion logic.
* **Reminders:** Send per-session email reminders via WP-CLI.
* **Calendar Integration:** Generate .ics files for series and sessions.
* **RSVP:** Track attendee responses.

== Installation ==

1. Upload the `organizer` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Install "WP Mail SMTP" to configure email delivery.
4. (Optional) Set up a cron job for reminders: `wp organizer send-reminders`
