Tickets API
===========

Base URL
- Local: http://localhost:8000
- Prefix: /api/v1
- Auth: Bearer token via Laravel Sanctum
- Requires: Authenticated user with verified email

Overview
--------
The Tickets API provides a complete support ticket system. Users can create tickets, view their tickets, add responses, and upload attachments. Tickets support categories, priorities, and status tracking. Each ticket has a unique ticket number and maintains a conversation thread.

Endpoints
---------

1. POST /api/v1/tickets
   Description: Create a new support ticket
   Authentication: Required (Bearer token)
   Body:
     {
       "subject": "Ticket subject",
       "description": "Detailed description of the issue",
       "category": "support",  // optional: bug, feature, support, account, other (default: support)
       "priority": "medium"    // optional: low, medium, high, urgent (default: medium)
     }
   Response: Created ticket object with auto-generated ticket number
   Notes:
     - Subject is required (max 255 characters)
     - Description is required
     - Ticket number is automatically generated (format: TKT-YYYYMMDD-XXX)
     - Status is automatically set to "open"
     - User ID is automatically set from authenticated user

2. GET /api/v1/tickets
   Description: List current user's tickets
   Authentication: Required (Bearer token)
   Query Parameters:
     - status: (optional) Filter by status (open, in_progress, resolved, closed)
     - category: (optional) Filter by category (bug, feature, support, account, other)
     - priority: (optional) Filter by priority (low, medium, high, urgent)
     - per_page: (optional, default: 20) Items per page
     - page: (optional, default: 1) Page number
   Response: Paginated list of user's tickets
   Notes:
     - Only returns tickets created by the authenticated user
     - Results are ordered by creation date (newest first)

3. GET /api/v1/tickets/{id}
   Description: Get ticket details with full conversation thread
   Authentication: Required (Bearer token)
   Parameters:
     - id: Ticket ID
   Response: Ticket object with responses and attachments
   Notes:
     - Only accessible by the ticket owner
     - Returns 404 if ticket not found or not owned by user
     - Includes all responses (user and admin)
     - Includes all attachments

4. POST /api/v1/tickets/{id}/responses
   Description: Add a response to a ticket
   Authentication: Required (Bearer token)
   Parameters:
     - id: Ticket ID
   Body:
     {
       "message": "Response message"
     }
   Response: Created response object
   Notes:
     - Only accessible by the ticket owner
     - Cannot add responses to closed tickets
     - If ticket was resolved, status changes back to "open"
     - Message is required

5. POST /api/v1/tickets/{id}/attachments
   Description: Upload attachment to a ticket
   Authentication: Required (Bearer token)
   Parameters:
     - id: Ticket ID
   Body (multipart/form-data):
     - file: (required) File to upload
     - response_id: (optional) Attach to a specific response
   Response: Created attachment object
   Notes:
     - Only accessible by the ticket owner
     - Max file size: 10MB
     - Allowed types: jpg, jpeg, png, gif, pdf, doc, docx
     - If response_id is provided, must belong to the ticket and user
     - Files are stored in storage/app/public/tickets/{ticket_id}/

Response Format
---------------
All responses follow the standard ApiResponse format:

Success (Create Ticket):
{
  "success": true,
  "message": "Ticket created successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "ticket_number": "TKT-20260106-001",
    "subject": "Account Access Issue",
    "description": "I cannot access my account...",
    "category": "account",
    "priority": "high",
    "status": "open",
    "assigned_to": null,
    "resolved_at": null,
    "created_at": "2026-01-06T00:00:00.000000Z",
    "updated_at": "2026-01-06T00:00:00.000000Z"
  }
}

