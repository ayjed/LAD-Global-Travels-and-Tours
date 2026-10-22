<?php

declare(strict_types=1);

namespace App\Services;

/**
 * LAD GLOBAL TRAVELS AND TOURS — TRAVEL SERVICE CATALOG
 * Single Source of Truth (SOT) for the 8 core travel and mobility services.
 * Provides structured metadata, rich descriptions, process workflows,
 * required documentation checklists, statutory consular notices, and service-specific FAQs.
 */
class TravelServiceCatalog
{
    /**
     * Return all 8 services indexed by canonical slug.
     *
     * @return array<string, array>
     */
    public static function all(): array
    {
        return [
            'visa-assistance' => [
                'id' => 1,
                'slug' => 'visa-assistance',
                'aliases' => ['visa', 'visa-advisory'],
                'short_title' => 'Visa Assistance',
                'title' => 'Visa Assistance & Advisory',
                'eyebrow' => 'Document Audit & Advisory',
                'badge' => 'Document Advisory',
                'badge_class' => 'badge-info',
                'cover_image' => '/assets/images/service-visa.jpg',
                'icon_svg' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>',
                'tagline' => 'Professional document audit, checklist compliance, and consular submission guidance for tourist, business, student, and family visas worldwide.',
                'summary' => 'Complete document checklist preparation, application review, embassy appointment scheduling for business, tourist, conference, and student visas.',
                'overview' => [
                    'Navigating international visa requirements is often the most demanding aspect of foreign travel. Consular officers evaluate applications based on strict evidentiary benchmarks, financial turnover stability, demonstrated ties to Nigeria, and flawless checklist compliance. At LAD Global Travels and Tours, our dedicated visa specialists conduct exhaustive, multi-point document reviews to ensure your application meets official consular criteria and avoids preventable clerical rejections.',
                    'From compiling business registration documents (CAC), tax clearance certificates, and certified bank statements to scheduling biometric captures on official embassy portals (such as VFS Global and TLScontact), we provide end-to-end advisory tailored to your specific destination across the UK, US, Canada, Schengen Europe, China, and Africa.'
                ],
                'highlights' => [
                    [
                        'title' => 'Exhaustive Document Audit',
                        'desc' => 'In-depth pre-screening of employment letters, CAC papers, hotel bookings, and travel insurance to ensure consistency and alignment with consular guidelines.'
                    ],
                    [
                        'title' => 'Official Appointment Scheduling',
                        'desc' => 'Active monitoring and assistance with securing biometric capture and interview dates on authorized embassy portals.'
                    ],
                    [
                        'title' => 'Financial Statement Verification',
                        'desc' => 'Review of 6-month certified bank statements, turnover consistency, sponsorship affidavits, and proof of funds formatting.'
                    ],
                    [
                        'title' => 'Refusal Profile Assessment',
                        'desc' => 'Analytical evaluation of previous visa refusals, addressing former reasons for rejection, and structuring corrective documentation.'
                    ]
                ],
                'process_steps' => [
                    [
                        'step' => '1',
                        'title' => 'Initial Assessment & Strategy',
                        'desc' => 'We review your travel purpose, destination, travel history, and category (tourist, business, conference, or student) to formulate an optimal application plan.'
                    ],
                    [
                        'step' => '2',
                        'title' => 'Checklist & Document Audit',
                        'desc' => 'You receive a customized document checklist. Our consultants scrutinize every file, affidavit, and statement for compliance before submission.'
                    ],
                    [
                        'step' => '3',
                        'title' => 'Application Packaging & Appointment',
                        'desc' => 'We assist with precise online form completion and coordinate your biometric interview appointment booking on official embassy systems.'
                    ],
                    [
                        'step' => '4',
                        'title' => 'Interview Briefing & Follow-Up',
                        'desc' => 'We provide thorough interview prep guidance, organize your file into standard consular tabulations, and track your application status.'
                    ]
                ],
                'checklist' => [
                    'International Passport valid for at least 6 months with minimum 2 blank pages',
                    'Recent passport-sized photographs meeting destination biometric specifications',
                    'Proof of employment (work ID, introduction letter, payslips) or CAC business registration',
                    '6 months certified bank statements with bank reference letter',
                    'Confirmed flight reservation and hotel accommodation voucher',
                    'Invitation letter or conference registration (for business/conference visas)'
                ],
                'disclaimer' => 'Visa issuance is the sole and exclusive prerogative of the respective sovereign embassy, consulate, or high commission. Under statutory international law and Nigerian consumer protection guidelines, LAD Global Travels provides advisory, document checklist verification, and application preparation services, but will never claim, promise, or imply guaranteed visa approvals.',
                'faqs' => [
                    [
                        'q' => 'Can you guarantee that my visa will be approved?',
                        'a' => 'No. No travel agency, consultant, or third party has the legal authority to guarantee visa approval. Embassies and consular officers alone make all visa determinations. Our role is strictly advisory—ensuring your file avoids omissions, false statements, and format errors.'
                    ],
                    [
                        'q' => 'How early before my intended travel date should I apply?',
                        'a' => 'We strongly recommend starting at least 6 to 12 weeks before your intended departure date. This allows adequate time for document gathering, bank statement certification, appointment booking, and embassy processing.'
                    ],
                    [
                        'q' => 'What happens if I have had a previous visa refusal?',
                        'a' => 'A previous refusal does not permanently prevent you from traveling. We conduct a thorough refusal profile review to understand the specific grounds of refusal and prepare adequate evidentiary remedies.'
                    ]
                ],
                'enquiry_code' => 'visa',
                'cta_text' => 'Enquire for Visa Guidance',
                'cta_url' => '/contact?service=visa',
                'whatsapp_msg' => 'Hello LAD Global Travels, I would like to enquire about Visa Assistance for my upcoming travel.'
            ],

            'tour-packages' => [
                'id' => 2,
                'slug' => 'tour-packages',
                'aliases' => ['tours', 'curated-tours'],
                'short_title' => 'Tour Packages',
                'title' => 'Curated International Tour Packages',
                'eyebrow' => 'Group & Private Travel',
                'badge' => 'Group & Private Packages',
                'badge_class' => 'badge-bookable',
                'cover_image' => '/assets/images/service-tours.jpg',
                'icon_svg' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>',
                'tagline' => 'Handcrafted international holiday packages across Kenya, Rwanda, Ghana, Dubai, China, and Europe with transparent per-person pricing.',
                'summary' => 'Tailored holiday itineraries across Kenya, Rwanda, Ghana, Dubai, China, and Europe with transparent per-person pricing.',
                'overview' => [
                    'Discover the world with confidence and comfort. LAD Global Travels designs immersive, hassle-free international holiday packages that blend guided cultural exploration, wildlife safaris, and beach relaxation. Each package is thoughtfully assembled with handpicked 3-star to 5-star accommodations, certified local tour guides, and scheduled airport transfers.',
                    'Whether you are planning a memorable family vacation, a romantic couple\'s getaway, a corporate retreat, or a solo adventure, we offer scheduled group departures as well as fully customized bespoke private itineraries.'
                ],
                'highlights' => [
                    [
                        'title' => 'Transparent Per-Person Pricing',
                        'desc' => 'All published packages are clearly priced in Nigerian Naira (NGN) with zero hidden resort fees or surprise arrival charges.'
                    ],
                    [
                        'title' => 'Verified Quality Accommodations',
                        'desc' => 'Handpicked 3 to 5-star hotels inspected for safety, cleanliness, guest comfort, and strategic central location.'
                    ],
                    [
                        'title' => 'Comprehensive Day-by-Day Itineraries',
                        'desc' => 'Carefully balanced schedules combining must-see landmarks with authentic local dining and leisure time.'
                    ],
                    [
                        'title' => 'Instant & Bank Payment Options',
                        'desc' => 'Secure instant online card checkout powered by Paystack or direct manual transfer to our verified Zenith Bank corporate account.'
                    ]
                ],
                'process_steps' => [
                    [
                        'step' => '1',
                        'title' => 'Browse & Choose Destination',
                        'desc' => 'Explore our catalog of published packages across Kenya, Rwanda, Dubai, Ghana, Japan, and European destinations.'
                    ],
                    [
                        'step' => '2',
                        'title' => 'Select Dates & Inclusions',
                        'desc' => 'Review day-by-day itineraries, inclusions, exclusions, and select scheduled group or private custom travel dates.'
                    ],
                    [
                        'step' => '3',
                        'title' => 'Secure Booking & Payment',
                        'desc' => 'Book online via our secure form with Paystack or direct corporate bank transfer to our verified Zenith Bank account.'
                    ],
                    [
                        'step' => '4',
                        'title' => 'Departure Briefing & Vouchers',
                        'desc' => 'Receive your official travel voucher, hotel confirmation, local guide contacts, and comprehensive packing checklist.'
                    ]
                ],
                'checklist' => [
                    '3-star to 5-star hotel accommodations with daily breakfast',
                    'Airport pick-up and drop-off in private air-conditioned vehicles',
                    'Guided city tours and safari game drives with licensed English-speaking guides',
                    'All listed attraction entry permits and national park conservation fees',
                    'Optional international flight add-on assistance tailored to your departure airport'
                ],
                'disclaimer' => 'All published package prices are clearly shown in Nigerian Naira (NGN) per person. International flight tickets are provided on an assisted add-on basis unless explicitly stated as flight-inclusive.',
                'faqs' => [
                    [
                        'q' => 'Are international flight tickets included in package prices?',
                        'a' => 'Ground arrangements (hotels, tours, transfers) form the core package. Flight tickets fluctuate daily based on booking date, so we provide transparent airline ticket add-ons upon reservation.'
                    ],
                    [
                        'q' => 'Can itineraries be customized for private groups or couples?',
                        'a' => 'Yes. We frequently organize customized private departures for families, honeymoon couples, and corporate teams tailored to your specific schedule and budget.'
                    ],
                    [
                        'q' => 'What payment methods are supported for tour packages?',
                        'a' => 'We accept Paystack online checkout (debit/credit card, bank transfer) and direct manual transfer to our verified Zenith Bank corporate account. Official receipts are issued immediately upon reconciliation.'
                    ]
                ],
                'enquiry_code' => 'tours',
                'cta_text' => 'Browse Tour Packages',
                'cta_url' => '/packages',
                'whatsapp_msg' => 'Hello LAD Global Travels, I would like to enquire about your international tour packages.'
            ],

            'flight-assistance' => [
                'id' => 3,
                'slug' => 'flight-assistance',
                'aliases' => ['flights', 'flight-booking'],
                'short_title' => 'Flight Assistance',
                'title' => 'Flight Booking Assistance',
                'eyebrow' => 'Assisted Route Advisory',
                'badge' => 'Enquiry-Only Service',
                'badge_class' => 'badge-enquiry',
                'cover_image' => '/assets/images/service-flights.jpg',
                'icon_svg' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg>',
                'tagline' => 'Personalized domestic and international flight search, schedule comparison across international carriers, and routing advice.',
                'summary' => 'Personalized domestic and international flight search, fare comparison across carriers, and routing advice.',
                'overview' => [
                    'Finding the ideal international flight requires navigating complex airline fare structures, layover durations, transit visa rules, and baggage allowances. LAD Global Travels provides personalized flight assistance rather than relying on impersonal automated flight booking engines. Our experienced travel consultants manually verify flight availability, compare multi-carrier routes, and secure the most cost-effective and convenient flight schedules.',
                    'We assist solo travelers, families, and corporate executives with flight itinerary quotes, seat selection, special meal requests, date change amendments, and ticket reissues.'
                ],
                'highlights' => [
                    [
                        'title' => 'Personalized Carrier Comparison',
                        'desc' => 'Direct comparison of leading carriers including Qatar Airways, Emirates, Ethiopian Airlines, British Airways, Air France, and Delta.'
                    ],
                    [
                        'title' => 'Transit & Layover Advisory',
                        'desc' => 'Guidance on transit visa requirements and connection durations to prevent missed flight connections.'
                    ],
                    [
                        'title' => 'Baggage Allowance Audits',
                        'desc' => 'Clear guidance on piece-concept versus weight-concept baggage allowances for each leg of your journey.'
                    ],
                    [
                        'title' => 'Schedule Amendments & Re-booking',
                        'desc' => 'Dedicated consultant assistance when you need to change travel dates, manage cancellations, or request refunds.'
                    ]
                ],
                'process_steps' => [
                    [
                        'step' => '1',
                        'title' => 'Submit Route & Travel Dates',
                        'desc' => 'Provide your departure city, destination, preferred dates, and number of passengers via our enquiry form or WhatsApp.'
                    ],
                    [
                        'step' => '2',
                        'title' => 'Carrier Comparison & Options',
                        'desc' => 'Our consultants analyze airline schedules and present 2-3 optimal itinerary options with transparent fare breakdowns.'
                    ],
                    [
                        'step' => '3',
                        'title' => 'Passenger Details Verification',
                        'desc' => 'You select your preferred option; we verify full passenger passport names and dates of birth to eliminate ticketing errors.'
                    ],
                    [
                        'step' => '4',
                        'title' => 'Issuance & E-Ticket Delivery',
                        'desc' => 'Upon confirmed payment, your official electronic ticket is issued and delivered directly to your email and WhatsApp.'
                    ]
                ],
                'checklist' => [
                    'Accurate passenger passport full names matching the biometric page',
                    'Passport validity of at least 6 months for international departures',
                    'Preferred travel dates, destination airport codes, and flexibility windows',
                    'Specific requests (infant bassinet, wheelchair assistance, special dietary meals)'
                ],
                'disclaimer' => 'Enquiry-Only Model: LAD Global Travels does not operate an automated self-service booking engine. All flight reservations are personally audited and issued by our travel consultants to ensure competitive pricing and fare accuracy.',
                'faqs' => [
                    [
                        'q' => 'Can I search and book flights directly on your website?',
                        'a' => 'No. We operate an assisted consultation model. Rather than an impersonal automated booking engine, our consultants evaluate routes manually to find the best value and avoid common booking mistakes.'
                    ],
                    [
                        'q' => 'Can you help with date changes or flight rescheduling?',
                        'a' => 'Yes. If your ticket was issued through our agency, our consultants can assist in calculating airline change fees, checking seat availability for new dates, and processing schedule amendments.'
                    ]
                ],
                'enquiry_code' => 'flights',
                'cta_text' => 'Request Flight Itinerary Quote',
                'cta_url' => '/contact?service=flights',
                'whatsapp_msg' => 'Hello LAD Global Travels, I would like to request a flight quote.'
            ],

            'hotel-reservations' => [
                'id' => 4,
                'slug' => 'hotel-reservations',
                'aliases' => ['hotels', 'hotel-advisory'],
                'short_title' => 'Hotel Reservations',
                'title' => 'Hotel & Accommodation Advisory',
                'eyebrow' => 'Handpicked Lodging',
                'badge' => 'Enquiry-Only Service',
                'badge_class' => 'badge-enquiry',
                'cover_image' => '/assets/images/service-hotels.jpg',
                'icon_svg' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v8h4"/><path d="M18 9h2a2 2 0 0 1 2 2v11h-4"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>',
                'tagline' => 'Handpicked accommodations matching your budget, verified guest reviews, and safe booking coordination worldwide.',
                'summary' => 'Handpicked accommodations matching your budget, verified reviews, and safe booking coordination.',
                'overview' => [
                    'Where you stay defines your travel experience. Online hotel aggregators frequently display misleading photographs, hidden utility charges, or unsafe locations far from key city landmarks. LAD Global Travels eliminates uncertainty by curating verified, reputable accommodations worldwide.',
                    'We verify property hygiene credentials, verified traveler feedback, neighborhood security, and transit accessibility. From luxury beachfront resorts in Zanzibar to central corporate hotels in London, Dubai, and Kigali, we ensure safe, high-comfort lodging.'
                ],
                'highlights' => [
                    [
                        'title' => 'Verified Property Standards',
                        'desc' => 'We recommend properties with proven guest reviews, rigorous hygiene standards, and dependable round-the-clock security.'
                    ],
                    [
                        'title' => 'Strategic Central Locations',
                        'desc' => 'Accommodations selected for convenience—close to transit hubs, conference centers, or key tourist landmarks.'
                    ],
                    [
                        'title' => 'Group & Corporate Contracting',
                        'desc' => 'Preferential booking coordination for corporate delegations, family reunions, and wedding parties.'
                    ],
                    [
                        'title' => 'Transparent Inclusions',
                        'desc' => 'Clear itemization of breakfast inclusions, local municipality tourist taxes, and cancellation policies.'
                    ]
                ],
                'process_steps' => [
                    [
                        'step' => '1',
                        'title' => 'Share Your Lodging Preferences',
                        'desc' => 'Tell us your destination, dates, budget category (3-star, 4-star, 5-star, or serviced apartment), and party size.'
                    ],
                    [
                        'step' => '2',
                        'title' => 'Curated Options Proposal',
                        'desc' => 'We provide 2-3 verified properties matching your requirements with photos, amenities, and transparent pricing.'
                    ],
                    [
                        'step' => '3',
                        'title' => 'Direct Reservation Locking',
                        'desc' => 'We coordinate directly with hotel management to lock in your rooms and confirm any special requests.'
                    ],
                    [
                        'step' => '4',
                        'title' => 'Official Voucher Delivery',
                        'desc' => 'You receive official booking confirmation vouchers with check-in instructions and contact details prior to departure.'
                    ]
                ],
                'checklist' => [
                    'Check-in and check-out dates with room type preference (single, double, suite)',
                    'Exact guest count including children\'s ages for bedding arrangements',
                    'Specific amenity needs (complimentary Wi-Fi, executive lounge, pool, kitchenette)',
                    'Location preference (city centre, beach front, near airport, or business district)'
                ],
                'disclaimer' => 'Enquiry-Only Model: We verify availability and security directly with hotel partners rather than relying on unverified third-party scraping portals.',
                'faqs' => [
                    [
                        'q' => 'Are breakfast and local taxes included in the hotel quote?',
                        'a' => 'Yes. We provide clear, itemized quotes indicating whether daily breakfast, VAT, and city tourism levies are included so you face no arrival surprises.'
                    ],
                    [
                        'q' => 'Can you arrange serviced apartments for extended stays?',
                        'a' => 'Yes. We coordinate fully furnished serviced apartments with equipped kitchens and laundry facilities for extended business assignments or medical trips.'
                    ]
                ],
                'enquiry_code' => 'hotels',
                'cta_text' => 'Request Hotel Recommendations',
                'cta_url' => '/contact?service=hotels',
                'whatsapp_msg' => 'Hello LAD Global Travels, I need assistance with hotel reservations.'
            ],

            'study-abroad' => [
                'id' => 5,
                'slug' => 'study-abroad',
                'aliases' => ['study', 'study-abroad-advisory'],
                'short_title' => 'Study Abroad Advisory',
                'title' => 'Study Abroad Advisory',
                'eyebrow' => 'International Education',
                'badge' => 'Education Advisory',
                'badge_class' => 'badge-info',
                'cover_image' => '/assets/images/service-study.jpg',
                'icon_svg' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
                'tagline' => 'Guidance on accredited university admissions, tuition prerequisites, SOP reviews, and student visa applications.',
                'summary' => 'Accredited international university options, admission prerequisites, tuition guidance, and student visa checklists.',
                'overview' => [
                    'Pursuing higher education overseas is a life-transforming investment. However, selecting the right university, navigating admission deadlines, drafting compelling Statements of Purpose (SOP), and complying with strict student visa regulations can be challenging without professional guidance.',
                    'LAD Global Travels provides structured education advisory for students seeking undergraduate, postgraduate, and diploma qualifications across the United Kingdom, Canada, the United States, Ireland, Australia, and accredited European institutions.'
                ],
                'highlights' => [
                    [
                        'title' => 'Course & Institution Matching',
                        'desc' => 'Aligning your academic transcripts, career ambitions, and budget with accredited universities and recognized faculties.'
                    ],
                    [
                        'title' => 'Statement of Purpose (SOP) Audit',
                        'desc' => 'Comprehensive review and structural editing of your academic essay to clearly demonstrate genuine student intent.'
                    ],
                    [
                        'title' => 'Tuition & Financial Advisory',
                        'desc' => 'Guidance on tuition deposit requirements, scholarship eligibility, and international student bank transfer protocols.'
                    ],
                    [
                        'title' => 'Student Visa Filing Checklist',
                        'desc' => 'Meticulous compilation of required financial proof, CAS / I-20 / PAL forms, tuberculosis testing, and biometric scheduling.'
                    ]
                ],
                'process_steps' => [
                    [
                        'step' => '1',
                        'title' => 'Academic Profile Review',
                        'desc' => 'We assess WAEC, NECO, Bachelor\'s transcripts, English test requirements, and target study destinations.'
                    ],
                    [
                        'step' => '2',
                        'title' => 'University Application Submission',
                        'desc' => 'Shortlisting suitable institutions and submitting complete, error-free admission applications.'
                    ],
                    [
                        'step' => '3',
                        'title' => 'Offer Acceptance & Documentation',
                        'desc' => 'Guidance on deposit payments, securing unconditional offer letters, and obtaining CAS or PAL documentation.'
                    ],
                    [
                        'step' => '4',
                        'title' => 'Student Visa Filing & Pre-Departure',
                        'desc' => 'Thorough assembly of Proof of Funds, biometric appointment booking, and comprehensive pre-departure briefing.'
                    ]
                ],
                'checklist' => [
                    'Academic certificates and official transcripts (WAEC/NECO/Degree)',
                    'Curriculum Vitae (CV) and two academic/professional reference letters',
                    'Statement of Purpose (SOP) outlining your study rationale and career goals',
                    'Valid International Passport with minimum 12 months validity',
                    'Proof of financial sponsorship and certified 6 months bank statements'
                ],
                'disclaimer' => 'Admissions Notice: Academic admission decisions are determined solely by the respective educational institutions; student visa determinations remain the exclusive prerogative of national immigration authorities.',
                'faqs' => [
                    [
                        'q' => 'Do I need IELTS to study in the UK or Canada?',
                        'a' => 'Requirements vary by institution. Many UK universities accept WAEC or NECO English grades (C6 or higher) in lieu of IELTS, while others require specific language tests. We help you identify institutions that match your profile.'
                    ],
                    [
                        'q' => 'How much Proof of Funds is required for a student visa?',
                        'a' => 'Proof of funds depends on destination country guidelines (such as UKVI living expense benchmarks or IRCC cost of living criteria) plus outstanding first-year tuition. We calculate exact requirements before you apply.'
                    ]
                ],
                'enquiry_code' => 'study',
                'cta_text' => 'Enquire About Study Abroad',
                'cta_url' => '/contact?service=study',
                'whatsapp_msg' => 'Hello LAD Global Travels, I would like to consult on Study Abroad Advisory.'
            ],

            'canada-pr' => [
                'id' => 6,
                'slug' => 'canada-pr',
                'aliases' => ['canada-pr-guidance', 'pr'],
                'short_title' => 'Canada PR Guidance',
                'title' => 'Canada Permanent Residency (PR) Guidance',
                'eyebrow' => 'Economic Immigration Advisory',
                'badge' => 'Mobility Guidance',
                'badge_class' => 'badge-info',
                'cover_image' => '/assets/images/service-canada-pr.jpg',
                'icon_svg' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/><circle cx="12" cy="12" r="4"/></svg>',
                'tagline' => 'Advisory on Express Entry (FSW, CEC), Provincial Nominee Programs (PNP), CRS point calculations, and credential assessment advisory.',
                'summary' => 'Express Entry (FSW, CEC), Provincial Nominee Programs (PNP), CRS point calculations, and credential assessment advisory.',
                'overview' => [
                    'Canada\'s economic immigration programs offer qualified professionals and their families a direct path to Canadian Permanent Residency. Navigating the Comprehensive Ranking System (CRS), Educational Credential Assessment (ECA), language benchmarks (IELTS General / TEF), and Provincial Nominee Programs (PNP) requires structured preparation.',
                    'LAD Global Travels provides advisory support to help prospective applicants understand qualifying criteria, calculate CRS scores accurately, select strategic occupational categories (NOC/TEER), and organize supporting documents.'
                ],
                'highlights' => [
                    [
                        'title' => 'CRS Score Optimization',
                        'desc' => 'Realistic evaluation based on age, education level, language exam scores, and foreign work experience history.'
                    ],
                    [
                        'title' => 'ECA Credential Advisory',
                        'desc' => 'Guidance on submitting academic transcripts to designated assessment organizations like WES, IQAS, or ICES.'
                    ],
                    [
                        'title' => 'PNP Pathway Feasibility',
                        'desc' => 'Review of Canadian provincial nomination streams with targeted in-demand occupation lists matching your profile.'
                    ],
                    [
                        'title' => 'Proof of Settlement Funds',
                        'desc' => 'Clear guidance on official settlement fund thresholds required by IRCC for primary applicants and dependents.'
                    ]
                ],
                'process_steps' => [
                    [
                        'step' => '1',
                        'title' => 'Eligibility Assessment',
                        'desc' => 'Evaluation of age, academic credentials, work experience history, and language test readiness.'
                    ],
                    [
                        'step' => '2',
                        'title' => 'ECA & Language Exam Prep',
                        'desc' => 'Step-by-step guidance on processing your Educational Credential Assessment and preparing for IELTS General.'
                    ],
                    [
                        'step' => '3',
                        'title' => 'Express Entry & PNP Profiling',
                        'desc' => 'Assistance with structuring your profile in the Express Entry pool and monitoring category-based invitation rounds.'
                    ],
                    [
                        'step' => '4',
                        'title' => 'Post-ITA Documentation Audit',
                        'desc' => 'Auditing reference letters, police clearance certificates, medical records, and proof of funds upon receiving an Invitation to Apply (ITA).'
                    ]
                ],
                'checklist' => [
                    'WES or recognized Educational Credential Assessment (ECA) report',
                    'IELTS General Training test results meeting CLB 7 or higher benchmarks',
                    'Comprehensive employment reference letters with specific NOC/TEER duties',
                    'Valid International Passport for all family members included in the profile',
                    'Proof of unencumbered settlement funds conforming to IRCC guidelines'
                ],
                'disclaimer' => 'Legal Disclaimer: Information provided is advisory in nature and does not constitute formal legal representation. Official immigration determinations and invitations to apply are governed strictly by Immigration, Refugees and Citizenship Canada (IRCC).',
                'faqs' => [
                    [
                        'q' => 'What is the minimum CRS score needed for Canada PR?',
                        'a' => 'CRS cutoff scores fluctuate with each invitation round and category (e.g. STEM, healthcare, trade occupations, French language). We evaluate your profile to identify strategies to maximize your points or pursue Provincial Nominee Programs (PNP).'
                    ],
                    [
                        'q' => 'Do my spouse\'s qualifications help increase our score?',
                        'a' => 'Yes. A spouse\'s educational credentials, language test results, and any Canadian work experience can contribute valuable points to your total CRS ranking.'
                    ]
                ],
                'enquiry_code' => 'canada_pr',
                'cta_text' => 'Consult on Canada PR',
                'cta_url' => '/contact?service=canada_pr',
                'whatsapp_msg' => 'Hello LAD Global Travels, I would like to consult on Canada Permanent Residency pathways.'
            ],

            'citizenship-by-investment' => [
                'id' => 7,
                'slug' => 'citizenship-by-investment',
                'aliases' => ['cbi', 'citizenship'],
                'short_title' => 'Citizenship by Investment',
                'title' => 'Citizenship by Investment (CBI) Advisory',
                'eyebrow' => 'Global Mobility & Wealth Preservation',
                'badge' => 'Global Mobility',
                'badge_class' => 'badge-gold',
                'cover_image' => '/assets/images/service-cbi.jpg',
                'icon_svg' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>',
                'tagline' => 'Advisory on legitimate government-legislated economic citizenship programs offering global visa-free mobility.',
                'summary' => 'Advisory on legitimate government-legislated economic citizenship programs offering global visa-free mobility.',
                'overview' => [
                    'For high-net-worth individuals, business leaders, and global investors, a second citizenship delivers unmatched visa-free mobility, international business expansion, and geopolitical peace of mind. Citizenship by Investment (CBI) programs allow qualifying individuals and their families to legally obtain citizenship through designated sovereign contributions or approved real estate investments.',
                    'LAD Global Travels provides confidential advisory on verified, government-legislated CBI programs across the Caribbean (Saint Kitts & Nevis, Grenada, Antigua & Barbuda, Dominica, Saint Lucia) and Europe.'
                ],
                'highlights' => [
                    [
                        'title' => 'Visa-Free Global Access',
                        'desc' => 'Gain visa-free or visa-on-arrival access to over 140+ countries and global commercial centers without recurring visa delays.'
                    ],
                    [
                        'title' => 'Family Inclusion',
                        'desc' => 'Include your spouse, dependent children up to age 30, and elderly dependent parents within a single unified family application.'
                    ],
                    [
                        'title' => 'Strict Confidentiality',
                        'desc' => 'Confidential preliminary compliance pre-screening to verify eligibility before formal government submission.'
                    ],
                    [
                        'title' => 'Donation & Real Estate Options',
                        'desc' => 'Comparison between non-refundable sovereign fund contributions and government-approved luxury real estate developments.'
                    ]
                ],
                'process_steps' => [
                    [
                        'step' => '1',
                        'title' => 'Confidential Consultation',
                        'desc' => 'Evaluation of your family profile, global travel objectives, capital preferences, and program suitability.'
                    ],
                    [
                        'step' => '2',
                        'title' => 'Pre-Screening & Due Diligence',
                        'desc' => 'Preliminary background audit to ensure compliance with sovereign citizenship unit regulations.'
                    ],
                    [
                        'step' => '3',
                        'title' => 'Document Compilation',
                        'desc' => 'Gathering certified birth records, police clearances, medical checks, and verified source-of-funds banking documentation.'
                    ],
                    [
                        'step' => '4',
                        'title' => 'Approval & Passport Issuance',
                        'desc' => 'Government approval in principle, capital transfer execution, certificate of naturalization issuance, and passport delivery.'
                    ]
                ],
                'checklist' => [
                    'Valid International Passport and certified true copies of national identification',
                    'Birth certificates and marriage certificates with apostille / consular legalisation',
                    'Police clearance certificates from countries of citizenship and residence',
                    'Documented proof of legitimate source of funds and audited financial references',
                    'Comprehensive medical examinations and clean health certificates'
                ],
                'disclaimer' => 'Statutory Compliance: All programs are subject to verified sovereign government frameworks, stringent anti-money laundering (AML) protocols, and international background clearance checks.',
                'faqs' => [
                    [
                        'q' => 'Do I need to relocate to the country to obtain citizenship?',
                        'a' => 'Most Caribbean CBI programs do not require physical residency or travel to the country before or after citizenship is granted.'
                    ],
                    [
                        'q' => 'Are CBI programs recognized under international law?',
                        'a' => 'Yes. We only advise on programs established by official parliamentary legislation and administered by sovereign government units.'
                    ]
                ],
                'enquiry_code' => 'cbi',
                'cta_text' => 'CBI Consultation',
                'cta_url' => '/contact?service=cbi',
                'whatsapp_msg' => 'Hello LAD Global Travels, I would like to enquire about Citizenship by Investment programs.'
            ],

            'residency-by-investment' => [
                'id' => 8,
                'slug' => 'residency-by-investment',
                'aliases' => ['rbi', 'golden-visa'],
                'short_title' => 'Residency by Investment',
                'title' => 'Residency by Investment (RBI) Advisory',
                'eyebrow' => 'European & International Golden Visas',
                'badge' => 'Residency Advisory',
                'badge_class' => 'badge-gold',
                'cover_image' => '/assets/images/service-rbi.jpg',
                'icon_svg' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
                'tagline' => 'European and international Golden Visa programs, residency permits, and family relocation options through investment.',
                'summary' => 'European and international Golden Visa programs, residency permits, and family relocation options through investment.',
                'overview' => [
                    'Residency by Investment (RBI), widely known as Golden Visa programs, provides investors and their families with legal residence permits in prominent European and international jurisdictions. Whether your goal is accessing world-class education for your children, superior healthcare, establishing an international business base, or having a secure European home, Golden Visas offer exceptional flexibility without requiring permanent relocation.',
                    'LAD Global Travels provides specialized advisory on verified programs across Portugal, Spain, Greece, Malta, and the United Arab Emirates (UAE 10-Year Golden Visa).'
                ],
                'highlights' => [
                    [
                        'title' => 'European Schengen Visa-Free Access',
                        'desc' => 'Travel freely across all 29 European Schengen member states without separate visa applications.'
                    ],
                    [
                        'title' => 'Minimal Physical Stay Requirements',
                        'desc' => 'Many European programs require as little as 7 to 14 days of physical presence per year to maintain residency.'
                    ],
                    [
                        'title' => 'Education & Healthcare Access',
                        'desc' => 'Enables your children to attend top European schools and universities while enjoying resident healthcare rights.'
                    ],
                    [
                        'title' => 'Pathway to Permanent Residency',
                        'desc' => 'Structured legal pathway toward European permanent residency or citizenship after satisfying statutory period criteria.'
                    ]
                ],
                'process_steps' => [
                    [
                        'step' => '1',
                        'title' => 'Jurisdiction & Program Selection',
                        'desc' => 'Comparing investment thresholds across funds, real estate, and government bonds in Portugal, Greece, Spain, and the UAE.'
                    ],
                    [
                        'step' => '2',
                        'title' => 'Legal & Investment Structuring',
                        'desc' => 'Partnering with certified immigration attorneys and opening local tax and bank accounts in the destination jurisdiction.'
                    ],
                    [
                        'step' => '3',
                        'title' => 'Biometric Capture & Permit Issuance',
                        'desc' => 'Coordinating appointment bookings with national immigration authorities for family biometric enrollment.'
                    ],
                    [
                        'step' => '4',
                        'title' => 'Ongoing Renewal Management',
                        'desc' => 'Monitoring stay requirements and managing renewal schedules for your residency cards.'
                    ]
                ],
                'checklist' => [
                    'Valid International Passport for all applying family members',
                    'Clean police conduct certificates apostilled by official authorities',
                    'Documented proof of legitimate source of funds and capital transfer clearance',
                    'Comprehensive private international health insurance covering the jurisdiction',
                    'Proof of qualifying qualifying capital investment or property ownership'
                ],
                'disclaimer' => 'Jurisdictional Notice: Requirements, stay criteria, and tax implications vary by jurisdiction. We provide initial feasibility reviews and coordinate with licensed local legal practitioners.',
                'faqs' => [
                    [
                        'q' => 'Does a Golden Visa allow me to travel freely in Europe?',
                        'a' => 'Yes. A European Golden Visa holder enjoys visa-free travel within the Schengen Area for up to 90 days in any 180-day period.'
                    ],
                    [
                        'q' => 'Can I include my family members in my Golden Visa application?',
                        'a' => 'Yes. Most programs allow the primary applicant to include their spouse, dependent children, and dependent parents.'
                    ]
                ],
                'enquiry_code' => 'rbi',
                'cta_text' => 'RBI Consultation',
                'cta_url' => '/contact?service=rbi',
                'whatsapp_msg' => 'Hello LAD Global Travels, I would like to enquire about Residency by Investment (Golden Visa) programs.'
            ]
        ];
    }

    /**
     * Find a service by slug or alias.
     *
     * @param string $slug
     * @return array|null
     */
    public static function find(string $slug): ?array
    {
        $normalized = strtolower(trim($slug));
        $all = self::all();

        if (isset($all[$normalized])) {
            return $all[$normalized];
        }

        foreach ($all as $service) {
            if (in_array($normalized, $service['aliases'] ?? [], true)) {
                return $service;
            }
        }

        return null;
    }
}
