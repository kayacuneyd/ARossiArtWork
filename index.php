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
                        onclick="openModal()"
                        class="bg-gradient-to-r from-blue-600 to-blue-500 text-white px-6 py-3 rounded-lg font-medium hover:from-blue-700 hover:to-blue-600 transition transform hover:scale-105 active:scale-95"
                    >
                        Request Artwork
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Gallery -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if (empty($artworks)): ?>
            <div class="text-center py-20">
                <svg class="mx-auto h-24 w-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h2 class="text-2xl font-bold text-gray-600 mb-2">No Artworks Yet</h2>
                <p class="text-gray-500">Check back soon for new artwork!</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($artworks as $artwork): ?>
                    <div class="artwork-card bg-white rounded-xl shadow-lg overflow-hidden cursor-pointer" onclick="openArtwork(<?php echo $artwork['id']; ?>)">
                        <div class="aspect-square overflow-hidden bg-gray-100">
                            <picture>
                                <source 
                                    srcset="<?php echo SITE_URL . '/uploads/webp/' . h($artwork['webp_filename']); ?>" 
                                    type="image/webp"
                                >
                                <img 
                                    src="<?php echo SITE_URL . '/uploads/thumbnails/' . h($artwork['thumbnail']); ?>" 
                                    alt="<?php echo h($artwork['title']); ?>"
                                    loading="lazy"
                                    class="w-full h-full object-cover"
                                    onload="this.classList.add('loaded')"
                                >
                            </picture>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">
                                <?php echo h($artwork['title']); ?>
                            </h3>
                            <?php if ($artwork['year'] || $artwork['technique']): ?>
                                <p class="text-sm text-gray-600 mb-3">
                                    <?php echo h($artwork['year']); ?>
                                    <?php if ($artwork['year'] && $artwork['technique']) echo ' • '; ?>
                                    <?php echo h($artwork['technique']); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($artwork['description']): ?>
                                <p class="text-gray-700 text-sm line-clamp-2 mb-3">
                                    <?php echo h($artwork['description']); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($enablePrices && $artwork['price']): ?>
                                <div class="text-lg font-semibold text-blue-600">
                                    £<?php echo number_format($artwork['price'], 2); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center text-gray-600 text-sm">
            <p class="mb-1">Built in Kornwestheim</p>
            <p>
                Developed by 
                <a href="https://kayacuneyt.com" target="_blank" class="text-blue-600 hover:text-blue-700 font-medium">
                    Cüneyt Kaya
                </a>
            </p>
        </div>
    </footer>

    <!-- WhatsApp Inquiry Modal -->
    <div id="inquiryModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900">Request Artwork Information</h2>
                <button 
                    onclick="closeModal()"
                    class="text-gray-400 hover:text-gray-600 text-3xl leading-none"
                >
                    ×
                </button>
            </div>
            <form id="inquiryForm" class="p-6 space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
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
                        onclick="closeModal()"
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

    <script>
        function openModal() {
            document.getElementById('inquiryModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('inquiryModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        document.getElementById('inquiryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('<?php echo SITE_URL; ?>/api/submit-inquiry.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Open WhatsApp
                    window.location.href = result.whatsapp_url;
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Submission failed. Please try again.');
            }
        });

        function openArtwork(id) {
            // You can implement a detail view here or lightbox
            console.log('Open artwork:', id);
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
