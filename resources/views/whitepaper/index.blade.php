@extends('layouts.whitepaper')

@section('hero')
    <p class="wp-eyebrow">LIS CMS White Paper</p>
    <p class="wp-hero-sub">Content management architecture, secretary workflows, and LIS publishing controls</p>
    <h1>The administrative backbone for Sangguniang Bayan content, sessions, and document approval</h1>
    <p class="wp-hero-desc">
        This white paper describes the Legislative Information System CMS as the admin back-office for
        deploying local government units (LGUs). It manages council content, SB Secretary workflows,
        session meetings, agendas, and minutes—publishing data to the public LIS portal via REST APIs.
    </p>
    <p class="wp-audience">Documentation for LGU stakeholders, implementers, and technical reviewers</p>
    <div class="wp-meta-grid">
        <div class="wp-meta-card"><div class="label">Platform role</div><div class="value">Admin back-office</div></div>
        <div class="wp-meta-card"><div class="label">Portals</div><div class="value">Staff + secretary</div></div>
        <div class="wp-meta-card"><div class="label">Publishing</div><div class="value">REST API to LIS</div></div>
        <div class="wp-meta-card"><div class="label">Sessions</div><div class="value">Agenda, minutes, live</div></div>
    </div>
@endsection

@section('toc')
    <li><a href="#section-1">1. Platform overview</a></li>
    <li><a href="#section-2">2. System participants</a></li>
    <li><a href="#section-3">3. Content management lifecycle</a></li>
    <li><a href="#section-4">4. Secretary workflow</a></li>
    <li><a href="#section-5">5. Session and document framework</a></li>
    <li><a href="#section-6">6. LIS integration</a></li>
    <li><a href="#section-7">7. System architecture</a></li>
    <li><a href="#section-8">8. Security and access controls</a></li>
    <li><a href="#section-9">9. Risk management framework</a></li>
    <li><a href="#section-10">10. Operational resilience</a></li>
    <li><a href="#section-11">11. Platform role summary</a></li>
@endsection

