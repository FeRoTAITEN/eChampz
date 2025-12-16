Posts & Engagement APIs
========================

Base URL
- Local: http://localhost:8000
- Prefix: /api/v1
- Auth: Bearer token via Laravel Sanctum
- Requires: Authentication + Verified Email
- Global rate limit: 60 requests/minute

Authenticated Endpoints (Authorization: Bearer {token} + verified email)
- GET    /api/v1/posts              Get feed (global or following)
- POST   /api/v1/posts              Create post
- GET    /api/v1/posts/{id}         Get single post
- PUT    /api/v1/posts/{id}         Update post (owner only)
- DELETE /api/v1/posts/{id}         Delete post (owner only)

1) Get Feed
- GET /api/v1/posts
  Query params:
    - type: "global" | "following" (default: "global")
    - page: integer (default: 1)
    - per_page: integer 1-50 (default: 20)
  Response: Paginated list of posts with content_segments, mentions, media, games

2) Create Post
- POST /api/v1/posts
  body:
    - content: string (required, max: 5000)
    - type: "text" | "image" | "video" (optional, default: "text")
    - game_ids: array of game IDs (optional)
    - media: array (optional)
      - type: "image" | "video" (required)
      - url: string (required)
      - thumbnail_url: string (optional)
  notes:
    - Mentions (@username) are automatically parsed and validated
    - Invalid mentions are silently ignored (not stored)
    - Only valid usernames create mention records
  Response: Created post with content_segments array

3) Get Single Post
- GET /api/v1/posts/{id}
  notes: Automatically increments view count
  Response: Post with content_segments, mentions, media, games

4) Update Post
- PUT /api/v1/posts/{id}
  body:
    - content: string (required, max: 5000)
  notes:
    - Only post owner can update
    - Mentions are re-parsed and re-validated
    - Old mentions are deleted, new ones created
  Response: Updated post with content_segments

5) Delete Post
- DELETE /api/v1/posts/{id}
  notes:
    - Only post owner can delete
    - Soft delete (can be restored)
  Response: Success message

Mentions
--------
- Format: @username in post content
- Parsing: Backend automatically extracts @username patterns
- Validation: Only existing usernames create mention records
- Storage: Stored in post_mentions table with position and length
- Response: Both raw content and pre-processed content_segments array

Content Segments Format
------------------------
The API returns both:
- content: Raw text (for editing)
- content_segments: Pre-processed array for frontend rendering

Example content_segments:
[
  {"type": "text", "value": "Hey "},
  {
    "type": "mention",
    "username": "john_doe",
    "user_id": 5,
    "name": "John Doe",
    "display": "@john_doe"
  },
  {"type": "text", "value": " check this out!"}
]

Frontend should map through segments:
- type="text": render as plain text
- type="mention": render as clickable link to /users/{user_id}

Game Tags
---------
- Multiple games can be attached to a post
- Games must exist in games table
- Pass game_ids array when creating/updating post
- Response includes games array with id, name, slug, icon_url

Media
-----
- Multiple media items per post
- Supported types: image, video
- Order is preserved via order field
- thumbnail_url optional for videos

Engagement Metrics
------------------
Each post includes:
- views: Total view count (auto-incremented on GET)
- upvotes: Upvote count
- downvotes: Downvote count
- shares: Share count

Note: Engagement endpoints (upvote, downvote, share) are not yet implemented.

Example Request
---------------
POST /api/v1/posts
Authorization: Bearer {token}
Content-Type: application/json

{
  "content": "Hey @john_doe check this amazing play! @jane_smith",
  "type": "image",
  "game_ids": [1, 2],
  "media": [
    {
      "type": "image",
      "url": "https://example.com/image.jpg"
    }
  ]
}

Example Response
----------------
{
  "success": true,
  "message": "Post created successfully",
  "data": {
    "id": 1,
    "content": "Hey @john_doe check this amazing play! @jane_smith",
    "content_segments": [
      {"type": "text", "value": "Hey "},
      {
        "type": "mention",
        "username": "john_doe",
        "user_id": 5,
        "name": "John Doe",
        "display": "@john_doe"
      },
      {"type": "text", "value": " check this amazing play! "},
      {
        "type": "mention",
        "username": "jane_smith",
        "user_id": 7,
        "name": "Jane Smith",
        "display": "@jane_smith"
      }
    ],
    "type": "image",
    "views": 0,
    "upvotes": 0,
    "downvotes": 0,
    "shares": 0,
    "created_at": "2025-12-10T12:40:00.000000Z",
    "updated_at": "2025-12-10T12:40:00.000000Z",
    "user": {
      "id": 3,
      "name": "Lindsay Tromp",
      "username": "lindsay_tromp",
      "avatar_url": null
    },
    "mentions": [
      {
        "user_id": 5,
        "username": "john_doe",
        "name": "John Doe"
      },
      {
        "user_id": 7,
        "username": "jane_smith",
        "name": "Jane Smith"
      }
    ],
    "media": [
      {
        "id": 1,
        "type": "image",
        "url": "https://example.com/image.jpg",
        "thumbnail_url": null
      }
    ],
    "games": [
      {
        "id": 1,
        "name": "Valorant",
        "slug": "valorant",
        "icon_url": null
      }
    ]
  }
}

Notes
-----
- All endpoints require authentication (Bearer token)
- All endpoints require verified email
- Mentions are parsed and validated on backend only
- Invalid mentions are silently ignored (no error, just not stored)
- Content segments are built dynamically on read (always current username)
- Username changes don't break old mentions (stored by user_id)
- Future: Can add notifications for mentioned users
- Future: Can add engagement endpoints (upvote, downvote, share)

Testing
-------
- Use Postman collection for manual testing
- Ensure users have verified emails before testing
- Create test games in games table before attaching to posts


