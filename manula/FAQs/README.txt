FAQs API
========

Base URL
- Local: http://localhost:8000
- Prefix: /api/v1
- Auth: Public (no authentication required)

Overview
--------
The FAQs API provides access to frequently asked questions. FAQs are organized by category and can be searched. This is a public API that doesn't require authentication.

Endpoints
---------

1. GET /api/v1/faqs
   Description: List all active FAQs
   Query Parameters:
     - category: (optional) Filter by category (e.g., "General", "Account", "Technical")
     - search: (optional) Search in question and answer text
     - per_page: (optional, default: 20) Items per page
     - page: (optional, default: 1) Page number
   Response: Paginated list of active FAQs
   Example: GET /api/v1/faqs?category=General&search=password&per_page=10
   Notes:
     - Only returns FAQs where is_active = true
     - Results are ordered by 'order' field (ascending), then by creation date
     - Search works across both question and answer fields

2. GET /api/v1/faqs/{id}
   Description: Get a single FAQ by ID
   Parameters:
     - id: FAQ ID
   Response: Single FAQ object
   Notes:
     - Only returns active FAQs
     - Automatically increments the view count
     - Returns 404 if FAQ not found or inactive

Response Format
---------------
All responses follow the standard ApiResponse format:

Success:
{
  "success": true,
  "message": "FAQs retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "category": "General",
        "question": "What is eChampz?",
        "answer": "eChampz is a gaming platform...",
        "order": 1,
        "is_active": true,
        "views": 42,
        "created_at": "2026-01-06T00:00:00.000000Z",
        "updated_at": "2026-01-06T00:00:00.000000Z"
      }
    ],
    "per_page": 20,
    "total": 1
  }
}

Error (404):
{
  "success": false,
  "message": "FAQ not found"
}

FAQ Object Structure
-------------------
{
  "id": 1,
  "category": "General",
  "question": "What is eChampz?",
  "answer": "eChampz is a gaming platform for gamers and recruiters.",
  "order": 1,
  "is_active": true,
  "views": 42,
  "created_at": "2026-01-06T00:00:00.000000Z",
  "updated_at": "2026-01-06T00:00:00.000000Z"
}

Usage Examples
--------------

1. List All FAQs:
   GET /api/v1/faqs
   (No authentication required)

2. Filter by Category:
   GET /api/v1/faqs?category=Account
   (No authentication required)

3. Search FAQs:
   GET /api/v1/faqs?search=password
   (No authentication required)

4. Get Single FAQ:
   GET /api/v1/faqs/1
   (No authentication required)

5. Combined Filters:
   GET /api/v1/faqs?category=Technical&search=playstation&per_page=5
   (No authentication required)

Available Categories
-------------------
Common categories include:
- General
- Account
- Payments
- Technical
- Other

Note: Categories are configurable by admins and may vary.

Notes
-----
- This is a PUBLIC API - no authentication required
- Only active FAQs (is_active = true) are returned
- View count is automatically incremented when viewing a single FAQ
- Search is case-insensitive and matches partial text
- Results are paginated by default (20 per page)
- Order field determines display order (lower numbers appear first)

Testing
-------
Import the Postman collection from this directory to test all endpoints.
No authentication token is required for these endpoints.