Success (Get Ticket with Responses):
{
  "success": true,
  "message": "Ticket retrieved successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "ticket_number": "TKT-20260106-001",
    "subject": "Account Access Issue",
    "description": "I cannot access my account...",
    "category": "account",
    "priority": "high",
    "status": "open",
    "assigned_to": null,
    "resolved_at": null,
    "responses": [
      {
        "id": 1,
        "ticket_id": 1,
        "user_id": 1,
        "admin_id": null,
        "message": "This is a user response.",
        "created_at": "2026-01-06T00:00:00.000000Z",
        "attachments": []
      },
      {
        "id": 2,
        "ticket_id": 1,
        "user_id": null,
        "admin_id": 1,
        "message": "This is an admin response.",
        "created_at": "2026-01-06T00:00:00.000000Z",
        "attachments": []
      }
    ],
    "attachments": []
  }
}

Error (404):
{
  "success": false,
  "message": "Ticket not found"
}

Error (400 - Closed Ticket):
{
  "success": false,
  "message": "Cannot add response to a closed ticket"
}

Ticket Object Structure
----------------------
{
  "id": 1,
  "user_id": 1,
  "ticket_number": "TKT-20260106-001",
  "subject": "Account Access Issue",
  "description": "I cannot access my account. Please help.",
  "category": "account",
  "priority": "high",
  "status": "open",
  "assigned_to": null,
  "resolved_at": null,
  "created_at": "2026-01-06T00:00:00.000000Z",
  "updated_at": "2026-01-06T00:00:00.000000Z"
}

Ticket Categories
-----------------
- bug: Bug reports or technical issues
- feature: Feature requests
- support: General support requests
- account: Account-related issues
- other: Other types of tickets

Ticket Priorities
-----------------
- low: Low priority (default for some categories)
- medium: Medium priority (default)
- high: High priority issues
- urgent: Urgent issues requiring immediate attention

Ticket Status
-------------
- open: Newly created ticket (default)
- in_progress: Ticket is being worked on
- resolved: Issue has been resolved
- closed: Ticket is closed (no further responses allowed)

Usage Examples
--------------

1. Create Support Ticket:
   POST /api/v1/tickets
   Authorization: Bearer {token}
   Content-Type: application/json
   {
     "subject": "Account Access Issue",
     "description": "I cannot access my account. Please help.",
     "category": "account",
     "priority": "high"
   }

2. Create Bug Report Ticket:
   POST /api/v1/tickets
   Authorization: Bearer {token}
   Content-Type: application/json
   {
     "subject": "Login Button Not Working",
     "description": "The login button does not respond when clicked on mobile.",
     "category": "bug",
     "priority": "urgent"
   }

3. List My Tickets:
   GET /api/v1/tickets
   Authorization: Bearer {token}

4. Get Ticket Details:
   GET /api/v1/tickets/1
   Authorization: Bearer {token}

5. Add Response to Ticket:
   POST /api/v1/tickets/1/responses
   Authorization: Bearer {token}
   Content-Type: application/json
   {
     "message": "I've tried clearing my cache but the issue persists."
   }

6. Upload Attachment:
   POST /api/v1/tickets/1/attachments
   Authorization: Bearer {token}
   Content-Type: multipart/form-data
   - file: [select file]
   - response_id: 1 (optional)

7. Filter Tickets:
   GET /api/v1/tickets?status=open&priority=high&category=bug
   Authorization: Bearer {token}

Notes
-----
- All endpoints require authentication (Bearer token)
- All endpoints require verified email
- Users can only view/manage their own tickets
- Ticket numbers are auto-generated: TKT-YYYYMMDD-XXX format
- Subject field has a maximum length of 255 characters
- Description field has no length limit
- Maximum file size for attachments: 10MB
- Allowed file types: jpg, jpeg, png, gif, pdf, doc, docx
- Attachments are stored in: storage/app/public/tickets/{ticket_id}/
- Cannot add responses to closed tickets
- If a resolved ticket receives a new response, status changes to "open"
- Tickets are ordered by creation date (newest first)
- Admin responses are included in the conversation thread
- Users can only attach files to their own responses

Testing
-------
Import the Postman collection from this directory to test all endpoints.
Make sure to set your authentication token in the Postman environment variables.
For file uploads, use multipart/form-data format in Postman.

