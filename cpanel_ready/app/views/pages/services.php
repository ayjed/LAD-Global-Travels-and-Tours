<?php
/**
 * Public Services Page View
 */
?>

<!-- Page Hero -->
<section class="page-hero page-hero-packages">
  <div class="container">
    <span class="eyebrow-pill">WHAT WE OFFER</span>
    <h1 class="page-hero-title">Our Travel & Global Mobility Services</h1>
    <p class="page-hero-desc">
      From meticulous visa file preparation and personalized flight consultation to curated international tour packages, discover how we make your journey seamless.
    </p>
  </div>
</section>

<!-- Services Detailed Grid Section -->
<section class="section">
  <div class="container">
    <div style="display: flex; flex-direction: column; gap: var(--space-8);">

      <!-- Service 1: Visa Assistance -->
      <div class="service-card" id="visa" style="padding: var(--space-8); border-left: 6px solid var(--color-primary-light);">
        <div class="service-detail-row">
          <div class="service-detail-icon" style="background: #EFF6FF; color: #1D4ED8; border: 1.5px solid #BFDBFE;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
          </div>
          <div class="service-detail-content">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-3); margin-bottom: var(--space-3);">
              <div>
                <h2 style="font-size: 1.5rem; margin-bottom: 2px;"><a href="/services/visa-assistance" style="color: inherit; text-decoration: none;">1. Visa Assistance & Advisory</a></h2>
                <span class="badge badge-info">Document Advisory</span>
              </div>
              <div style="display: flex; gap: var(--space-2); flex-wrap: wrap;">
                <a href="/services/visa-assistance" class="btn btn-outline btn-sm" style="font-weight: 600;">Full Details →</a>
                <a href="/contact?service=visa" class="btn btn-primary btn-sm">Enquire for Visa Guidance</a>
              </div>
            </div>
            <p class="service-desc" style="font-size: var(--font-size-base); margin-bottom: var(--space-4);">
              Navigating embassy requirements can be complex and stressful. Our experienced visa specialists conduct exhaustive document audits, verify financial statement formats, assist with accurate online form completion, and coordinate official embassy appointment dates for tourist, business, conference, and visitor visas.
            </p>
            <div class="service-notice" style="background-color: var(--color-warning-bg); color: var(--color-warning); font-size: var(--font-size-sm); border: 1px solid rgba(178, 94, 2, 0.2);">
              <strong>Statutory Consular Notice:</strong> Visa issuance is the sole and exclusive prerogative of the respective embassy, consulate, or high commission. LAD Global provides advisory, document checklist verification, and application preparation services to present your application accurately, but will never claim or promise guaranteed visa approvals.
            </div>
          </div>
        </div>
      </div>

      <!-- Service 2: Tour Packages -->
      <div class="service-card" id="tours" style="padding: var(--space-8); border-left: 6px solid var(--color-accent);">
        <div class="service-detail-row">
          <div class="service-detail-icon" style="background: #FEF3C7; color: #B45309; border: 1.5px solid #FDE68A;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
          </div>
          <div class="service-detail-content">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-3); margin-bottom: var(--space-3);">
              <div>
                <h2 style="font-size: 1.5rem; margin-bottom: 2px;"><a href="/services/tour-packages" style="color: inherit; text-decoration: none;">2. Curated International Tour Packages</a></h2>
                <span class="badge badge-bookable">Group & Private Packages</span>
              </div>
              <div style="display: flex; gap: var(--space-2); flex-wrap: wrap;">
                <a href="/services/tour-packages" class="btn btn-outline btn-sm" style="font-weight: 600;">Full Details →</a>
                <a href="/packages" class="btn btn-accent btn-sm">Browse Tour Packages</a>
              </div>
            </div>
            <p class="service-desc" style="font-size: var(--font-size-base); margin-bottom: var(--space-4);">
              We design immersive, hassle-free international holiday packages for families, couples, solo explorers, and corporate teams. Our itineraries include handpicked hotel accommodations, guided cultural and wildlife tours, airport transfers, and transparent per-person rates across Kenya, Rwanda, Ghana, Dubai, China, and destinations across Europe.
            </p>
            <div class="service-notice">
              <strong>Pricing Transparency:</strong> All published package prices are clearly shown in Nigerian Naira (NGN) per person. Direct booking is available with instant Paystack checkout or manual corporate bank transfer.
            </div>
          </div>
        </div>
      </div>

      <!-- Service 3: Flight Booking Assistance -->
      <div class="service-card" id="flights" style="padding: var(--space-8); border-left: 6px solid #2B6CB0;">
        <div class="service-detail-row">
          <div class="service-detail-icon" style="background: #F0F9FF; color: #0284C7; border: 1.5px solid #BAE6FD;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg>
          </div>
          <div class="service-detail-content">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-3); margin-bottom: var(--space-3);">
              <div>
                <h2 style="font-size: 1.5rem; margin-bottom: 2px;"><a href="/services/flight-assistance" style="color: inherit; text-decoration: none;">3. Flight Booking Assistance</a></h2>
                <span class="badge badge-enquiry">Enquiry-Only Service</span>
              </div>
              <div style="display: flex; gap: var(--space-2); flex-wrap: wrap;">
                <a href="/services/flight-assistance" class="btn btn-outline btn-sm" style="font-weight: 600;">Full Details →</a>
                <a href="/contact?service=flights" class="btn btn-primary btn-sm">Request Flight Itinerary Quote</a>
              </div>
            </div>
            <p class="service-desc" style="font-size: var(--font-size-base); margin-bottom: var(--space-4);">
              Finding the best airfare, routing, and luggage allowances across multiple airlines requires expert knowledge. Our consultants research flight options, compare domestic and international carriers, suggest optimal layover schedules, and handle itinerary adjustments or seat requests.
            </p>
            <div class="service-notice">
              <strong>Enquiry-Only Model:</strong> LAD Global does not operate an automated flight booking engine or live ticketing portal. All flight assistance requests are reviewed personally by our consultants to ensure accuracy and competitive pricing.
            </div>
          </div>
        </div>
      </div>

      <!-- Service 4: Hotel Reservations -->
      <div class="service-card" id="hotels" style="padding: var(--space-8); border-left: 6px solid #2B6CB0;">
        <div class="service-detail-row">
          <div class="service-detail-icon" style="background: #EEF2FF; color: #4F46E5; border: 1.5px solid #C7D2FE;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v8h4"/><path d="M18 9h2a2 2 0 0 1 2 2v11h-4"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
          </div>
          <div class="service-detail-content">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-3); margin-bottom: var(--space-3);">
              <div>
                <h2 style="font-size: 1.5rem; margin-bottom: 2px;"><a href="/services/hotel-reservations" style="color: inherit; text-decoration: none;">4. Hotel & Accommodation Advisory</a></h2>
                <span class="badge badge-enquiry">Enquiry-Only Service</span>
              </div>
              <div style="display: flex; gap: var(--space-2); flex-wrap: wrap;">
                <a href="/services/hotel-reservations" class="btn btn-outline btn-sm" style="font-weight: 600;">Full Details →</a>
                <a href="/contact?service=hotels" class="btn btn-primary btn-sm">Request Hotel Recommendations</a>
              </div>
            </div>
            <p class="service-desc" style="font-size: var(--font-size-base); margin-bottom: var(--space-4);">
              Whether you need luxury resort bookings, convenient business travel hotels near conference centers, or safe family apartments abroad, we curate verified properties tailored to your budget and travel preferences.
            </p>
            <div class="service-notice">
              <strong>Enquiry-Only Model:</strong> We verify availability and security directly with hotel partners rather than relying on unverified third-party scrapers.
            </div>
          </div>
        </div>
      </div>

      <!-- Service 5: Study Abroad Advisory -->
      <div class="service-card" id="study" style="padding: var(--space-8); border-left: 6px solid var(--color-primary-light);">
        <div class="service-detail-row">
          <div class="service-detail-icon" style="background: #FAF5FF; color: #7E22CE; border: 1.5px solid #E9D5FF;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          </div>
          <div class="service-detail-content">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-3); margin-bottom: var(--space-3);">
              <div>
                <h2 style="font-size: 1.5rem; margin-bottom: 2px;"><a href="/services/study-abroad" style="color: inherit; text-decoration: none;">5. Study Abroad Advisory</a></h2>
                <span class="badge badge-info">Education Advisory</span>
              </div>
              <div style="display: flex; gap: var(--space-2); flex-wrap: wrap;">
                <a href="/services/study-abroad" class="btn btn-outline btn-sm" style="font-weight: 600;">Full Details →</a>
                <a href="/contact?service=study" class="btn btn-primary btn-sm">Enquire About Study Abroad</a>
              </div>
            </div>
            <p class="service-desc" style="font-size: var(--font-size-base); margin-bottom: var(--space-4);">
              Planning to pursue undergraduate, postgraduate, or diploma programs overseas? We provide guidance on accredited international university options, admission prerequisites, tuition fee expectations, statement of purpose (SOP) reviews, and student visa application checklists.
            </p>
            <div class="service-notice">
              <strong>Admissions Notice:</strong> Academic admission decisions are determined solely by the respective educational institutions; visa decisions remain with national immigration authorities.
            </div>
          </div>
        </div>
      </div>

      <!-- Service 6: Canada PR Guidance -->
      <div class="service-card" id="pr" style="padding: var(--space-8); border-left: 6px solid var(--color-primary-dark);">
        <div class="service-detail-row">
          <div class="service-detail-icon" style="background: #FFF1F2; color: #BE123C; border: 1.5px solid #FECDD3;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/><circle cx="12" cy="12" r="4"/></svg>
          </div>
          <div class="service-detail-content">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-3); margin-bottom: var(--space-3);">
              <div>
                <h2 style="font-size: 1.5rem; margin-bottom: 2px;"><a href="/services/canada-pr" style="color: inherit; text-decoration: none;">6. Canada Permanent Residency (PR) Guidance</a></h2>
                <span class="badge badge-info">Mobility Guidance</span>
              </div>
              <div style="display: flex; gap: var(--space-2); flex-wrap: wrap;">
                <a href="/services/canada-pr" class="btn btn-outline btn-sm" style="font-weight: 600;">Full Details →</a>
                <a href="/contact?service=canada_pr" class="btn btn-primary btn-sm">Consult on Canada PR</a>
              </div>
            </div>
            <p class="service-desc" style="font-size: var(--font-size-base); margin-bottom: var(--space-4);">
              We guide prospective applicants through the prerequisites of Canadian economic immigration programs, including Express Entry (Federal Skilled Worker, Canadian Experience Class) and Provincial Nominee Programs (PNP). Our advisors help you understand CRS point calculations, ECA credential assessments, and required civil documentation.
            </p>
            <div class="service-notice" style="background-color: var(--color-warning-bg); color: var(--color-warning); font-size: var(--font-size-sm); border: 1px solid rgba(178, 94, 2, 0.2);">
              <strong>Legal Disclaimer:</strong> Information provided is advisory in nature and does not constitute formal legal representation. Official immigration determinations are governed strictly by Immigration, Refugees and Citizenship Canada (IRCC).
            </div>
          </div>
        </div>
      </div>

      <!-- Service 7: Citizenship by Investment (CBI) -->
      <div class="service-card" id="cbi" style="padding: var(--space-8); border-left: 6px solid var(--color-accent-hover);">
        <div class="service-detail-row">
          <div class="service-detail-icon" style="background: #FEFCE8; color: #A16207; border: 1.5px solid #FEF08A;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
          </div>
          <div class="service-detail-content">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-3); margin-bottom: var(--space-3);">
              <div>
                <h2 style="font-size: 1.5rem; margin-bottom: 2px;"><a href="/services/citizenship-by-investment" style="color: inherit; text-decoration: none;">7. Citizenship by Investment (CBI) Advisory</a></h2>
                <span class="badge badge-gold">Global Mobility</span>
              </div>
              <div style="display: flex; gap: var(--space-2); flex-wrap: wrap;">
                <a href="/services/citizenship-by-investment" class="btn btn-outline btn-sm" style="font-weight: 600;">Full Details →</a>
                <a href="/contact?service=cbi" class="btn btn-primary btn-sm">CBI Consultation</a>
              </div>
            </div>
            <p class="service-desc" style="font-size: var(--font-size-base); margin-bottom: var(--space-4);">
              Advisory on legitimate government-legislated economic citizenship programs worldwide. We guide high-net-worth individuals and families on qualifying capital thresholds, due diligence processes, and global mobility advantages.
            </p>
            <div class="service-notice">
              <strong>Statutory Compliance:</strong> All programs are subject to verified sovereign government frameworks and statutory background clearance.
            </div>
          </div>
        </div>
      </div>

      <!-- Service 8: Residency by Investment (RBI) -->
      <div class="service-card" id="rbi" style="padding: var(--space-8); border-left: 6px solid var(--color-primary);">
        <div class="service-detail-row">
          <div class="service-detail-icon" style="background: #ECFDF5; color: #047857; border: 1.5px solid #A7F3D0;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          </div>
          <div class="service-detail-content">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-3); margin-bottom: var(--space-3);">
              <div>
                <h2 style="font-size: 1.5rem; margin-bottom: 2px;"><a href="/services/residency-by-investment" style="color: inherit; text-decoration: none;">8. Residency by Investment (RBI) Advisory</a></h2>
                <span class="badge badge-gold">Residency Advisory</span>
              </div>
              <div style="display: flex; gap: var(--space-2); flex-wrap: wrap;">
                <a href="/services/residency-by-investment" class="btn btn-outline btn-sm" style="font-weight: 600;">Full Details →</a>
                <a href="/contact?service=rbi" class="btn btn-primary btn-sm">RBI Consultation</a>
              </div>
            </div>
            <p class="service-desc" style="font-size: var(--font-size-base); margin-bottom: var(--space-4);">
              Strategic guidance for investors seeking European and international Golden Visa programs, residency permits, and family relocation options through real estate investment or capital contributions.
            </p>
            <div class="service-notice">
              <strong>Jurisdictional Notice:</strong> Requirements, stay criteria, and tax implications vary by jurisdiction. We provide initial feasibility reviews.
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Direct Support Banner -->
<section class="section section-subtle text-center">
  <div class="container container-narrow">
    <h2>Need Customized Travel Assistance?</h2>
    <p class="text-muted" style="margin-bottom: var(--space-6);">Connect directly with our team in Lagos for personalized recommendations and prompt quotes.</p>
    <div style="display: flex; gap: var(--space-4); justify-content: center; flex-wrap: wrap;">
      <a href="https://wa.me/2347017597650" class="btn btn-accent btn-lg" target="_blank" rel="noopener">WhatsApp Consultation (07017597650)</a>
      <a href="/contact" class="btn btn-primary btn-lg">Submit Online Enquiry</a>
    </div>
  </div>
</section>
