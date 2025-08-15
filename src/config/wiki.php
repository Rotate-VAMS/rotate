<?php

return [
    'pages' => [
        'onboarding' => [
            'page_title' => 'Onboarding',
            'content' => [
                'intro' => 'Welcome to Rotate! This guide walks a new admin through first‑time setup so your Virtual Airline (VA) is ready for pilots and operations within minutes.',
                'What You\'ll Do' => 'You will familiarize yourself with the UI, visit Settings, and configure core building blocks: roles, ranks, fleet, flight types, custom fields, leaderboard, branding, and Discord.',
                'Quick Product Tour' => [
                    '**Top Bar:** Global navigation to Dashboard, Operations, Pilots, Events, and Settings.',
                    '**Dashboard Cards:** At‑a‑glance numbers (pilots, flights, events) with quick links.',
                    '**Search & Actions:** Use global search to jump to pilots/routes; use quick actions for frequent tasks.',
                ],
                'First Login Checklist' => [
                    '**Go to Settings → General:** Confirm VA name, timezone.',
                    '**Branding → Logo:** Upload your VA logo (PNG/SVG).',
                    '**Access Control → Roles:** Review default roles; add any custom roles you need.',
                    '**Pilot Progression → Ranks:** Add rank ladder and minimum hours.',
                    '**Operations → Fleet:** Add aircraft, codeshares, and minimum rank requirements.',
                    '**Operations → Flight Types:** Create multipliers for Short/Long/Ultra flights if needed.',
                    '**Customization → Custom Fields:** Add any fields for Pilots, PIREPs, Routes, Events.',
                    '**Engagement → Leaderboard:** Configure points for operations and milestones.',
                    '**Integrations → Discord:** Invite the bot and enable notifications.',
                ],
                'Best Practices' => [
                    '**Start small:** Create a minimal set of ranks and expand later.',
                    '**Test a full flow:** Add one aircraft, file a test PIREP, review stats.',
                    '**Document your rules:** Note your VA’s rank requirements, PIREP expectations, and event policy for pilots.',
                ],
                'Next Steps' => 'Proceed through each sub‑page below for detailed setup instructions. After finishing, announce your VA onboarding steps to your pilots.'
            ],
        ],

        // 1.1 Roles & Permissions
        'roles-permissions' => [
            'page_title' => 'Roles & Permissions',
            'content' => [
                'intro' => 'Roles control what users can do. Rotate ships with two roles out of the box: **Admin** and **Pilot**.',
                'Defaults' => [
                    '**Admin:** All permissions enabled. This role’s permissions cannot be modified.',
                    '**Pilot:** Basic flying/PIREP capabilities.',
                ],
                'Creating Custom Roles' => [
                    '**Settings → Access Control → Roles → New Role:** Name the role (e.g., Dispatcher, Event Manager).',
                    '**Assign Permissions:** Toggle fine‑grained capabilities (create routes, approve PIREPs, manage events, etc.).',
                    '**Save & Assign:** Open a user profile and attach the new role. Users can hold multiple roles.',
                ],
                'Guidelines' => [
                    '**Least privilege:** Grant only what’s needed.',
                    '**Keep one protected Admin:** Ensure at least one account retains Admin to avoid lockout.',
                    '**Audit regularly:** Review role usage as your VA grows.',
                ],
            ],
        ],

        // 1.2 Ranks
        'ranks' => [
            'page_title' => 'Ranks',
            'content' => [
                'intro' => 'Ranks define progression and access based on flight hours.',
                'Key Concepts' => [
                    '**Two required fields:** Rank name and minimum hours for that rank.',
                    '**Progression:** Pilots automatically qualify for higher ranks as hours accumulate.',
                    '**Usage:** Ranks can gate aircraft/fleet access and influence leaderboards or events.',
                ],
                'Setup Steps' => [
                    '**Settings → Pilot Progression → Ranks → New Rank:** Provide a name (e.g., Cadet, First Officer, Captain).',
                    '**Minimum Hours:** Enter the threshold required to obtain the rank.',
                    '**Ordering:** Arrange ranks from lowest to highest.',
                ],
                'Tips' => [
                    '**Smooth ladder:** Keep hour jumps reasonable so new pilots see steady progress.',
                    '**Communicate rules:** Publish rank requirements so pilots know the path.',
                ],
            ],
        ],

        // 1.3 Fleet
        'fleet' => [
            'page_title' => 'Fleet',
            'content' => [
                'intro' => 'Define the aircraft your VA operates, optional codeshares, and minimum ranks required to fly each type.',
                'What to Configure' => [
                    '**Aircraft Types:** Add each model (e.g., A320, B738, B77W) with basic metadata.',
                    '**Codeshares (optional):** Mark aircraft/routes operated via partners.',
                    '**Minimum Rank:** Set the lowest rank that can fly this aircraft.',
                    '**Defaults:** Choose default fleet where appropriate for quick route creation.',
                ],
                'Setup Steps' => [
                    '**Settings → Operations → Fleet → Add Aircraft:** Enter name, ICAO/IATA where relevant.',
                    '**Rank Requirement:** Choose the minimum rank needed.',
                    '**Assign to Routes:** When creating routes, pick allowed aircraft from your fleet.',
                ],
                'Best Practices' => [
                    '**Progression mapping:** Tie widebodies to higher ranks for meaningful growth.',
                    '**Consistency:** Align route aircraft with real‑world or VA policy.',
                ],
            ],
        ],

        // 1.4 Flight Types
        'flight-types' => [
            'page_title' => 'Flight Types',
            'content' => [
                'intro' => 'Flight Types let you categorize operations and apply a **multiplier** to computed flight time for PIREPs.',
                'Defaults' => [
                    '**Regular:** Preconfigured with a 1.0× multiplier.',
                ],
                'How Multipliers Work' => 'Computed time = **base flight time × multiplier**. Example: 2.0 hours × 1.5 = 3.0 computed.',
                'Setup Steps' => [
                    '**Settings → Operations → Flight Types → New Type:** Provide a name (e.g., Short Haul, Long Haul, Ultra Long).',
                    '**Multiplier:** Enter a decimal such as 1.1, 1.25, 1.5.',
                    '**Apply:** When pilots file a PIREP and pick this type, the multiplier is used.',
                ],
                'Guidelines' => [
                    '**Reasonable multipliers:** Avoid excessive values that distort stats.',
                    '**Transparency:** Publish which types exist and what they mean.',
                ],
            ],
        ],

        // 1.5 Custom Fields
        'custom-fields' => [
            'page_title' => 'Custom Fields',
            'content' => [
                'intro' => 'Add structured data to Pilots, PIREPs, Routes, and Events. These fields appear in create/edit forms and detail views.',
                'Supported Types' => [
                    '**Text**', '**Integer**', '**Float**', '**Boolean**', '**Date**', '**Dropdown**',
                ],
                'Dropdown Configuration' => [
                    '**Static options:** Define a list of allowed values.',
                    '**Entity selectors:** Link to entities like Events, Pilots, Routes, Fleets, or add custom values.',
                ],
                'Setup Steps' => [
                    '**Settings → Customization → Custom Fields → Add Field:** Choose the target (Pilot/PIREP/Route/Event).',
                    '**Field Type:** Pick from the supported types.',
                    '**Visibility & Validation:** Mark required/optional, set min/max or patterns where relevant.',
                    '**Order:** Arrange display order for forms and cards.',
                ],
                'Tips' => [
                    '**Keep it relevant:** Only add fields you plan to use in workflows or reports.',
                    '**Use dropdowns for consistency:** Prevents inconsistent free‑text entries.',
                ],
            ],
        ],

        // 1.6 Leaderboard
        'leaderboard' => [
            'page_title' => 'Leaderboard',
            'content' => [
                'intro' => 'Reward activity and milestones. Configure points that accrue to pilots and power friendly competition.',
                'Categories' => [
                    '**Flight Operations:** Filing a PIREP, Short Haul (< 2h), Long Haul (> 4h), Ultra Long Haul (> 8h).',
                    '**Milestones:** First PIREP, 100 PIREPs, 500 PIREPs, 1000 PIREPs.',
                ],
                'How Scoring Works' => [
                    'Each configured event adds points to the pilot when conditions are met. Time‑based categories are evaluated from PIREP flight time (after multipliers).',
                ],
                'Setup Steps' => [
                    '**Settings → Engagement → Leaderboard:** Enter point values for each operation and milestone.',
                    '**Save & Test:** File a few test PIREPs with different durations to verify scoring.',
                ],
                'Recommendations' => [
                    '**Balanced values:** Keep routine actions low and milestones higher.',
                    '**Communicate scoring:** Publish your scheme so pilots know how to climb the board.',
                ],
            ],
        ],

        // 1.7 Logo / Branding
        'logo-branding' => [
            'page_title' => 'Logo & Branding',
            'content' => [
                'intro' => 'Upload your VA logo and ensure it displays nicely across login and the top bar.',
                'Requirements' => [
                    '**Formats:** PNG or SVG recommended.',
                    '**Sizing:** Provide at least a 512×512 PNG plus an SVG for crisp scaling.',
                    '**Variants:** If your logo has dark/light variants, upload the one that suits your theme best.',
                ],
                'Setup Steps' => [
                    '**Settings → Branding → Logo:** Upload and save.',
                    '**Preview:** Check login screen and the app header.',
                    '**Cache:** If using a CDN, purge cache after updates.',
                ],
            ],
        ],

        // 1.8 Discord Integration
        'discord-integration' => [
            'page_title' => 'Discord Integration',
            'content' => [
                'intro' => 'Integrate Discord to file PIREPs via chat and post notifications for Events and PIREPs.',
                'Features' => [
                    '**File PIREP from Discord:** Interactive flow in a user’s private channel.',
                    '**Event Notifications:** Post event create/update reminders to a channel.',
                    '**PIREP Notifications:** Post new/approved PIREPs to a channel.',
                ],
                'Prerequisites' => [
                    '**Invite the bot:** Once invited, PIREP filing is enabled automatically.',
                    '**Register Discord ID:** Each user must add their Discord ID in VAMS → Profile Settings → Discord Integration.',
                    '**Permissions:** Ensure the bot can read/send messages and add reactions in target channels.',
                ],
                'PIREP Commands' => [
                    '**`!rotate pirep`** → Start guided PIREP filing for a regular flight. The bot DM will collect fields step by step.',
                    '**`!rotate pe`** → File a PIREP for a **past event** you participated in. Only enrolled participants are allowed.',
                ],
                'Enable Notifications' => [
                    '**Admin → Settings → Integrations → Discord:**',
                    '• **Enable/Disable Event Notifications** and **Enable/Disable PIREP Notifications**.',
                    '• Enter the **channel ID** where messages should go.',
                    '• Use **Test Connection** to send a sample message and confirm setup.',
                ],
                'Operational Notes' => [
                    '**Rate limits:** Avoid spamming; batch notifications where possible.',
                    '**Security:** Limit command permissions in Discord if needed.',
                    '**Support:** If DMs fail, ask users to allow server DMs or check privacy settings.',
                ],
            ],
        ],

        // Pilot Management — Overview
        'pilot-management' => [
            'page_title' => 'Pilot Management',
            'content' => [
                'intro' => 'Manage your pilot roster end‑to‑end: add new pilots, control access, update records, deactivate or delete accounts, and export data for reporting.',
                'What You Can Do' => [
                    '**Create pilots:** Add pilots via the **+ Add Pilot** button.',
                    '**Pilot portal access:** Pilots log in using their email; default password is **123456** (must change on first login).',
                    '**Manage pilots:** Edit details (except passwords), deactivate/reactivate accounts, or delete permanently.',
                    '**Export data:** Download a CSV of pilots for analytics or compliance.',
                ],
                'Best Practices' => [
                    '**Data accuracy first:** Capture correct emails and callsigns at creation to avoid onboarding delays.',
                    '**Security:** Encourage immediate password change; enforce strong password policy.',
                    '**Lifecycle:** Prefer **Deactivate** over **Delete** unless data removal is explicitly required.',
                    '**Compliance:** Export and archive pilot lists periodically per your VA’s policy.',
                ],
            ],
        ],

        // 1. Pilot Creation
        'pilot-creation' => [
            'page_title' => 'Pilot Creation',
            'content' => [
                'intro' => 'Users with the appropriate role permissions can create pilots directly from the Pilots page using a fast, guided sidebar form.',
                'Who Can Create Pilots?' => [
                    'Any role with the **Create Pilot** permission (e.g., Admin, HR/OPS roles you define).',
                ],
                'How to Create a Pilot' => [
                    '**Navigate:** Pilots → **+ Add Pilot**.',
                    '**Fill the form:** Provide required fields (e.g., Name, Callsign, Email, Initial Rank).',
                    '**Optional fields:** Add custom fields if your VA has configured them (e.g., Base, Hub, Discord ID).',
                    '**Save:** The pilot is created and immediately visible in the grid.',
                ],
                'Field Tips' => [
                    '**Email:** Must be unique and valid—this becomes the pilot’s login ID.',
                    '**Rank:** Choose an appropriate starting rank to match access/rules.',
                    '**Custom Fields:** Use dropdowns and validators to maintain clean data.',
                ],
                'After Creation' => [
                    'The pilot can log in right away using their email and the default password (**123456**).',
                    'Send a welcome message with login instructions and links (optional but recommended).',
                ],
            ],
        ],

        // 2. Pilots Accessing Their Portal
        'pilot-portal-access' => [
            'page_title' => 'Pilots Accessing Their Portal',
            'content' => [
                'intro' => 'Pilots access the VAMS using the email provided at creation and a default temporary password.',
                'Login Credentials' => [
                    '**Username:** The email address entered during pilot creation.',
                    '**Default Password:** **123456** (system default for first login).',
                ],
                'Mandatory First Steps' => [
                    '**Change Password:** Strongly encourage pilots to change their password immediately (Profile → Security).',
                    '**Complete Profile:** Ask pilots to fill in missing details (e.g., Discord ID for bot features).',
                ],
                'Access Issues' => [
                    '**Wrong email:** Verify the email on the pilot record.',
                    '**Deactivated account:** Reactivate from the management grid if needed.',
                    '**Password forgotten:** Pilot can use **Forgot Password** (if enabled) or request a reset by an authorized role.',
                ],
            ],
        ],

        // 3. Pilot Management Actions
        'pilot-management-actions' => [
            'page_title' => 'Pilot Management Actions',
            'content' => [
                'intro' => 'In the Pilots grid, the **Actions** column presents three controls for each pilot: Edit, Deactivate/Activate, and Delete. Availability depends on your role permissions.',
                'Edit Pilot' => [
                    '**What you can edit:** Name, callsign, email, rank, hub/base, custom fields, and other non‑credential metadata.',
                    '**What you cannot edit:** **Password**—only the **pilot** can change their password in Profile → Security.',
                    '**When to use:** Fix typos, update rank, change contact info, or maintain operational metadata.',
                ],
                'Deactivate Pilot' => [
                    '**Effect:** The pilot cannot log in or use any VAMS features while deactivated.',
                    '**Data retention:** **No data is deleted.** All PIREPs, stats, and history remain intact.',
                    '**When to use:** Extended inactivity, disciplinary holds, or temporary leave.',
                    '**Reactivation:** Toggle back to active; pilot resumes exactly where they left off.',
                ],
                'Delete Pilot' => [
                    '**Effect:** Permanently removes the pilot **and all related data** (e.g., PIREPs, associations).',
                    '**Irreversible:** This action cannot be undone—confirm before proceeding.',
                    '**When to use:** Only when you must remove all traces (e.g., per data removal request/policy).',
                    '**Alternative:** Prefer **Deactivate** if you only need to block access.',
                ],
                'Audit & Safety' => [
                    '**Permissions:** Ensure only trusted roles have Deactivate/Delete rights.',
                    '**Records:** Consider exporting pilot data before deletion for audit/legal needs.',
                ],
            ],
        ],

        // 4. Exporting Pilots
        'exporting-pilots' => [
            'page_title' => 'Exporting Pilots',
            'content' => [
                'intro' => 'Export your pilot roster to a CSV file directly from the Pilots screen for analysis, backup, or compliance.',
                'How to Export' => [
                    '**Navigate:** Pilots → **Export**.',
                    '**Format:** CSV (comma‑separated values) compatible with Excel, Google Sheets, BI tools.',
                    '**Scope:** Export includes visible columns and configured custom fields (where applicable).',
                ],
                'Use Cases' => [
                    '**Analytics:** Load into BI/Sheets to build leaderboards or activity charts.',
                    '**Compliance:** Archive snapshots of your roster on a schedule.',
                    '**Migration:** Move data into other systems if ever required.',
                ],
                'Best Practices' => [
                    '**Protect sensitive data:** Store exports securely and limit distribution.',
                    '**Data hygiene:** Periodically review and clean custom fields to keep exports tidy.',
                ],
            ],
        ],

        // Routes — Overview
        'routes' => [
            'page_title' => 'Routes',
            'content' => [
                'intro' => 'Manage your Virtual Airline’s network with a powerful routes module. Define flight numbers, aircraft, and constraints, then let pilots file accurate PIREPs in a click.',
                'What You Can Do' => [
                    '**Browse routes:** See flight number, city pairs (ICAO + names), aircraft, distance (NM), flight time (approx), minimum rank, status, and actions.',
                    '**Create & edit:** Add routes via a guided side drawer; update any operational detail later.',
                    '**Control access:** Enforce minimum rank or inherit rank from assigned fleets.',
                    '**Take actions:** File PIREPs instantly, deactivate, or delete routes.',
                    '**Export & import:** Export CSV for analysis; bulk import thousands of routes in one go.',
                ],
                'Best Practices' => [
                    '**Keep fleets updated:** Route creation relies on your fleet registry and rank gates.',
                    '**Use meaningful flight numbers:** E.g., ROT123 for consistency across systems.',
                    '**Validate airports:** Ensure ICAO codes are correct; distances and times depend on these.',
                    '**Prefer deactivate over delete:** Retain history while preventing new PIREPs.',
                ],
            ],
        ],

        // 1) Routes Grid
        'routes-grid' => [
            'page_title' => 'Routes Grid',
            'content' => [
                'intro' => 'A fast, filterable table presents operational details at a glance and shortcuts to common actions.',
                'Columns' => [
                    '**Flight Number:** Your published identifier (e.g., ROT001).',
                    '**Route:** Shown as **ICAO_ORIGIN–ICAO_DEST** with city names beneath (e.g., KJFK–EGLL • New York ⟷ London).',
                    '**Aircraft:** One or more fleets assigned to the route.',
                    '**Distance (NM):** Auto‑calculated great‑circle distance from origin to destination.',
                    '**Flight Time (Approx):** Auto‑estimated based on distance/speed heuristics—pilots can still file exact times in PIREPs.',
                    '**Minimum Rank:** Restricts PIREP filing to pilots at/above this rank (or inherited from assigned fleets).',
                    '**Status:** Active/Inactive. Inactive routes remain visible but cannot be used for new PIREPs.',
                    '**Actions:** Quick actions menu (File PIREP, Edit, Deactivate/Activate, Delete).',
                ],
                'Custom Fields' => [
                    'Any admin‑defined custom fields for Routes appear as additional columns and within the route drawer.',
                ],
                'Filtering & Search' => [
                    'Filter by aircraft, origin, destination, status, or rank; search by flight number or ICAO.',
                ],
            ],
        ],

        // 2) Creating Routes
        'route-creation' => [
            'page_title' => 'Creating Routes',
            'content' => [
                'intro' => 'Authorized roles can add new routes using a guided side drawer. Ensure your **Fleets** and **Ranks** are configured first (Settings → Fleets/Ranks).',
                'Prerequisites' => [
                    '**Fleets:** Add your aircraft types and set minimum ranks per fleet (if any).',
                    '**Ranks:** Define rank ladder and thresholds (hours).',
                ],
                'How to Create a Route' => [
                    '**Open drawer:** Routes → **+ Add Route**.',
                    '**Basic details:** Enter **Flight Number**, **Origin ICAO**, **Destination ICAO**.',
                    '**Assign fleets:** Choose one or more fleets that can operate this route.',
                    '**Rank toggle:** By default, the route **inherits minimum rank from assigned fleets**.',
                    '   – **Toggle OFF** to specify a **custom minimum rank** for the route.',
                    '   – **Leave OFF and no rank selected** to allow **no rank restriction**.',
                    '**Save:** Distance (NM) and approximate time auto‑calculate after airports are set.',
                ],
                'Validation & Tips' => [
                    '**ICAO format:** 4 letters (e.g., KJFK, EGLL).',
                    '**Unique flight numbers:** Avoid duplicates for clarity.',
                    '**Multiple fleets:** If fleets have different rank gates, the inherited route gate follows your internal rule (e.g., highest required rank among selected fleets).',
                ],
            ],
        ],

        // 3) Route Actions
        'route-actions' => [
            'page_title' => 'Route Actions',
            'content' => [
                'intro' => 'Use the Actions menu on each row for operational shortcuts and maintenance.',
                'File PIREP' => [
                    '**One‑click start:** Opens the PIREP form with route details pre‑filled (airports, distance/time, aircraft).',
                    '**Adjust & file:** Pilots can tweak times/remarks, then submit.',
                ],
                'Edit Route' => [
                    '**Who:** Roles with Edit permissions.',
                    '**What:** Flight number, ICAOs, fleets, minimum rank mode (inherit vs custom), custom fields.',
                ],
                'Deactivate Route' => [
                    '**Effect:** Route stays visible in the grid but cannot be used for new PIREPs.',
                    '**Use when:** Seasonal routes, temporary closures, or data audits.',
                ],
                'Delete Route' => [
                    '**Effect:** Permanently removes the route from the database.',
                    '**Caution:** Prefer **Deactivate** to preserve history; only delete if you are certain.',
                ],
            ],
        ],

        // 4) Exporting Routes
        'routes-export' => [
            'page_title' => 'Exporting Routes',
            'content' => [
                'intro' => 'Export your full route network to CSV for analysis, backup, or migration.',
                'How to Export' => [
                    'Routes → **Export** → Download CSV.',
                ],
                'Included Data' => [
                    'Flight number, origin/destination ICAO, city names (if stored), assigned fleets, distance (NM), approximate time, minimum rank, status, and custom fields (where applicable).',
                ],
                'Best Practices' => [
                    '**Secure storage:** Route data can imply commercial strategy—store exports safely.',
                    '**Versioning:** Keep dated snapshots if your network changes frequently.',
                ],
            ],
        ],

        // 5) Mass Importing Routes
        'routes-import' => [
            'page_title' => 'Mass Importing Routes',
            'content' => [
                'intro' => 'Import thousands of routes at once via CSV. Perfect for initial setup or seasonal rebuilds.',
                'Required Columns' => [
                    '1. **flight_number** – e.g., "ROT001".',
                    '2. **fleet_ids** – JSON array of Fleet IDs, e.g., `"[1,2]"`.',
                    '3. **origin_icao** – e.g., "KJFK".',
                    '4. **destination_icao** – e.g., "EGLL".',
                    '5. **min_rank** – Minimum Rank ID (e.g., "1"). Use empty/NULL to allow all ranks or rely on fleet inheritance.',
                    '6. **status** – 1 for active, 0 for inactive.',
                ],
                'Where to Find Fleet & Rank IDs' => [
                    '**Settings → Fleets** for Fleet IDs.',
                    '**Settings → Ranks** for Rank IDs.',
                    'IDs are shown on each card’s header/footer.',
                ],
                'CSV Example (first two rows)' => [
                    'flight_number,fleet_ids,origin_icao,destination_icao,min_rank,status',
                    'ROT001,"[1,2]",KJFK,EGLL,2,1',
                    'ROT010,"[3]",KLAX,PHNL,,1',
                ],
                'Import Steps' => [
                    '**Prepare CSV:** Ensure headers and data types match the schema.',
                    '**Upload:** Routes → **Import** → Select CSV file → **Upload**.',
                    '**Map & validate:** The importer validates ICAOs, fleet IDs, ranks, and basic formats.',
                    '**Review results:** See how many rows were created, updated, or skipped with reasons.',
                ],
                'Validation & Error Handling' => [
                    '**Airports:** Invalid ICAO codes are rejected with row numbers.',
                    '**Fleets:** Unknown fleet IDs cause the row to be skipped.',
                    '**Ranks:** If `min_rank` is missing/invalid and inheritance toggle is on, the system falls back to fleet rank logic.',
                    '**Duplicates:** Existing flight numbers + airport pairs may update instead of creating, depending on your import mode.',
                ],
                'After Import' => [
                    'Distances and approximate times are auto‑computed.',
                    'You can bulk‑deactivate or filter newly added routes for spot‑checks.',
                ],
            ],
        ],

        // Events — Overview
        'events-overview' => [
            'page_title' => 'Events Management',
            'content' => [
                'intro' => 'Plan, publish, and run Virtual Airline events with flexible views, simple creation, and a clean registration-to-PIREP workflow.',
                'What You Can Do' => [
                    '**Browse events in multiple layouts:** Switch between Grid View and Card View for quick scanning or rich visuals.',
                    '**Create events fast:** A guided side drawer collects name, description, schedule, airports, aircraft, and cover imagery.',
                    '**Self‑service registration:** Pilots register/deregister before the event start.',
                    '**Event PIREPs:** Only registered pilots can file event PIREPs—keeping participation accurate.',
                ],
                'Best Practices' => [
                    '**Use clear titles & concise descriptions:** State purpose, timing, and special procedures up front.',
                    '**Pick realistic slots & aircraft:** Ensure your fleet and route planning align with expected turnout.',
                    '**Encourage pre‑registration:** It streamlines coordination and PIREP validation later.',
                    '**Add a distinctive cover image:** Helps discoverability and sets the theme.',
                ],
            ],
        ],

        // 1) Visibility & Views
        'events-views' => [
            'page_title' => 'Event Visibility & Views',
            'content' => [
                'intro' => 'Switch between **Grid View** and **Card View** to match your workflow—tabular scanning or visual browsing.',
                'Grid View' => [
                    '**Purpose:** Fast scanning, filtering, and sorting across many events.',
                    '**Shown fields:** **Name**, **Description** (truncated), **Date & Time**, **Origin**, **Destination**, **Aircraft**, **Cover Image** (thumbnail).',
                    '**Default cover image:** If none is provided, a system default is shown so events remain visually consistent.',
                    '**Tips:** Use filters to quickly locate events by date or airport; sort by date to see what’s next.',
                ],
                'Card View' => [
                    '**Purpose:** Rich visual browsing for pilots—great for highlighting upcoming or featured events.',
                    '**Shown fields:** Prominent **cover image**, **name**, short **description**, **date & time**, **origin/destination**, **aircraft**.',
                    '**Action affordances:** **Register**, **Deregister**, and **File PIREP** (when eligible) appear contextually on each card.',
                ],
                'Dates & Timezones' => [
                    'Display times in your VA’s configured timezone (Settings → Basic Settings). Consider adding UTC in descriptions for clarity.',
                ],
            ],
        ],

        // 2) Creating Events
        'event-creation' => [
            'page_title' => 'Creating Events',
            'content' => [
                'intro' => 'Roles with create permissions can publish events through a guided side‑drawer form.',
                'How to Create an Event' => [
                    '**Open the form:** Events → **+ Create new event**.',
                    '**Fill basics:** **Event Name** and **Event Description**. Keep the title short and the description action‑oriented (brief briefing, any special procedures).',
                    '**Schedule:** Set **Event Date & Time** (your VA timezone applies).',
                    '**Airports:** Enter **Origin ICAO** and **Destination ICAO**.',
                    '**Aircraft:** Choose from a dropdown. You can list aircraft from your VA Fleet or allow broader selection if your policy permits.',
                    '**Cover Image:** Upload a custom image for better visibility; if you skip it, a default image is used.',
                    '**Save:** The event appears instantly in both Grid and Card views.',
                ],
                'Validation & Tips' => [
                    '**ICAO format:** Use valid 4‑letter ICAOs (e.g., KJFK, EGLL).',
                    '**Description clarity:** Mention meeting points (gate/stand), comms channel, and any slot procedures if relevant.',
                    '**Lead time:** Publish at least a few days ahead to allow pilot registration and planning.',
                    '**Imagery:** Use 16:9 landscape images for best card presentation.',
                ],
                'Permissions' => [
                    'Only roles with **Create Event** permission can see and use the **+ Create new event** button.',
                ],
            ],
        ],

        // 3) Registration & Event PIREPs
        'event-registration-pireps' => [
            'page_title' => 'Registration & Filing Event PIREPs',
            'content' => [
                'intro' => 'Registration is required to file an event PIREP. Pilots can register or deregister until the event begins.',
                'Registration' => [
                    '**When:** Any time **before** the event start.',
                    '**How:** Click **Register** on the event (Grid or Card). Registration is immediate.',
                    '**Deregister:** Click **Deregister** if plans change; you can re‑register if the event hasn’t started.',
                    '**Why required:** Ensures event PIREPs are tied to actual participants and keeps stats accurate.',
                ],
                'Filing Event PIREPs' => [
                    '**Eligibility:** Only **registered** pilots can file an event PIREP for that specific event.',
                    '**How:** Click **File PIREP** from the event (Grid or Card).',
                    '**What to enter:** Provide **Flight Time** and select a **Flight Type** (the configured multiplier will apply to computed time).',
                    '**Validation:** If the pilot did not register, the system blocks the event PIREP.',
                ],
                'After the Event' => [
                    'Event PIREPs appear in the PIREP list with event context for reporting and leaderboards.',
                ],
                'Admin Tips' => [
                    '**Encourage early registration:** Improves planning for ATC coverage or group routing.',
                    '**Close registration at start time:** Prevents late sign‑ups from filing event PIREPs.',
                    '**Use Discord announcements (optional):** Pair with your Discord Integration to boost turnout.',
                ],
            ],
        ],

        // PIREPs — Overview
        'pirep-overview' => [
            'page_title' => 'PIREP Management',
            'content' => [
                'intro' => 'Manage your pilots’ flight reports (PIREPs) with fast entry, flexible views, and clear permissions for editing or deletion.',
                'What You Can Do' => [
                    '**Browse in two views:** A powerful Grid for bulk operations and a unique Boarding Pass view for quick, visual scanning.',
                    '**Create PIREPs quickly:** Side‑drawer form with route selection, flight time, flight type, and custom fields.',
                    '**Control edits & deletes:** Fine‑grained permissions let you decide who can modify their own vs. everyone’s PIREPs.',
                ],
                'Data Integrity' => [
                    '**Computed Flight Time:** Automatically calculated as **Flight Time × Flight Type Multiplier**.',
                    '**Distance:** Auto‑calculated for the route and displayed with each PIREP.',
                    '**Events:** When a PIREP is tied to an event, you’ll see the **event name** in place of a standalone flight number.',
                ],
            ],
        ],

        // 1) Visibility
        'pirep-visibility' => [
            'page_title' => 'PIREP Visibility',
            'content' => [
                'intro' => 'View PIREPs in two formats—each shows the same core data but supports different workflows.',
                'Grid View' => [
                    '**Purpose:** Bulk review, filtering, searching, and running actions (edit/delete).',
                    '**Columns shown:** **Pilot & Callsign**, **Route** (Flight number or Event name), **Flight Time**, **Flight Type** with **Multiplier**, **Computed Flight Time**, **Distance**, and **Actions**.',
                    '**Filtering & search:** Find PIREPs by pilot, route, date range, or status (as applicable).',
                    '**Actions availability:** **Edit** and **Delete** actions are available **only** in the Grid.',
                ],
                'Boarding Pass View' => [
                    '**Purpose:** At‑a‑glance, visual browsing of recent or relevant PIREPs.',
                    '**Shown details:** **Pilot & Callsign**, **Route/Flight/Event**, **Flight Time**, **Flight Type & Multiplier**, **Computed Flight Time**, and **Distance**.',
                    '**Tip:** Use for briefings or quick review—switch back to Grid when you need to take actions.',
                ],
                'Data Notes' => [
                    '**Computed Flight Time = Flight Time × Multiplier** (from the selected **Flight Type**).',
                    '**Distance** is auto‑calculated from the route, ensuring consistency across reports.',
                ],
            ],
        ],

        // 2) Creation
        'pirep-creation' => [
            'page_title' => 'Creating PIREPs',
            'content' => [
                'intro' => 'Users with the proper permission can create PIREPs directly from the PIREPs page.',
                'How to Create a PIREP' => [
                    '**Open the form:** Click **+ Add PIREP** on the PIREPs page to open the side drawer.',
                    '**Route selection:** Choose a route from the dropdown (pre‑configured routes appear here).',
                    '**Flight Time:** Enter **hours** and **minutes** of actual block/air time per your VA policy.',
                    '**Flight Type:** Select from the configured flight types—the chosen type’s **multiplier** will be used to compute **Computed Flight Time**.',
                    '**Custom Fields:** Any admin‑defined custom fields for PIREPs will appear and must be filled if marked required.',
                    '**Save:** The PIREP is recorded and appears in both Grid and Boarding Pass views.',
                ],
                'Permissions' => [
                    'Only roles with **Create PIREP** permission can see and use the **+ Add PIREP** button.',
                ],
                'Quality Tips' => [
                    '**Be consistent with time entry:** Align on whether to record block time or airborne time.',
                    '**Use Flight Types properly:** Ensure multipliers reflect your VA’s policy (e.g., event bonus, training penalty).',
                ],
            ],
        ],

        // 3) Actions (Edit & Delete)
        'pirep-actions' => [
            'page_title' => 'PIREP Actions (Edit & Delete)',
            'content' => [
                'intro' => 'Edit and Delete are the only actions available for PIREPs, and both are permission‑gated.',
                'Where Actions Are Available' => [
                    '**Grid View only:** Edit/Delete icons are shown in the **Actions** column for each PIREP.',
                ],
                'Edit PIREP' => [
                    '**Permissions:**',
                    '- **edit-own-pirep:** Can edit only PIREPs created by the signed‑in user.',
                    '- **edit-all-pirep:** Can edit any PIREP in the system.',
                    '**What can be edited:** Route, Flight Time, Flight Type, and any applicable custom fields (subject to your VA policy).',
                    '**Audit tip:** Consider logging edits (who/when) to maintain historical accuracy.',
                ],
                'Delete PIREP' => [
                    '**Permissions:**',
                    '- **delete-own-pirep:** Can delete only PIREPs created by the signed‑in user.',
                    '- **delete-all-pirep:** Can delete any PIREP in the system.',
                    '**Effect:** The PIREP is permanently removed from listings and stats. Use with caution.',
                    '**Best practice:** Prefer edits over deletes unless the entry is clearly erroneous or duplicated.',
                ],
            ],
        ],

        // Miscellaneous Overview
        'misc-overview' => [
            'page_title' => 'Miscellaneous Features',
            'content' => [
                'intro' => 'The Miscellaneous section covers various additional features in Rotate VAMS that enhance automation, improve pilot convenience, and integrate with external platforms like Discord. These features operate alongside the main modules to make Virtual Airline operations smoother and more interactive.',
                'Key Highlights' => [
                    '**Automated Rank-Ups:** Pilot progression happens without manual intervention, ensuring a fair and consistent ranking process.',
                    '**Discord PIREP Filing:** Pilots can file regular and event PIREPs directly from Discord via interactive bot commands.',
                    '**Integration Requirements:** Discord features require pilots to register their Discord ID in their VAMS profile for authentication.',
                ],
            ],
        ],

        // 1) Pilot Rank-Up
        'pilot-rank-up' => [
            'page_title' => 'Pilot Rank-Up',
            'content' => [
                'intro' => 'Pilot rank progression in Rotate VAMS is fully automated, ensuring that promotions occur in real-time as pilots accumulate flight hours.',
                'How It Works' => [
                    '**Minimum Hours per Rank:** Admins configure the required minimum hours for each rank from the **Ranks** section in the Settings page.',
                    '**Trigger Points:** Every time a PIREP is **filed**, **updated**, or **deleted**, the system recalculates the pilot’s total hours.',
                    '**Automatic Updates:** If the total hours meet or exceed the requirement for the next rank, the pilot is promoted instantly—no admin action is needed.',
                ],
                'Benefits' => [
                    'Eliminates the need for manual monitoring of pilot hours.',
                    'Ensures consistent application of rank criteria across all pilots.',
                    'Reduces administrative workload, especially for large Virtual Airlines.',
                ],
                'Example' => [
                    'A pilot currently at **First Officer** rank with a requirement of **50 hours** will automatically be promoted to **Captain** once their logged hours reach 50, based on the PIREPs recorded.',
                ],
            ],
        ],

        // 2) File PIREP from Discord
        'file-pirep-discord' => [
            'page_title' => 'Filing a PIREP from Discord',
            'content' => [
                'intro' => 'Pilots can file PIREPs directly from Discord using the Rotate VAMS bot, allowing convenient reporting without logging into the web platform.',
                'Pre-Requisites' => [
                    '**Discord ID Registration:** Each pilot must register their Discord ID in **VAMS → Profile Settings → Discord Integration** section.',
                    '**Channel Access:** The admin/moderator must specify the correct Discord channel for PIREP commands.',
                ],
                'Steps to File a PIREP' => [
                    '1. Type the command **`!rotate pirep`** in the designated Discord channel.',
                    '2. The Rotate VAMS bot will send you a **DM (Direct Message)** with a step-by-step form.',
                    '3. When prompted, enter **Origin ICAO** and **Destination ICAO**.',
                    '4. The bot will list only the routes available for your current rank.',
                    '5. Follow the remaining prompts to complete and submit your PIREP.',
                ],
                'Notes' => [
                    'Routes not meeting your current rank requirements will not appear in the Discord bot’s selection list.',
                    'Ensure your Discord ID is accurate in VAMS to avoid command errors.',
                ],
            ],
        ],

        // 3) File an Event PIREP from Discord
        'file-event-pirep-discord' => [
            'page_title' => 'Filing an Event PIREP from Discord',
            'content' => [
                'intro' => 'Event-specific PIREPs can also be filed through Discord, but they are limited to pilots who registered for the event.',
                'Pre-Requisites' => [
                    '**Discord ID Registration:** Same as for regular PIREPs—must be set in **Profile Settings**.',
                    '**Event Registration:** The pilot must have been registered for the event before it started.',
                    '**Completed Events Only:** The bot will only show events that have finished.',
                ],
                'Steps to File an Event PIREP' => [
                    '1. Type the command **`!rotate pe`** in the designated Discord channel.',
                    '2. The Rotate VAMS bot will send you a **DM (Direct Message)**.',
                    '3. The bot will list only **completed events** for which you are eligible to file.',
                    '4. Follow the interactive prompts to enter **Flight Time**, select **Flight Type**, and confirm submission.',
                ],
                'Tips' => [
                    'Ensure timely registration for events to be eligible for PIREP filing.',
                    'Double-check event details in VAMS before filing to avoid mismatches.',
                ],
            ],
        ],
    ],

    'navigation' => [
        'onboarding' => [
            'title' => 'Onboarding',
            'icon' => 'CogIcon',
            'pages' => [
                ['id' => 'onboarding', 'title' => 'Overview'],
                ['id' => 'roles-permissions', 'title' => 'Roles & Permissions'],
                ['id' => 'ranks', 'title' => 'Ranks'],
                ['id' => 'fleet', 'title' => 'Fleet'],
                ['id' => 'flight-types', 'title' => 'Flight Types'],
                ['id' => 'custom-fields', 'title' => 'Custom Fields'],
                ['id' => 'leaderboard', 'title' => 'Leaderboard'],
                ['id' => 'logo-branding', 'title' => 'Logo & Branding'],
                ['id' => 'discord-integration', 'title' => 'Discord Integration'],
            ],
        ],

        'pilot_management' => [
            'title' => 'Pilot Management',
            'icon' => 'PilotIcon',
            'pages' => [
                ['id' => 'pilot-management', 'title' => 'Overview'],
                ['id' => 'pilot-creation', 'title' => 'Pilot Creation'],
                ['id' => 'pilot-portal-access', 'title' => 'Pilot Portal Access'],
                ['id' => 'pilot-management-actions', 'title' => 'Management Actions'],
                ['id' => 'exporting-pilots', 'title' => 'Exporting Pilots'],
            ],
        ],

        'route_management' => [
            'title' => 'Routes',
            'icon' => 'RouteIcon',
            'pages' => [
                ['id' => 'routes', 'title' => 'Overview'],
                ['id' => 'routes-grid', 'title' => 'Routes Grid'],
                ['id' => 'route-creation', 'title' => 'Creating Routes'],
                ['id' => 'route-actions', 'title' => 'Route Actions'],
                ['id' => 'routes-export', 'title' => 'Exporting Routes'],
                ['id' => 'routes-import', 'title' => 'Mass Importing Routes'],
            ],
        ],

        'events_system' => [
            'title' => 'Events System',
            'icon' => 'CalendarIcon',
            'pages' => [
                ['id' => 'events-overview', 'title' => 'Overview'],
                ['id' => 'events-views', 'title' => 'Visibility & Views'],
                ['id' => 'event-creation', 'title' => 'Creating Events'],
                ['id' => 'event-registration-pireps', 'title' => 'Registration & Event PIREPs'],
            ],
        ],

        'pirep_management' => [
            'title' => 'PIREP Management',
            'icon' => 'DocumentIcon',
            'pages' => [
                ['id' => 'pirep-overview', 'title' => 'Overview'],
                ['id' => 'pirep-visibility', 'title' => 'Visibility'],
                ['id' => 'pirep-creation', 'title' => 'Creating PIREPs'],
                ['id' => 'pirep-actions', 'title' => 'Edit & Delete'],
            ],
        ],

        'miscellaneous' => [
            'title' => 'Miscellaneous',
            'icon' => 'CogIcon',
            'pages' => [
                ['id' => 'misc-overview', 'title' => 'Overview'],
                ['id' => 'pilot-rank-up', 'title' => 'Pilot Rank-Up'],
                ['id' => 'file-pirep-discord', 'title' => 'File PIREP from Discord'],
                ['id' => 'file-event-pirep-discord', 'title' => 'File Event PIREP from Discord'],
            ],
        ],
    ],
];