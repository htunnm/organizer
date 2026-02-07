# API Authentication

The Organizer plugin uses WordPress's native REST API authentication methods. For external applications (such as mobile apps or third-party integrations), we recommend using **Application Passwords**.

## Setting up Application Passwords

1.  **Navigate to Profile**: Log in to your WordPress admin dashboard and go to **Users > Profile**.
2.  **Scroll Down**: Find the "Application Passwords" section.
3.  **Create New**: Enter a name for the application (e.g., "Organizer Mobile App") and click "Add New Application Password".
4.  **Copy Password**: Copy the generated password. You will not be able to see it again.

## Making Authenticated Requests

To authenticate your API requests, use **Basic Authentication** with your WordPress username and the Application Password you just generated.

### Header Format

```http
Authorization: Basic base64(username:application_password)
```

### Example (cURL)

```bash
curl -X POST https://yoursite.com/wp-json/organizer/v1/checkin \
     -H "Authorization: Basic dXNlcm5hbfi6cGFzc3dvcmQ=" \
     -d "token=YOUR_TICKET_TOKEN"
```

### Example (JavaScript / Fetch)

```javascript
const username = 'your_username';
const password = 'your_application_password';
const headers = new Headers();
headers.set('Authorization', 'Basic ' + btoa(username + ":" + password));

fetch('https://yoursite.com/wp-json/organizer/v1/checkin', {
    method: 'POST',
    headers: headers,
    body: JSON.stringify({ token: 'YOUR_TICKET_TOKEN' })
})
.then(response => response.json())
.then(data => console.log(data));
```