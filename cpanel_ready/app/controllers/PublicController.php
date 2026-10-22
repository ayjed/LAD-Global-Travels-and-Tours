<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\RateLimiter;
use App\Core\Response;
use App\Core\Security;
use App\Core\Validator;
use App\Core\View;
use App\Services\EnquiryService;
use App\Services\PackageService;
use App\Services\TravelServiceCatalog;
use Throwable;

/**
 * Public Front-Facing Controller
 * Handles server-rendered public pages: Home, About, Services, Packages, FAQ, Contact, and Legal.
 */
class PublicController
{
    /**
     * Homepage with hero, services preview, featured packages, and FAQ accordion.
     */
    public function home(): void
    {
        $featuredPackages = [];
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query(
                "SELECT * FROM packages WHERE status = 'published' AND is_featured = 1 ORDER BY id ASC LIMIT 3"
            );
            $featuredPackages = $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('[Home Controller] Error fetching packages: ' . $e->getMessage());
        }

        View::render('pages/home', [
            'pageTitle' => 'Home',
            'activeNav' => 'home',
            'pageDescription' => 'Personalized travel planning, international holiday packages, and visa guidance from Lagos, Nigeria.',
            'featuredPackages' => $featuredPackages
        ]);
    }

    /**
     * About Us page covering company background, mission, physical office, and scope.
     */
    public function about(): void
    {
        View::render('pages/about', [
            'pageTitle' => 'About Us',
            'activeNav' => 'about',
            'pageDescription' => 'Discover LAD Global Travels and Tours. Our commitment to transparent travel planning, verified payments, and expert visa advisory in Lagos.'
        ]);
    }

    /**
     * Services page with detailed cards for all 8 SOT services and compliance notices.
     */
    public function services(): void
    {
        $allServices = TravelServiceCatalog::all();

        View::render('pages/services', [
            'pageTitle' => 'Our Services',
            'activeNav' => 'services',
            'pageDescription' => 'Explore our 8 core travel and mobility services: visa assistance, curated tour packages, flight consultation, study abroad, and Canada PR guidance.',
            'allServices' => $allServices
        ]);
    }

    /**
     * Dedicated Service Description Detail View for each of the 8 services.
     */
    public function serviceDetail(array $params): void
    {
        $slug = trim($params['slug'] ?? '');
        $service = TravelServiceCatalog::find($slug);

        if (!$service) {
            http_response_code(404);
            View::render('pages/404', [
                'pageTitle' => 'Service Not Found',
                'activeNav' => 'services'
            ]);
            return;
        }

        View::render('pages/service-detail', [
            'pageTitle' => $service['title'] . ' — Services',
            'activeNav' => 'services',
            'pageDescription' => $service['tagline'] ?? $service['summary'],
            'service' => $service,
            'allServices' => TravelServiceCatalog::all()
        ]);
    }

    /**
     * Tour Packages directory displaying published packages or layout placeholders.
     */
    public function packages(): void
    {
        $packages = [];
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query(
                "SELECT * FROM packages WHERE status = 'published' ORDER BY is_featured DESC, created_at DESC"
            );
            $packages = $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('[Packages Controller] Error fetching packages: ' . $e->getMessage());
        }

        View::render('pages/packages', [
            'pageTitle' => 'Tour Packages',
            'activeNav' => 'packages',
            'pageDescription' => 'Browse handcrafted international tour itineraries across Kenya, Rwanda, Dubai, Ghana, and Europe with transparent per-person pricing.',
            'packages' => $packages
        ]);
    }

    /**
     * Package Detail view with itinerary breakdown, inclusions/exclusions, and enquiry CTA.
     */
    public function packageDetail(array $params): void
    {
        $slug = trim($params['slug'] ?? '');
        $package = null;

        $aliasMap = [
            'kenya-safari' => 'kenya-safari-experience',
            'dubai-experience' => 'dubai-luxury-experience',
            'rwanda-cultural' => 'rwanda-cultural-tour',
            'ghana-heritage' => 'ghana-heritage-tour',
            'japan-discovery' => 'japan-discovery',
            'japan-alpine-discovery' => 'japan-discovery'
        ];

        $targetSlug = $aliasMap[$slug] ?? $slug;

        if ($targetSlug !== '') {
            try {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("SELECT * FROM packages WHERE slug = :slug AND status = 'published' LIMIT 1");
                $stmt->execute([':slug' => $targetSlug]);
                $package = $stmt->fetch() ?: null;

                if (!$package && $targetSlug !== $slug) {
                    $stmt->execute([':slug' => $slug]);
                    $package = $stmt->fetch() ?: null;
                }
            } catch (Throwable $e) {
                error_log('[PackageDetail Controller] Error fetching package: ' . $e->getMessage());
            }
        }

        $sampleSlugs = [
            'kenya-safari-experience',
            'kenya-safari',
            'dubai-luxury-experience',
            'dubai-experience',
            'rwanda-cultural-tour',
            'rwanda-cultural',
            'ghana-heritage-tour',
            'ghana-heritage',
            'japan-discovery',
            'japan-alpine-discovery'
        ];

        // If package not found in DB as published and not an authorized sample slug, return HTTP 404
        if (!$package && !in_array($slug, $sampleSlugs, true) && !in_array($targetSlug, $sampleSlugs, true)) {
            Response::status(404);
            View::render('pages/404', [
                'pageTitle' => 'Package Not Found',
                'pageDescription' => 'The requested tour package is not available or has been unpublished.'
            ]);
            return;
        }

        $canonicalSlug = $package ? $package['slug'] : $targetSlug;

        $galleryImages = [];
        if (!empty($package['id'])) {
            $galleryImages = PackageService::getGalleryImages((int)$package['id']);
        }

        $title = $package ? $package['title'] : ucwords(str_replace('-', ' ', $slug));

        View::render('pages/package-detail', [
            'pageTitle' => $title,
            'activeNav' => 'packages',
            'pageDescription' => $package ? ($package['summary'] ?? $package['title']) : "Details and itinerary for {$title} with LAD Global Travels.",
            'package' => $package,
            'packageSlug' => $canonicalSlug,
            'galleryImages' => $galleryImages
        ]);
    }

    /**
     * FAQs page with progressive enhancement accordion.
     */
    public function faq(): void
    {
        View::render('pages/faq', [
            'pageTitle' => 'Frequently Asked Questions',
            'activeNav' => 'faq',
            'pageDescription' => 'Answers to common questions regarding our visa guidance, flight consultation, payments, and office location in Ikorodu, Lagos.'
        ]);
    }

    /**
     * Contact and consultation form view.
     */
    public function contactForm(): void
    {
        $packageId = !empty($_GET['package_id']) ? (int)$_GET['package_id'] : null;
        $packageSlug = !empty($_GET['package']) ? trim((string)$_GET['package']) : null;
        $associatedPackage = null;

        if ($packageId) {
            $associatedPackage = PackageService::getById($packageId);
        } elseif ($packageSlug) {
            $associatedPackage = PackageService::getBySlug($packageSlug);
        }

        View::render('pages/contact', [
            'pageTitle' => 'Contact Us',
            'activeNav' => 'contact',
            'pageDescription' => 'Connect with LAD Global Travels and Tours in Ikorodu, Lagos. Phone, direct WhatsApp, and online consultation form.',
            'associatedPackage' => $associatedPackage
        ]);
    }

    /**
     * Process contact and consultation form submissions.
     */
    public function contactSubmit(): void
    {
        // 1. Anti-spam honeypot check (hidden field)
        if (!empty($_POST['website_url_check'])) {
            // Silently redirect bot submissions without processing
            Response::redirect('/contact');
            return;
        }

        // 2. Validate CSRF token
        $csrfToken = $_POST['_csrf_token'] ?? ($_POST['csrf_token'] ?? null);
        if (!Security::verifyCsrf($csrfToken)) {
            Response::flash('danger', 'Security validation failed (invalid or expired token). Please submit the form again.');
            Response::redirect('/contact');
            return;
        }

        // 3. Rate limiting & spam throttle (max 5 submissions per 10 minutes per IP)
        $clientIp = Security::getClientIp();
        $rateLimitKey = 'enquiry_submit_' . $clientIp;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5, 600)) {
            $retrySeconds = RateLimiter::availableIn($rateLimitKey);
            $retryMinutes = max(1, (int)ceil($retrySeconds / 60));
            Response::flash('danger', "Too many enquiry submissions from your connection. Please wait {$retryMinutes} minute(s) before trying again, or reach us directly on WhatsApp.");
            Response::redirect('/contact');
            return;
        }

        // 4. Server-side validation
        $validator = Validator::make($_POST, [
            'full_name'       => 'required|min:3|max:150',
            'email'           => 'required|email|max:191',
            'phone'           => 'required|min:7|max:50',
            'service_type'    => 'required',
            'message'         => 'required|min:5|max:3000',
            'privacy_consent' => 'required'
        ]);

        if ($validator->fails()) {
            Response::flash('danger', 'Please correct the errors in the form before submitting.');
            View::render('pages/contact', [
                'pageTitle' => 'Contact Us',
                'activeNav' => 'contact',
                'errors' => $validator->errors(),
                'formData' => $_POST
            ]);
            return;
        }

        // 5. Store in database via EnquiryService and trigger transactional notification
        try {
            $result = EnquiryService::create($_POST);
            RateLimiter::hit($rateLimitKey, 600);

            $ref = $result['reference'];
            Response::flash(
                'success',
                "Thank you! Your enquiry has been received (Reference: {$ref}). A travel consultant will contact you via phone or WhatsApp shortly."
            );
        } catch (Throwable $e) {
            error_log('[Contact Controller] Error creating enquiry: ' . $e->getMessage());
            Response::flash(
                'danger',
                'We were unable to save your enquiry due to a temporary system error. Please reach us directly on WhatsApp at 07017597650.'
            );
        }

        Response::redirect('/contact');
    }

    /**
     * Provisional Privacy Policy view.
     */
    public function privacy(): void
    {
        View::render('pages/privacy', [
            'pageTitle' => 'Privacy Policy',
            'pageDescription' => 'Privacy Policy and data protection practices for LAD Global Travels and Tours.'
        ]);
    }

    /**
     * Provisional Terms of Service view.
     */
    public function terms(): void
    {
        View::render('pages/terms', [
            'pageTitle' => 'Terms of Service',
            'pageDescription' => 'Terms and conditions governing travel bookings, consultations, and website usage.'
        ]);
    }

    /**
     * Provisional Refund & Cancellation Policy view.
     */
    public function refundPolicy(): void
    {
        View::render('pages/refund-policy', [
            'pageTitle' => 'Refund Policy',
            'pageDescription' => 'Guidelines governing tour package cancellations, flight supplier terms, and manual administrative refund reviews.'
        ]);
    }

    /**
     * Search engine XML sitemap listing public static pages and published packages.
     */
    public function sitemapXml(): void
    {
        $domain = \App\Core\Env::get('APP_URL', 'https://ladglobaltravels.com');
        $domain = rtrim($domain, '/');

        $staticPages = [
            ['loc' => '/', 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['loc' => '/about', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => '/services', 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => '/packages', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => '/faq', 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => '/contact', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => '/privacy', 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => '/terms', 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => '/refund-policy', 'changefreq' => 'yearly', 'priority' => '0.3'],
        ];

        $packages = [];
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT slug, updated_at FROM packages WHERE status = 'published' ORDER BY updated_at DESC");
            $packages = $stmt->fetchAll();
        } catch (Throwable $e) {
            // fallback gracefully
        }

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($staticPages as $page) {
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars($domain . $page['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            echo "    <changefreq>{$page['changefreq']}</changefreq>\n";
            echo "    <priority>{$page['priority']}</priority>\n";
            echo "  </url>\n";
        }

        foreach ($packages as $pkg) {
            $lastmod = !empty($pkg['updated_at']) ? date('Y-m-d', strtotime($pkg['updated_at'])) : date('Y-m-d');
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars($domain . '/packages/' . $pkg['slug'], ENT_XML1, 'UTF-8') . "</loc>\n";
            echo "    <lastmod>{$lastmod}</lastmod>\n";
            echo "    <changefreq>weekly</changefreq>\n";
            echo "    <priority>0.8</priority>\n";
            echo "  </url>\n";
        }

        echo '</urlset>' . "\n";
        exit;
    }
}
