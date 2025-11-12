<?php
/**
 * Gallery Homepage
 * Built in Kornwestheim
 * Developed by Cüneyt Kaya — https://kayacuneyt.com
 */

define('APP_ROOT', __DIR__);
require_once APP_ROOT . '/includes/config.php';
require_once APP_ROOT . '/includes/db.php';

// Get published artworks
$artworks = $db->fetchAll("
    SELECT * FROM artworks 
    WHERE is_published = 1 
    ORDER BY sort_order ASC, created_at DESC
");

// Get settings
$siteTitle = get_setting('site_title', 'Artist Portfolio');
$siteDescription = get_setting('site_description', 'Contemporary art portfolio');
$enablePrices = get_setting('enable_prices', '1');
$enableInquiries = get_setting('enable_inquiries', '1');

$artistName = get_setting('artist_name', 'A. Rossi');
$artistLocation = get_setting('artist_location', 'Bournemouth, UK');
$artistTagline = get_setting('artist_tagline', 'Contemporary abstract artist exploring coastal light.');
$artistStatement = get_setting('artist_statement', 'My work captures the quiet tension between sea and sky, layering pigments and texture mediums to mirror the shifting coastline I call home.');
$artistEmailPublic = get_setting('public_email', 'studio@arossiartwork.com');
$artistInstagram = get_setting('instagram_handle', '@arossi.art');
$instagramHandleLink = ltrim($artistInstagram, '@');
$whatsappPhone = get_setting('whatsapp_phone', '+447123456789');

$statHighlights = [
    [
        'label' => 'Commissions delivered',
        'value' => get_setting('stat_commissions', '40+')
    ],
    [
        'label' => 'Exhibitions & pop-ups',
        'value' => get_setting('stat_exhibitions', '12')
    ],
    [
        'label' => 'Years of practice',
        'value' => get_setting('stat_years_active', '8')
    ],
];

$servicesOffered = [
    [
        'title' => 'Custom Commissions',
        'description' => 'Tell me about your space, palette and desired emotion. I create site-specific works including diptychs and extra-large formats.'
    ],
    [
        'title' => 'Original Works',
        'description' => 'Curated selection of ready-to-hang canvases available for immediate purchase or gallery loans.'
    ],
    [
        'title' => 'Workshops & Live Painting',
        'description' => 'Small-group texture workshops and on-site painting for brand activations or hospitality venues.'
    ],
];

$commissionSteps = [
    [
        'title' => 'Share your vision',
        'description' => 'Send a note with the mood, palette, reference pieces and the wall measurements so I can sketch ideas.'
    ],
    [
        'title' => 'Concept & approval',
        'description' => 'Receive a digital mock-up plus material swatches. We refine together before the first brush stroke.'
    ],
    [
        'title' => 'Creation & delivery',
        'description' => 'Expect weekly progress photos, optional studio visits, and insured delivery or installation support.'
    ],
];

$featuredArtwork = null;
foreach ($artworks as $artwork) {
    if (!empty($artwork['is_featured'])) {
        $featuredArtwork = $artwork;
        break;
    }
}
if (!$featuredArtwork && !empty($artworks)) {
    $featuredArtwork = $artworks[0];
}

$uniqueTechniqueMap = [];
foreach ($artworks as $artwork) {
    $technique = trim($artwork['technique'] ?? '');
    if ($technique === '') {
        continue;
    }
    $slug = slugify($technique);
    if (!isset($uniqueTechniqueMap[$slug])) {
        $uniqueTechniqueMap[$slug] = $technique;
    }
}
$uniqueTechniques = [];
foreach ($uniqueTechniqueMap as $slug => $label) {
    $uniqueTechniques[] = [
        'slug' => $slug,
        'label' => $label
    ];
}

$uniqueYears = array_values(array_filter(array_unique(array_map(function ($item) {
    return $item['year'] ?? null;
}, $artworks))));
rsort($uniqueYears);

$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo h($siteDescription); ?>">
    <title><?php echo h($siteTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, h4 {
            font-family: 'Playfair Display', serif;
        }
        .artwork-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .artwork-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .modal {
            display: none;
            backdrop-filter: blur(4px);
        }
        .modal.active {
            display: flex;
        }
        img[loading="lazy"] {
            opacity: 0;
            transition: opacity 0.3s;
        }
        img.loaded {
            opacity: 1;
        }
        .filter-chip {
            padding: 0.5rem 1.2rem;
            border-radius: 9999px;
            border: 1px solid #e5e7eb;
            font-size: 0.875rem;
            color: #374151;
            transition: all 0.2s ease;
        }
        .filter-chip:hover {
            border-color: #111827;
        }
        .filter-chip.active {
            background-color: #111827;
            color: #ffffff;
            border-color: #111827;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900">
                    <?php echo h($siteTitle); ?>
                </h1>
                <?php if ($enableInquiries): ?>
                    <button 
                        onclick="openInquiryModal()"
                        class="bg-gradient-to-r from-blue-600 to-blue-500 text-white px-6 py-3 rounded-lg font-medium hover:from-blue-700 hover:to-blue-600 transition transform hover:scale-105 active:scale-95"
                    >
                        Request Artwork
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero -->
        <section class="bg-gradient-to-b from-slate-950 via-slate-900 to-slate-800 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <p class="inline-flex items-center text-sm uppercase tracking-[0.3em] text-slate-300 mb-4">
                            <?php echo h($artistLocation); ?>
                        </p>
                        <h2 class="text-4xl md:text-5xl font-bold text-white leading-tight">
                            <?php echo h($artistName); ?>
                        </h2>
                        <p class="text-xl text-slate-200 mt-4">
                            <?php echo h($artistTagline); ?>
                        </p>
                        <p class="text-slate-300 mt-6 leading-relaxed">
                            <?php echo h($siteDescription); ?>
                        </p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <?php if ($enableInquiries): ?>
                                <button onclick="openInquiryModal()" class="px-6 py-3 bg-white text-slate-900 rounded-full font-medium shadow-lg hover:-translate-y-0.5 transition">
                                    Start a custom piece
                                </button>
                            <?php endif; ?>
                            <a href="#gallery" class="px-6 py-3 border border-white/40 text-white rounded-full hover:bg-white/10 transition">
                                Browse gallery
                            </a>
                        </div>
                        <div class="mt-10 grid grid-cols-3 gap-4">
                            <?php foreach ($statHighlights as $stat): ?>
                                <div class="border-t border-white/20 pt-4">
                                    <div class="text-3xl font-semibold text-white">
                                        <?php echo h($stat['value']); ?>
                                    </div>
                                    <p class="text-sm text-slate-300 mt-1">
                                        <?php echo h($stat['label']); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="relative">
                        <?php if ($featuredArtwork): ?>
                            <div class="bg-white/10 backdrop-blur rounded-3xl p-4 shadow-2xl">
                                <div class="rounded-2xl overflow-hidden aspect-[4/5] bg-slate-800">
                                    <picture>
                                        <?php if (!empty($featuredArtwork['webp_filename'])): ?>
                                            <source srcset="<?php echo SITE_URL . '/uploads/webp/' . h($featuredArtwork['webp_filename']); ?>" type="image/webp">
                                        <?php endif; ?>
                                        <img src="<?php echo SITE_URL . '/uploads/artworks/' . h($featuredArtwork['filename']); ?>" alt="<?php echo h($featuredArtwork['title']); ?>" class="w-full h-full object-cover">
                                    </picture>
                                </div>
                                <div class="mt-4">
                                    <p class="text-sm uppercase tracking-widest text-slate-300">Featured work</p>
                                    <h3 class="text-2xl font-semibold text-white"><?php echo h($featuredArtwork['title']); ?></h3>
                                    <p class="text-slate-300 text-sm mt-1">
                                        <?php echo h($featuredArtwork['year']); ?> <?php echo $featuredArtwork['technique'] ? '• ' . h($featuredArtwork['technique']) : ''; ?>
                                    </p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="rounded-3xl border border-dashed border-white/30 p-10 text-center">
                                <p class="text-lg text-slate-200">Upload your first artwork to highlight it here.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Artist Statement -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid lg:grid-cols-3 gap-12">
                <div class="lg:col-span-2">
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-widest mb-3">Artist Statement</p>
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Coastal light reinterpreted on canvas</h2>
                    <p class="text-lg text-gray-700 leading-relaxed">
                        <?php echo h($artistStatement); ?>
                    </p>
                </div>
                <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Studio Notes</h3>
                    <ul class="space-y-4 text-gray-600">
                        <li>
                            <span class="block text-sm uppercase tracking-widest text-gray-400">Currently booking</span>
                            <span class="text-gray-900 font-medium">June – August 2024 commissions</span>
                        </li>
                        <li>
                            <span class="block text-sm uppercase tracking-widest text-gray-400">Email</span>
                            <a href="mailto:<?php echo h($artistEmailPublic); ?>" class="text-blue-600 hover:underline"><?php echo h($artistEmailPublic); ?></a>
                        </li>
                        <li>
                            <span class="block text-sm uppercase tracking-widest text-gray-400">Instagram</span>
                            <a href="https://instagram.com/<?php echo h($instagramHandleLink); ?>" target="_blank" class="text-blue-600 hover:underline">
                                <?php echo h($artistInstagram); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Services -->
        <section class="bg-white/60 border-y border-gray-100 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-10">
                    <div>
                        <p class="text-sm font-semibold text-blue-600 uppercase tracking-widest">Offerings</p>
                        <h2 class="text-3xl font-bold text-gray-900">Ways to collaborate</h2>
                    </div>
                    <?php if ($enableInquiries): ?>
                        <button onclick="openInquiryModal()" class="px-6 py-3 border border-gray-300 rounded-full text-gray-800 hover:bg-gray-50">Discuss a project</button>
                    <?php endif; ?>
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    <?php foreach ($servicesOffered as $service): ?>
                        <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:-translate-y-1 hover:shadow-lg transition">
                            <h3 class="text-xl font-semibold text-gray-900 mb-3"><?php echo h($service['title']); ?></h3>
                            <p class="text-gray-600 leading-relaxed"><?php echo h($service['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Commission Process -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="mb-10">
                <p class="text-sm font-semibold text-blue-600 uppercase tracking-widest">Commission process</p>
                <h2 class="text-3xl font-bold text-gray-900">From brief to installation</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                <?php foreach ($commissionSteps as $index => $step): ?>
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                        <span class="text-sm font-semibold text-blue-600">Step <?php echo $index + 1; ?></span>
                        <h3 class="text-2xl font-semibold text-gray-900 mt-2"><?php echo h($step['title']); ?></h3>
                        <p class="text-gray-600 mt-3 leading-relaxed"><?php echo h($step['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Gallery -->
        <section id="gallery" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-10">
                <div>
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-widest">Gallery</p>
                    <h2 class="text-3xl font-bold text-gray-900">Available & archived works</h2>
                    <p class="text-gray-600 mt-2">Tap a piece to view materials, dimensions and inquire instantly.</p>
                </div>
                <?php if ($enableInquiries): ?>
                    <button onclick="openInquiryModal()" class="px-5 py-3 border border-gray-300 rounded-full text-gray-800 hover:bg-gray-50">General inquiry</button>
                <?php endif; ?>
            </div>

            <?php if (empty($artworks)): ?>
                <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
                    <svg class="mx-auto h-24 w-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-700 mb-2">Gallery coming soon</h3>
                    <p class="text-gray-500">Uploads appear here with automatic thumbnails and WebP previews.</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-10">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div class="flex flex-wrap gap-3">
                            <button class="filter-chip active" data-filter-type="technique" data-filter-value="all">
                                All media
                            </button>
                            <?php foreach ($uniqueTechniques as $technique): ?>
                                <button class="filter-chip" data-filter-type="technique" data-filter-value="<?php echo h($technique['slug']); ?>">
                                    <?php echo h($technique['label']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!empty($uniqueYears)): ?>
                            <div class="lg:w-60">
                                <label for="yearFilter" class="block text-sm font-medium text-gray-600 mb-2">Year</label>
                                <select id="yearFilter" class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="all">All years</option>
                                    <?php foreach ($uniqueYears as $year): ?>
                                        <option value="<?php echo h($year); ?>"><?php echo h($year); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="filterEmptyState" class="hidden text-center py-12 bg-white rounded-3xl border border-dashed border-gray-200 mb-10">
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No artworks match your filters.</h3>
                    <p class="text-gray-500">Try clearing the filters or explore a different medium.</p>
                </div>

                <div id="artworkGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($artworks as $artwork): ?>
                        <?php 
                            $techSlug = slugify($artwork['technique'] ?? '');
                            $yearValue = $artwork['year'] ?: '';
                        ?>
                        <div 
                            class="artwork-card bg-white rounded-xl shadow-lg overflow-hidden cursor-pointer flex flex-col"
                            onclick="openArtwork(this)"
                            data-title="<?php echo h($artwork['title']); ?>"
                            data-description="<?php echo h($artwork['description']); ?>"
                            data-year="<?php echo h($yearValue); ?>"
                            data-technique="<?php echo h($artwork['technique']); ?>"
                            data-technique-slug="<?php echo h($techSlug); ?>"
                            data-dimensions="<?php echo h($artwork['dimensions']); ?>"
                            data-price="<?php echo h($artwork['price']); ?>"
                            data-image="<?php echo SITE_URL . '/uploads/artworks/' . h($artwork['filename']); ?>"
                            data-webp="<?php echo !empty($artwork['webp_filename']) ? SITE_URL . '/uploads/webp/' . h($artwork['webp_filename']) : ''; ?>"
                            data-thumbnail="<?php echo SITE_URL . '/uploads/thumbnails/' . h($artwork['thumbnail']); ?>"
                        >
                            <div class="relative aspect-square overflow-hidden bg-gray-100">
                                <?php if (!empty($artwork['is_featured'])): ?>
                                    <span class="absolute top-3 left-3 z-10 bg-white/90 text-gray-900 text-xs font-semibold px-3 py-1 rounded-full shadow">Featured</span>
                                <?php endif; ?>
                                <picture>
                                    <?php if (!empty($artwork['webp_filename'])): ?>
                                        <source srcset="<?php echo SITE_URL . '/uploads/webp/' . h($artwork['webp_filename']); ?>" type="image/webp">
                                    <?php endif; ?>
                                    <img 
                                        src="<?php echo SITE_URL . '/uploads/thumbnails/' . h($artwork['thumbnail']); ?>" 
                                        alt="<?php echo h($artwork['title']); ?>"
                                        loading="lazy"
                                        class="w-full h-full object-cover"
                                        onload="this.classList.add('loaded')"
                                    >
                                </picture>
                            </div>
                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex items-center justify-between gap-4">
                                    <h3 class="text-xl font-bold text-gray-900">
                                        <?php echo h($artwork['title']); ?>
                                    </h3>
                                    <?php if ($enablePrices && $artwork['price']): ?>
                                        <span class="text-base font-semibold text-blue-600">
                                            £<?php echo number_format($artwork['price'], 2); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?php echo h($artwork['year']); ?>
                                    <?php if ($artwork['year'] && $artwork['technique']) echo ' • '; ?>
                                    <?php echo h($artwork['technique']); ?>
                                </p>
                                <?php if ($artwork['description']): ?>
                                    <p class="text-gray-700 text-sm mt-3 line-clamp-3 flex-1">
                                        <?php echo h($artwork['description']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ($artwork['dimensions']): ?>
                                    <p class="text-sm text-gray-500 mt-3">Dimensions: <?php echo h($artwork['dimensions']); ?></p>
                                <?php endif; ?>
                                <div class="mt-6 flex items-center justify-between text-sm text-blue-600 font-medium">
                                    <span>View details</span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6 text-sm">
            <div>
                <p class="font-semibold">© <?php echo date('Y'); ?> <?php echo h($artistName); ?>.</p>
                <p class="text-slate-400">Built in Kornwestheim.</p>
                <p class="text-slate-400">Developed by <a href="https://kayacuneyt.com" target="_blank" class="text-white underline-offset-4 hover:underline">Cüneyt Kaya</a></p>
            </div>
            <div class="space-y-2 text-slate-300">
                <p><?php echo h($artistLocation); ?></p>
                <a href="mailto:<?php echo h($artistEmailPublic); ?>" class="hover:text-white">Email: <?php echo h($artistEmailPublic); ?></a>
                <a href="https://api.whatsapp.com/send?phone=<?php echo urlencode($whatsappPhone); ?>" target="_blank" class="hover:text-white">WhatsApp: <?php echo h($whatsappPhone); ?></a>
            </div>
        </div>
    </footer>

    <!-- Artwork Detail Modal -->
    <div id="artworkModal" class="modal fixed inset-0 bg-black bg-opacity-60 items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center px-6 py-4 border-b">
                <h2 class="text-2xl font-bold text-gray-900" id="artworkModalTitle">Artwork Title</h2>
                <button onclick="closeArtworkModal()" class="text-gray-400 hover:text-gray-600 text-3xl leading-none">×</button>
            </div>
            <div class="grid lg:grid-cols-2 gap-0">
                <div class="p-6">
                    <div class="rounded-2xl overflow-hidden bg-gray-100">
                        <img id="artworkModalImage" src="" alt="Artwork image" class="w-full h-full object-cover">
                    </div>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <p class="text-sm uppercase tracking-widest text-gray-400">Details</p>
                        <p class="text-gray-900 text-lg font-medium" id="artworkModalMeta"></p>
                        <p class="text-gray-600 mt-2" id="artworkModalDimensions"></p>
                        <p class="text-blue-600 text-xl font-semibold mt-2 hidden" id="artworkModalPrice"></p>
                    </div>
                    <div>
                        <p class="text-sm uppercase tracking-widest text-gray-400 mb-2">Story</p>
                        <p class="text-gray-700 leading-relaxed" id="artworkModalDescription"></p>
                    </div>
                    <?php if ($enableInquiries): ?>
                        <button onclick="requestFromArtworkModal()" class="w-full px-6 py-4 bg-green-600 text-white rounded-2xl font-medium hover:bg-green-700 transition">
                            Request this piece via WhatsApp
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- WhatsApp Inquiry Modal -->
    <div id="inquiryModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900">Request Artwork Information</h2>
                <button 
                    onclick="closeInquiryModal()"
                    class="text-gray-400 hover:text-gray-600 text-3xl leading-none"
                >
                    ×
                </button>
            </div>
            <form id="inquiryForm" class="p-6 space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="artwork_title" id="artworkTitleInput" value="">
                <div id="selectedArtworkNotice" class="hidden border border-green-100 bg-green-50 text-green-900 rounded-xl px-4 py-3">
                    Asking about <span class="font-semibold" id="selectedArtworkTitle"></span>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                    <input 
                        type="text" 
                        name="name" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Your name"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input 
                        type="email" 
                        name="email" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="your@email.com"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone (optional)</label>
                    <input 
                        type="tel" 
                        name="phone"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="+44 7123 456789"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                    <textarea 
                        name="message" 
                        required
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Tell us about your inquiry..."
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Size</label>
                        <input 
                            type="text" 
                            name="preferred_size"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., 60x80 cm"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Color</label>
                        <input 
                            type="text" 
                            name="preferred_color"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., Blue tones"
                        >
                    </div>
                </div>

                <div class="pt-4 flex space-x-4">
                    <button 
                        type="button"
                        onclick="closeInquiryModal()"
                        class="flex-1 px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium flex items-center justify-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        Send via WhatsApp
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="hidden fixed top-6 right-6 z-50 px-4 py-3 rounded-xl shadow-lg text-sm font-medium"></div>

    <script>
        let currentArtworkTitle = '';
        let selectedArtworkTitle = '';

        function toggleBodyScroll(disable) {
            if (disable) {
                document.body.style.overflow = 'hidden';
                return;
            }
            const anyOpenModal = document.querySelector('.modal.active');
            document.body.style.overflow = anyOpenModal ? 'hidden' : 'auto';
        }

        function openInquiryModal(title = '') {
            selectedArtworkTitle = title || '';
            const modal = document.getElementById('inquiryModal');
            const notice = document.getElementById('selectedArtworkNotice');
            const noticeTitle = document.getElementById('selectedArtworkTitle');
            const hiddenInput = document.getElementById('artworkTitleInput');
            const messageField = document.querySelector('#inquiryForm textarea[name="message"]');

            if (selectedArtworkTitle) {
                notice.classList.remove('hidden');
                noticeTitle.textContent = selectedArtworkTitle;
                hiddenInput.value = selectedArtworkTitle;
                if (!messageField.value.trim()) {
                    messageField.value = `Hi, I'm interested in "${selectedArtworkTitle}". Could you tell me more about availability and pricing?`;
                }
            } else {
                notice.classList.add('hidden');
                noticeTitle.textContent = '';
                hiddenInput.value = '';
            }

            modal.classList.add('active');
            toggleBodyScroll(true);
        }

        function closeInquiryModal() {
            document.getElementById('inquiryModal').classList.remove('active');
            toggleBodyScroll(false);
        }

        function openArtwork(cardElement) {
            const modal = document.getElementById('artworkModal');
            const title = cardElement.dataset.title || '';
            const description = cardElement.dataset.description || '';
            const year = cardElement.dataset.year || '';
            const technique = cardElement.dataset.technique || '';
            const dimensions = cardElement.dataset.dimensions || '';
            const price = cardElement.dataset.price || '';
            const image = cardElement.dataset.image || cardElement.dataset.thumbnail;

            currentArtworkTitle = title;

            document.getElementById('artworkModalTitle').textContent = title;
            document.getElementById('artworkModalDescription').textContent = description || 'Details coming soon.';
            document.getElementById('artworkModalMeta').textContent = [year, technique].filter(Boolean).join(' • ');
            document.getElementById('artworkModalDimensions').textContent = dimensions ? `Dimensions: ${dimensions}` : '';

            const priceEl = document.getElementById('artworkModalPrice');
            if (price && Number(price) > 0) {
                priceEl.textContent = `£${Number(price).toLocaleString('en-GB', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                priceEl.classList.remove('hidden');
            } else {
                priceEl.classList.add('hidden');
                priceEl.textContent = '';
            }

            const modalImage = document.getElementById('artworkModalImage');
            modalImage.alt = title;
            modalImage.src = image;

            modal.classList.add('active');
            toggleBodyScroll(true);
        }

        function closeArtworkModal() {
            document.getElementById('artworkModal').classList.remove('active');
            currentArtworkTitle = '';
            toggleBodyScroll(false);
        }

        function requestFromArtworkModal() {
            closeArtworkModal();
            openInquiryModal(currentArtworkTitle);
        }

        function showToast(type, message) {
            const toast = document.getElementById('toast');
            const baseClasses = 'px-4 py-3 rounded-xl shadow-lg text-sm font-medium transition';
            let colorClasses = '';
            if (type === 'success') {
                colorClasses = 'bg-green-600 text-white';
            } else {
                colorClasses = 'bg-red-600 text-white';
            }
            toast.className = `${baseClasses} ${colorClasses}`;
            toast.textContent = message;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 4000);
        }

        document.getElementById('inquiryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.classList.add('opacity-70', 'cursor-not-allowed');

            const formData = new FormData(this);
            
            try {
                const response = await fetch('<?php echo SITE_URL; ?>/api/submit-inquiry.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const whatsappWindow = window.open(result.whatsapp_url, '_blank');
                    if (!whatsappWindow) {
                        showToast('error', 'Pop-up blocked. Opening WhatsApp in this tab.');
                        window.location.href = result.whatsapp_url;
                    } else {
                        showToast('success', 'Inquiry saved. WhatsApp opened in a new tab.');
                    }
                    closeInquiryModal();
                    this.reset();
                    document.getElementById('selectedArtworkNotice').classList.add('hidden');
                    document.getElementById('artworkTitleInput').value = '';
                } else {
                    showToast('error', result.message || 'Something went wrong.');
                }
            } catch (error) {
                showToast('error', 'Submission failed. Please try again.');
            } finally {
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        });

        const filterButtons = document.querySelectorAll('.filter-chip');
        const yearFilter = document.getElementById('yearFilter');
        const artworkCards = document.querySelectorAll('#artworkGrid .artwork-card');
        const filterEmptyState = document.getElementById('filterEmptyState');

        function applyFilters() {
            const activeTechniqueBtn = document.querySelector('.filter-chip.active');
            const techniqueValue = activeTechniqueBtn ? activeTechniqueBtn.dataset.filterValue : 'all';
            const yearValue = yearFilter ? yearFilter.value : 'all';

            let visibleCount = 0;

            artworkCards.forEach(card => {
                const cardTechnique = card.dataset.techniqueSlug || '';
                const cardYear = card.dataset.year || '';

                const techniqueMatch = techniqueValue === 'all' || techniqueValue === cardTechnique;
                const yearMatch = yearValue === 'all' || yearValue === cardYear;

                if (techniqueMatch && yearMatch) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            if (filterEmptyState) {
                if (visibleCount === 0) {
                    filterEmptyState.classList.remove('hidden');
                } else {
                    filterEmptyState.classList.add('hidden');
                }
            }
        }

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                applyFilters();
            });
        });

        if (filterButtons.length) {
            applyFilters();
        }

        if (yearFilter) {
            yearFilter.addEventListener('change', applyFilters);
        }

        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeInquiryModal();
                closeArtworkModal();
            }
        });
    </script>
</body>
</html>
