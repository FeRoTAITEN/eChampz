Feedback API
============

Base URL
- Local: http://localhost:8000
- Prefix: /api/v1
- Auth: Bearer token via Laravel Sanctum
- Requires: Authenticated user with verified email

Overview
--------
The Feedback API allows authenticated users to submit feedback about the platform. Users can submit general feedback, bug reports, feature requests, or other types of feedback. Users can also view their own feedback history.

Endpoints
---------

1. POST /api/v1/feedback
   Description: Submit feedback
   Authentication: Required (Bearer token)
   Body:
     {
       "subject": "Feedback subject",
       "message": "Feedback message content",
       "type": "general"  // optional: general, bug, feature, other (default: general)
     }
   Response: Created feedback object
   Notes:
     - Subject is required (max 255 characters)
     - Message is required
     - Type defaults to "general" if not provided
     - Status is automatically set to "new"
     - User ID is automatically set from authenticated user

2. GET /api/v1/feedback
   Description: Get current user's feedback history
   Authentication: Required (Bearer token)
   Query Parameters:
     - status: (optional) Filter by status (new, reviewed, resolved)
     - type: (optional) Filter by type (general, bug, feature, other)
     - per_page: (optional, default: 20) Items per page
     - page: (optional, default: 1) Page number
   Response: Paginated list of user's feedback
   Notes:
     - Only returns feedback submitted by the authenticated user
     - Results are ordered by creation date (newest first)

Response Format
---------------
All responses follow the standard ApiResponse format:

Success (Create):
{
  "success": true,
  "message": "Feedback submitted successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "subject": "Great platform!",
    "message": "I really love using eChampz...",
    "type": "general",
    "status": "new",
    "admin_notes": null,
    "created_at": "2026-01-06T00:00:00.000000Z",
    "updated_at": "2026-01-06T00:00:00.000000Z"
  }
}

Success (List):
{
  "success": true,
  "message": "Feedback retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "subject": "Great platform!",
        "message": "I really love using eChampz...",
        "type": "general",
        "status": "new",
        "admin_notes": null,
        "created_at": "2026-01-06T00:00:00.000000Z",
        "updated_at": "2026-01-06T00:00:00.000000Z"
      }
    ],
    "per_page": 20,
    "total": 1
  }
}

Error (Validation):
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "subject": ["The subject field is required."],
    "message": ["The message field is required."]
  }
}

Feedback Object Structure
-------------------------
{
  "id": 1,
  "user_id": 1,
  "subject": "Great platform!",
  "message": "I really love using eChampz. Keep up the good work!",
  "type": "general",
  "status": "new",
  "admin_notes": null,
  "created_at": "2026-01-06T00:00:00.000000Z",
  "updated_at": "2026-01-06T00:00:00.000000Z"
}

Feedback Types
--------------
- general: General feedback or comments
- bug: Bug reports or issues
- feature: Feature requests
- other: Other types of feedback

Feedback Status
---------------
- new: Newly submitted feedback (default)
- reviewed: Feedback has been reviewed by admin
- resolved: Feedback has been resolved/addressed

Usage Examples
--------------

1. Submit General Feedback:
   POST /api/v1/feedback
   Authorization: Bearer {token}
   Content-Type: application/json
   {
     "subject": "Great platform!",
     "message": "I really love using eChampz. Keep up the good work!",
     "type": "general"
   }

2. Submit Bug Report:
   POST /api/v1/feedback
   Authorization: Bearer {token}
   Content-Type: application/json
   {
     "subject": "Login Issue on Mobile",
     "message": "I am experiencing issues logging in on mobile devices.",
     "type": "bug"
   }

3. Submit Feature Request:
   POST /api/v1/feedback
   Authorization: Bearer {token}
   Content-Type: application/json
   {
     "subject": "Dark Mode Request",
     "message": "Can you please add a dark mode option?",
     "type": "feature"
   }

4. View My Feedback:
   GET /api/v1/feedback
   Authorization: Bearer {token}

5. Filter by Status:
   GET /api/v1/feedback?status=new
   Authorization: Bearer {token}

6. Filter by Type:
   GET /api/v1/feedback?type=bug
   Authorization: Bearer {token}

7. Combined Filters:
   GET /api/v1/feedback?status=new&type=bug&per_page=10
   Authorization: Bearer {token}

Notes
-----
- All endpoints require authentication (Bearer token)
- All endpoints require verified email
- Users can only view their own feedback
- Subject field has a maximum length of 255 characters
- Message field has no length limit
- Feedback type defaults to "general" if not specified
- Status workflow: new → reviewed → resolved
- Admin notes are only visible to admins (not returned in API)
- Feedback is ordered by creation date (newest first)

Testing
-------
Import the Postman collection from this directory to test all endpoints.
Make sure to set your authentication token in the Postman environment variables.

