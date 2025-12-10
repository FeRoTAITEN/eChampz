Onboarding Flow
===============

Scope
- Applies after authentication (Bearer token required).
- All steps are mandatory. Recruiters have an extra step (represent).
- No age restriction: birthdate accepted as provided.
- XP is granted per step + completion bonus (see below).

Endpoints (implemented)
- GET  /api/v1/onboarding/status
- POST /api/v1/onboarding/name
- POST /api/v1/onboarding/birthday
- POST /api/v1/onboarding/represent   (recruiter only)

1) Status
- GET /api/v1/onboarding/status
  Response example:
  {
    "completed": false,
    "pending_steps": ["name", "birthday", "represent"],
    "role": "recruiter"
  }

2) Name (mandatory for all)
- POST /api/v1/onboarding/name
  body: { "name": "Full Name" }
  notes: saves display name.

3) Birthday (mandatory for all)
- POST /api/v1/onboarding/birthday
  body: { "day": 12, "month": 3, "year": 2000 }
  notes: no age validation required; store as date_of_birth.

4) Represent (mandatory for recruiters only)
- POST /api/v1/onboarding/represent
  body:
    type: "organization" | "freelancer"
    if organization: { "type": "organization", "organization_name": "Team X", "position": "Manager" }
    if freelancer:   { "type": "freelancer" }

5) Completion
- Mark onboarding completed when all required steps for the user role are saved.
- Suggest field: onboarding_completed_at (timestamp) or onboarding_completed (boolean).

XP Mapping
- Name:      10 XP
- Birthday:  10 XP
- Represent: 15 XP (recruiter only)
- Completion bonus (all required steps done): 20 XP
- XP is recorded in xp_transactions (unique per step) and aggregated into users.xp_total.

Rules & Notes
- All steps require Authorization: Bearer {token}.
- Steps are mandatory; reject requests if a required step is missing for the role.
- Recruiter must submit represent; gamer does not.
- No minimum age validation; accept provided date.

Testing ideas
- Recruiter path: name -> birthday -> represent -> status completed.
- Gamer path: name -> birthday -> status completed.
- Ensure status reports pending steps correctly per role.