@section('content')
    <section id="section-1">
        <h2>1. Platform overview</h2>
        <p class="section-lead">Role in municipal legislative administration</p>
        <p>
            LIS CMS operates as the authoritative admin platform for the deploying LGU,
            connecting staff content management, SB Secretary document review, session meeting administration,
            and public API feeds consumed by the Legislative Information System.
        </p>
        <h3>Core platform functions</h3>
        <ul>
            <li>CRUD for members, standing committees, district assignments, organization chart, and barangay officials.</li>
            <li>Photo journals, galleries, and calendar events for public display on LIS.</li>
            <li>SB Secretary module: document review, session meetings, agenda, minutes PDF, remarks, live session.</li>
            <li>Public API routes under <code>/api/*</code> for LIS proxy and direct consumption.</li>
            <li>User management with role-based access to modules.</li>
        </ul>
    </section>

    <section id="section-2">
        <h2>2. System participants</h2>
        <p class="section-lead">Distinct roles in content and session administration</p>
        <h3>CMS administrators</h3>
        <p>Manage council master data, users, photo journals, organization structure, and general content modules.</p>
        <h3>SB Secretary</h3>
        <p>Review forwarded member documents, assign sessions, build agendas, generate minutes, and configure live sessions.</p>
        <h3>LIS (API consumer)</h3>
        <p>Public portal fetches CMS content via REST APIs and storage proxy for consistent delivery to citizens.</p>
        <h3>DMS integration</h3>
        <p>Published legislative documents are stored in DMS; LIS report pages consume DMS separately from CMS content APIs.</p>
    </section>

    <section id="section-3">
        <h2>3. Content management lifecycle</h2>
        <p class="section-lead">From staff encoding to public API publication</p>
        <div class="wp-flow">
            <span class="wp-flow-item">Staff encoding</span>
            <span class="wp-flow-arrow">→</span>
            <span class="wp-flow-item">Validation</span>
            <span class="wp-flow-arrow">→</span>
            <span class="wp-flow-item">Storage</span>
            <span class="wp-flow-arrow">→</span>
            <span class="wp-flow-item">API publish</span>
            <span class="wp-flow-arrow">→</span>
            <span class="wp-flow-item">LIS display</span>
        </div>
        <div class="wp-steps">
            <div class="wp-step"><span class="step-num">1</span><div><h4>Content creation</h4><p>Authorized staff create or update members, committees, events, and media through admin modules.</p></div></div>
            <div class="wp-step"><span class="step-num">2</span><div><h4>Review and approval</h4><p>Content is validated before being exposed via public API endpoints.</p></div></div>
            <div class="wp-step"><span class="step-num">3</span><div><h4>API availability</h4><p>Routes such as <code>/api/members</code>, <code>/api/photo-journals</code>, and <code>/api/calendar-event</code> serve LIS.</p></div></div>
            <div class="wp-step"><span class="step-num">4</span><div><h4>Public visibility</h4><p>LIS renders council information for citizens using CMS-sourced data.</p></div></div>
        </div>
    </section>

    <section id="section-4">
        <h2>4. Secretary workflow</h2>
        <p class="section-lead">Document review and session administration</p>
        <p>Secretary routes under <code>/secretary/*</code> implement the operational core for council meetings:</p>
        <ul>
            <li><strong>Documents</strong> — review forwarded member documents (forwarded, approved states).</li>
            <li><strong>Session meetings</strong> — CRUD for session meetings with agenda and packet generation.</li>
            <li><strong>Agenda</strong> — reorder agenda items, attach documents to sessions.</li>
            <li><strong>Minutes</strong> — template, PDF generation, and review workflows.</li>
            <li><strong>Remarks</strong> — session document remarks for members viewing in LIS.</li>
            <li><strong>Live session</strong> — settings for live stream integration on the public portal.</li>
        </ul>
    </section>

    <section id="section-5">
        <h2>5. Session and document framework</h2>
        <p class="section-lead">Data models linking members, documents, and sessions</p>
        <div class="wp-data-cards">
            <div class="wp-data-card"><h4>MemberDocument</h4><p>SB member submissions with status: draft, forwarded, approved.</p></div>
            <div class="wp-data-card"><h4>SessionMeeting</h4><p>Scheduled council sessions with metadata for agendas and minutes.</p></div>
            <div class="wp-data-card"><h4>SessionDocument</h4><p>Links documents to sessions with agenda ordering.</p></div>
            <div class="wp-data-card"><h4>Session remarks</h4><p>Annotations visible to members on session documents in LIS.</p></div>
        </div>
    </section>

    <section id="section-6">
        <h2>6. LIS integration</h2>
        <p class="section-lead">CORS, APIs, and cross-system connectivity</p>
        <ul>
            <li>Public API routes expose members, committees, assignments, organization, photo journals, calendar, barangay officials, and galleries.</li>
            <li>CORS headers configured for LIS origin (<code>CORS_ALLOWED_ORIGIN</code>).</li>
            <li>LIS uses server-side proxy and <code>cms_mysql</code> connection for session data where applicable.</li>
        </ul>
        @if($lisUrl || $dmsUrl)
        <p class="wp-cross-links">
            Related documentation:
            @if($lisUrl)<a href="{{ $lisUrl }}/whitepaper">LIS White Paper</a>@endif
            @if($lisUrl && $dmsUrl) · @endif
            @if($dmsUrl)<a href="{{ $dmsUrl }}/whitepaper">DMS White Paper</a>@endif
        </p>
        @endif
    </section>

    <section id="section-7">
        <h2>7. System architecture</h2>
        <p class="section-lead">Application and data structure</p>
        <h3>Presentation layer</h3>
        <p>Blade admin UI with Bootstrap, DataTables, and Vite-compiled assets for staff workflows.</p>
        <h3>Application layer</h3>
        <p>Laravel 11 controllers, DomPDF for minutes, secretary and content controllers, CORS middleware.</p>
        <h3>Data layer</h3>
        <p>MySQL tables for members, sessions, documents, users, roles, and media storage.</p>
        <div class="wp-arch-grid">
            <div class="wp-arch-item">Members</div>
            <div class="wp-arch-item">Committees</div>
            <div class="wp-arch-item">Secretary</div>
            <div class="wp-arch-item">Sessions</div>
            <div class="wp-arch-item">Minutes PDF</div>
            <div class="wp-arch-item">Public API</div>
        </div>
    </section>

    <section id="section-8">
        <h2>8. Security and access controls</h2>
        <p class="section-lead">Governance and access integrity</p>
        <ul>
            <li>Laravel UI authentication with registration disabled.</li>
            <li>Auth middleware on all admin routes; secretary routes role-gated.</li>
            <li>CSRF protection on web forms.</li>
            <li>Public API routes intentionally unauthenticated for LIS consumption; scope limited to read-only feeds.</li>
        </ul>
    </section>

    <section id="section-9">
        <h2>9. Risk management framework</h2>
        <h3>Operational risk</h3>
        <p>Document approval queues and session status tracking reduce processing errors.</p>
        <h3>Access risk</h3>
        <p>Staff accounts with module-level responsibilities; secretary functions restricted to authorized roles.</p>
        <h3>Integration risk</h3>
        <p>LIS dependency on CMS APIs documented; CORS and URL configuration validated per deployment.</p>
    </section>

    <section id="section-10">
        <h2>10. Operational resilience</h2>
        <p>
            CMS availability directly affects LIS public content and session visibility. Recovery procedures include
            database backups, application logs, and coordinated restoration with LIS proxy configuration.
        </p>
    </section>

    <section id="section-11">
        <h2>11. Platform role summary</h2>
        <h3>LIS CMS, in summary</h3>
        <ul>
            <li>Central admin platform for Sangguniang Bayan content and secretary operations.</li>
            <li>Publishes council data to LIS via REST APIs.</li>
            <li>Manages full session lifecycle from document intake to minutes.</li>
            <li>White-label ready for municipal and city deployments without per-client code forks.</li>
        </ul>
    </section>
@endsection

@section('cta')
    <h2>Ready to evaluate LIS CMS for your LGU?</h2>
    <p>Prepare council structure, secretary workflows, and LIS integration requirements for a rollout design workshop.</p>
    <div class="wp-cta-buttons">
        <a href="{{ $loginRoute }}" class="btn-primary-wp">{{ $loginLabel }}</a>
        <a href="{{ route('whitepaper') }}" class="btn-outline-wp">Back to white paper</a>
    </div>
@endsection
