# Email Configuration

The Organizer plugin uses the `GmailAdapter` by default, which relies on WordPress's native `wp_mail()` function.

## Setup Instructions

1. **Install WP Mail SMTP**:
   - Install and activate the "WP Mail SMTP" plugin by WPForms.
   
2. **Configure Google / Gmail**:
   - Go to **WP Mail SMTP > Settings**.
   - Select **Google / Gmail** as the mailer.
   - Follow the on-screen setup wizard to create a Google Cloud App and get your Client ID and Client Secret.
   - **Important**: Do not store Client IDs or Secrets in the `organizer` plugin code.

## Sending Limits

- **Gmail (Free)**: ~500 emails per rolling 24 hours.
- **Google Workspace**: ~2,000 emails per rolling 24 hours per user.
- **Note**: If you exceed these limits, Google will temporarily block sending.

## Scaling Up
For high-volume events (>2,000 attendees), switch the WP Mail SMTP mailer to:
- Mailgun
- Amazon SES
- SendGrid

The `Organizer` plugin code does not need to change; it simply calls `wp_mail()`, and the SMTP plugin handles the delivery channel.
