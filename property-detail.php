<?php
/**
 * Property Detail Page
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: properties.php'); exit; }

// Fetch property
$stmt = $pdo->prepare("
    SELECT p.*, pt.name as type_name, pt.slug as type_slug,
           a.name as agent_name, a.title as agent_title, a.photo as agent_photo,
           a.phone as agent_phone, a.whatsapp as agent_whatsapp, a.email as agent_email,
           a.rating as agent_rating, a.properties_sold as agent_sold, a.experience_years as agent_exp,
           a.id as agent_id
    FROM properties p
    LEFT JOIN property_types pt ON p.property_type_id = pt.id
    LEFT JOIN agents a ON p.agent_id = a.id
    WHERE p.id = :id AND p.is_active = 1
");
$stmt->execute([':id' => $id]);
$p = $stmt->fetch();

if (!$p) { header('Location: properties.php'); exit; }

// Increment views
$pdo->prepare("UPDATE properties SET views = views + 1 WHERE id = ?")->execute([$id]);

// Get property images
$images = $pdo->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY sort_order ASC");
$images->execute([$id]);
$images = $images->fetchAll();

// Similar properties
$similar = $pdo->prepare("
    SELECT p.*, pt.name as type_name
    FROM properties p
    LEFT JOIN property_types pt ON p.property_type_id = pt.id
    WHERE p.is_active = 1 AND p.id != :id
      AND (p.property_type_id = :tid OR p.location_area = :loc)
    ORDER BY p.featured DESC, RAND()
    LIMIT 3
");
$similar->execute([':id' => $id, ':tid' => $p['property_type_id'], ':loc' => $p['location_area']]);
$similar = $similar->fetchAll();

// Handle inquiry form
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_inquiry'])) {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $message) {
        $ins = $pdo->prepare("INSERT INTO inquiries (property_id, agent_id, name, email, phone, subject, message, inquiry_type, ip_address) VALUES (?,?,?,?,?,?,?,'property',?)");
        $ins->execute([$id, $p['agent_id'], $name, $email, $phone, 'Inquiry about: ' . $p['title'], $message, $_SERVER['REMOTE_ADDR']]);
        $flash = ['type' => 'success', 'message' => 'Your inquiry has been sent! Our agent will contact you shortly.'];
    } else {
        $flash = ['type' => 'danger', 'message' => 'Please fill in all required fields.'];
    }
}

$amenities = getAmenities($p);
$pageTitle = $p['title'];
require_once 'includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero">
    <div class="container">
        <div style="display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
            <?php if ($p['featured']): ?><span class="badge badge-gold">Featured</span><?php endif; ?>
            <span class="badge <?= $p['price_type'] === 'rent' ? 'badge-info' : 'badge-success' ?>">
                <?= $p['price_type'] === 'rent' ? 'For Rent' : 'For Sale' ?>
            </span>
            <?= statusBadge($p['status']) ?>
            <span class="badge" style="background:rgba(255,255,255,0.08);color:var(--text-secondary);">
                <i class="fas fa-eye"></i> <?= number_format($p['views']) ?> views
            </span>
        </div>
        <h1 style="font-size:clamp(1.4rem,3vw,2rem);"><?= e($p['title']) ?></h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <a href="properties.php">Properties</a>
            <i class="fas fa-chevron-right"></i>
            <span><?= e($p['type_name']) ?></span>
        </div>
    </div>
</div>

<section class="section-padding">
<div class="container">

    <!-- Property Gallery -->
    <div class="property-gallery" style="margin-bottom:32px;">
        <div class="gallery-main" data-lightbox="<?= propertyImageUrl($p['cover_image']) ?>">
            <img src="<?= propertyImageUrl($p['cover_image']) ?>" alt="<?= e($p['title']) ?>" id="galleryMain">
            <div style="position:absolute;top:14px;right:14px;">
                <button onclick="document.getElementById('lightbox').classList.add('open');document.getElementById('lightboxImg').src='<?= propertyImageUrl($p['cover_image']) ?>'"
                        style="background:rgba(0,0,0,0.6);border:1px solid rgba(255,255,255,0.2);color:white;padding:8px 16px;border-radius:8px;cursor:pointer;font-size:0.85rem;backdrop-filter:blur(10px);">
                    <i class="fas fa-expand-alt"></i> Full Screen
                </button>
            </div>
        </div>
        <?php
        $galleryImages = array_slice($images, 0, 2);
        if (count($galleryImages) < 2) {
            // Add placeholder
            while (count($galleryImages) < 2) {
                $galleryImages[] = ['image_path' => $p['cover_image']];
            }
        }
        foreach ($galleryImages as $idx => $img):
        ?>
        <div class="gallery-thumb" data-lightbox="<?= propertyImageUrl($img['image_path'] ?? $p['cover_image']) ?>">
            <img src="<?= propertyImageUrl($img['image_path'] ?? $p['cover_image']) ?>" alt="Gallery <?= $idx+1 ?>">
            <?php if ($idx === 1 && count($images) > 2): ?>
            <div class="gallery-more">+<?= count($images) - 2 ?> more</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Detail Layout -->
    <div class="detail-layout">
        <!-- Main Content -->
        <div class="detail-main">

            <!-- Header card -->
            <div class="detail-header" data-aos="fade-up">
                <div class="detail-price">
                    <?= $p['price_type'] === 'rent' ? formatRentPrice($p['price']) : formatPrice($p['price']) ?>
                </div>
                <h1 class="detail-title"><?= e($p['title']) ?></h1>
                <div class="detail-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= e($p['address'] ?: $p['location_area'] . ', ' . $p['city'] . ', ' . $p['county']) ?>
                </div>

                <?php if ($p['bedrooms'] > 0 || $p['bathrooms'] > 0 || $p['size_sqm'] > 0): ?>
                <div class="detail-features">
                    <?php if ($p['bedrooms'] > 0): ?>
                    <div class="detail-feature">
                        <i class="fas fa-bed"></i>
                        <strong><?= $p['bedrooms'] ?></strong>
                        <span>Bedrooms</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($p['bathrooms'] > 0): ?>
                    <div class="detail-feature">
                        <i class="fas fa-bath"></i>
                        <strong><?= $p['bathrooms'] ?></strong>
                        <span>Bathrooms</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($p['toilets'] > 0): ?>
                    <div class="detail-feature">
                        <i class="fas fa-toilet"></i>
                        <strong><?= $p['toilets'] ?></strong>
                        <span>Toilets</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($p['size_sqm'] > 0): ?>
                    <div class="detail-feature">
                        <i class="fas fa-ruler-combined"></i>
                        <strong><?= number_format($p['size_sqm']) ?></strong>
                        <span>Sq. Meters</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($p['floors'] > 1): ?>
                    <div class="detail-feature">
                        <i class="fas fa-layer-group"></i>
                        <strong><?= $p['floors'] ?></strong>
                        <span>Floors</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($p['year_built']): ?>
                    <div class="detail-feature">
                        <i class="fas fa-calendar-alt"></i>
                        <strong><?= $p['year_built'] ?></strong>
                        <span>Year Built</span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="section-box" data-aos="fade-up">
                <h3><i class="fas fa-info-circle text-gold"></i> Property Description</h3>
                <div style="color:var(--text-secondary);line-height:1.9;font-size:0.95rem;">
                    <?= nl2br(e($p['description'])) ?>
                </div>
            </div>

            <!-- Details Table -->
            <div class="section-box" data-aos="fade-up">
                <h3><i class="fas fa-list text-gold"></i> Property Details</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <?php
                    $details = [
                        'Property ID'     => '#' . $p['id'],
                        'Type'            => $p['type_name'],
                        'Status'          => ucfirst($p['status']),
                        'Price Type'      => ucfirst($p['price_type']),
                        'Location'        => $p['location_area'],
                        'City'            => $p['city'],
                        'County'          => $p['county'],
                        'Size'            => $p['size_sqm'] > 0 ? number_format($p['size_sqm']) . ' sqm' : 'N/A',
                        'Bedrooms'        => $p['bedrooms'] > 0 ? $p['bedrooms'] : 'N/A',
                        'Bathrooms'       => $p['bathrooms'] > 0 ? $p['bathrooms'] : 'N/A',
                        'Floors'          => $p['floors'],
                        'Year Built'      => $p['year_built'] ?? 'N/A',
                    ];
                    foreach ($details as $label => $val):
                    ?>
                    <div style="display:flex;justify-content:space-between;padding:10px 14px;background:rgba(255,255,255,0.03);border-radius:8px;border:1px solid var(--glass-border);">
                        <span style="color:var(--text-secondary);font-size:0.85rem;"><?= $label ?></span>
                        <strong style="font-size:0.88rem;color:var(--text-primary);"><?= e((string)$val) ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Amenities -->
            <?php if (!empty($amenities)): ?>
            <div class="section-box" data-aos="fade-up">
                <h3><i class="fas fa-star text-gold"></i> Amenities & Features</h3>
                <div class="amenities-grid">
                    <?php foreach ($amenities as $amenity): ?>
                    <div class="amenity-item">
                        <i class="fas <?= $amenity['icon'] ?>"></i>
                        <?= e($amenity['label']) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Location Map -->
            <div class="section-box" data-aos="fade-up">
                <h3><i class="fas fa-map-marked-alt text-gold"></i> Location</h3>
                <div class="map-container">
                    <iframe
                        src="https://www.openstreetmap.org/export/embed.html?bbox=<?= $p['longitude']-0.01 ?>,<?= $p['latitude']-0.01 ?>,<?= $p['longitude']+0.01 ?>,<?= $p['latitude']+0.01 ?>&layer=mapnik&marker=<?= $p['latitude'] ?>,<?= $p['longitude'] ?>"
                        height="300" style="width:100%;border:none;border-radius:12px;"
                        loading="lazy"></iframe>
                </div>
                <p style="color:var(--text-secondary);font-size:0.85rem;margin-top:10px;">
                    <i class="fas fa-map-marker-alt text-gold"></i>
                    <?= e($p['address'] ?: $p['location_area'] . ', ' . $p['city']) ?>
                </p>
            </div>

            <!-- Image Gallery Thumbnails -->
            <?php if (!empty($images)): ?>
            <div class="section-box" data-aos="fade-up">
                <h3><i class="fas fa-images text-gold"></i> Photo Gallery</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;">
                    <?php foreach ($images as $img): ?>
                    <div style="border-radius:8px;overflow:hidden;cursor:pointer;height:100px;"
                         data-lightbox="<?= propertyImageUrl($img['image_path']) ?>">
                        <img src="<?= propertyImageUrl($img['image_path']) ?>" alt="<?= e($img['caption'] ?? 'Property photo') ?>"
                             style="width:100%;height:100%;object-fit:cover;transition:transform 0.3s ease;">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Sidebar -->
        <div>
            <!-- Contact Agent -->
            <div class="contact-sidebar" data-aos="fade-left">
                <?php if (!empty($p['agent_name'])): ?>
                <div class="contact-agent-header">
                    <img src="<?= agentPhotoUrl($p['agent_photo']) ?>" alt="<?= e($p['agent_name']) ?>" class="contact-agent-photo">
                    <div>
                        <div class="contact-agent-name"><?= e($p['agent_name']) ?></div>
                        <div class="contact-agent-title"><?= e($p['agent_title'] ?? 'Property Agent') ?></div>
                        <div style="color:var(--primary);font-size:0.8rem;"><?= starRating($p['agent_rating'] ?? 5) ?></div>
                    </div>
                </div>
                <div class="contact-buttons">
                    <a href="https://wa.me/<?= e($p['agent_whatsapp']) ?>?text=<?= urlencode('Hi! I\'m interested in: ' . $p['title'] . '. Please send more details.') ?>"
                       target="_blank" class="contact-btn contact-btn-whatsapp">
                        <i class="fab fa-whatsapp fa-lg"></i> WhatsApp Agent
                    </a>
                    <a href="tel:<?= e($p['agent_phone']) ?>" class="contact-btn contact-btn-call">
                        <i class="fas fa-phone-alt"></i> Call Agent
                    </a>
                    <a href="mailto:<?= e($p['agent_email']) ?>" class="contact-btn contact-btn-call" style="background:rgba(59,130,246,0.15);border-color:rgba(59,130,246,0.3);color:#3b82f6;">
                        <i class="fas fa-envelope"></i> Email Agent
                    </a>
                </div>
                <?php endif; ?>

                <!-- Inquiry Form -->
                <div style="border-top:1px solid var(--glass-border);padding-top:20px;margin-top:4px;">
                    <h4 style="font-size:1rem;margin-bottom:16px;"><i class="fas fa-paper-plane text-gold"></i> Send Inquiry</h4>

                    <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] ?>" data-auto-dismiss>
                        <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                        <?= e($flash['message']) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" data-validate>
                        <input type="hidden" name="send_inquiry" value="1">
                        <div class="form-group">
                            <input type="text" name="name" class="form-control" placeholder="Your Full Name *" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-control" placeholder="Email Address *" required>
                        </div>
                        <div class="form-group">
                            <input type="tel" name="phone" class="form-control" placeholder="Phone Number">
                        </div>
                        <div class="form-group">
                            <textarea name="message" class="form-control" rows="4"
                                      placeholder="I'm interested in this property. Please send more details..."
                                      required><?= "Hi, I'm interested in: " . e($p['title']) . ". Please send me more information." ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%;border-radius:12px;">
                            <i class="fas fa-paper-plane"></i> Send Inquiry
                        </button>
                    </form>
                </div>

                <!-- Share Buttons -->
                <div style="border-top:1px solid var(--glass-border);padding-top:16px;margin-top:16px;">
                    <p style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:10px;font-weight:600;">SHARE THIS PROPERTY</p>
                    <div style="display:flex;gap:8px;">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>"
                           target="_blank" class="btn btn-sm btn-glass" style="flex:1;justify-content:center;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://wa.me/?text=<?= urlencode($p['title'] . ' - ' . "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>"
                           target="_blank" class="btn btn-sm btn-glass" style="flex:1;justify-content:center;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($p['title']) ?>&url=<?= urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>"
                           target="_blank" class="btn btn-sm btn-glass" style="flex:1;justify-content:center;">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mortgage Teaser -->
            <div class="glass-card" style="padding:24px;margin-top:20px;text-align:center;">
                <i class="fas fa-calculator" style="font-size:2rem;color:var(--primary);margin-bottom:12px;display:block;"></i>
                <h4 style="margin-bottom:8px;">Mortgage Calculator</h4>
                <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:16px;">Calculate monthly repayments for this property</p>
                <a href="mortgage-calculator.php?price=<?= $p['price'] ?>" class="btn btn-outline" style="width:100%;justify-content:center;">
                    Calculate Now
                </a>
            </div>
        </div>
    </div>

    <!-- Similar Properties -->
    <?php if (!empty($similar)): ?>
    <div style="margin-top:60px;">
        <div class="section-header" style="text-align:left;">
            <h2>Similar <span class="text-gold">Properties</span></h2>
        </div>
        <div class="properties-grid" style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr));">
            <?php foreach ($similar as $sp): ?>
            <div class="property-card">
                <div class="property-image">
                    <img src="<?= propertyImageUrl($sp['cover_image']) ?>" alt="<?= e($sp['title']) ?>" loading="lazy">
                    <div class="property-image-overlay"></div>
                    <div class="property-price"><?= formatPrice($sp['price']) ?></div>
                </div>
                <div class="property-body">
                    <h3 class="property-title">
                        <a href="property-detail.php?id=<?= $sp['id'] ?>"><?= e($sp['title']) ?></a>
                    </h3>
                    <p class="property-location"><i class="fas fa-map-marker-alt"></i> <?= e($sp['location_area']) ?>, <?= e($sp['city']) ?></p>
                    <div class="property-footer">
                        <span style="color:var(--text-secondary);font-size:0.82rem;"><?= e($sp['type_name']) ?></span>
                        <a href="property-detail.php?id=<?= $sp['id'] ?>" class="btn btn-sm btn-glass">Details <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>
</section>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
    <button class="lightbox-close" id="lightboxClose"><i class="fas fa-times"></i></button>
    <img id="lightboxImg" src="" alt="Property Image">
</div>

<?php require_once 'includes/footer.php'; ?>
