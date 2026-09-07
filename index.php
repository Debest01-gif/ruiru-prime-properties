<?php
/**
 * Homepage - Ruiru Prime Properties
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Home';

// Fetch featured properties
$featuredProps = $pdo->query("
    SELECT p.*, pt.name as type_name, pt.slug as type_slug, a.name as agent_name, a.photo as agent_photo
    FROM properties p
    LEFT JOIN property_types pt ON p.property_type_id = pt.id
    LEFT JOIN agents a ON p.agent_id = a.id
    WHERE p.is_active = 1 AND p.featured = 1
    ORDER BY p.created_at DESC
    LIMIT 6
")->fetchAll();

// Latest properties
$latestProps = $pdo->query("
    SELECT p.*, pt.name as type_name, a.name as agent_name, a.photo as agent_photo
    FROM properties p
    LEFT JOIN property_types pt ON p.property_type_id = pt.id
    LEFT JOIN agents a ON p.agent_id = a.id
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC
    LIMIT 6
")->fetchAll();

// Agents
$agents = $pdo->query("SELECT * FROM agents WHERE is_active = 1 ORDER BY sort_order LIMIT 4")->fetchAll();

// Testimonials
$testimonials = $pdo->query("SELECT * FROM testimonials WHERE is_approved = 1 AND is_featured = 1 ORDER BY RAND() LIMIT 4")->fetchAll();

// Blog posts
$posts = $pdo->query("
    SELECT bp.*, u.name as author_name
    FROM blog_posts bp
    LEFT JOIN users u ON bp.author_id = u.id
    WHERE bp.is_published = 1
    ORDER BY bp.published_at DESC
    LIMIT 3
")->fetchAll();

// Stats
$stats = [
    'properties' => $pdo->query("SELECT COUNT(*) FROM properties WHERE is_active = 1")->fetchColumn(),
    'agents'     => $pdo->query("SELECT COUNT(*) FROM agents WHERE is_active = 1")->fetchColumn(),
    'sold'       => 500,
    'clients'    => 1200,
];

// Property types for search
$propTypes = $pdo->query("SELECT * FROM property_types ORDER BY id")->fetchAll();

require_once 'includes/header.php';
?>

<!-- ===== HERO SECTION ===== -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-bg-image"></div>
    <div class="hero-particles"></div>

    <div class="container">
        <div class="hero-content">
            <div class="hero-tag">
                <i class="fas fa-map-marker-alt"></i>
                Ruiru, Kiambu County — Kenya's Fastest Growing Real Estate Hub
            </div>

            <h1 class="hero-title">
                <?= nl2br(e(setting('hero_title'))) ?>
            </h1>

            <p class="hero-desc"><?= e(setting('hero_subtitle')) ?></p>

            <div class="hero-actions">
                <a href="properties.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Browse Properties
                </a>
                <a href="contact.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-phone-alt"></i> Free Consultation
                </a>
            </div>

            <!-- Search Box -->
            <div class="hero-search">
                <div class="search-tabs">
                    <button class="search-tab active" data-type="sale">For Sale</button>
                    <button class="search-tab" data-type="rent">For Rent</button>
                    <button class="search-tab" data-type="">All</button>
                </div>
                <form action="properties.php" method="GET" class="search-grid" id="filterForm">
                    <input type="hidden" name="type" id="searchType" value="sale">
                    <div class="search-field">
                        <label><i class="fas fa-search"></i> Keyword</label>
                        <input type="text" name="q" class="form-control" placeholder="e.g. Kimbo, 3 bedroom...">
                    </div>
                    <div class="search-field">
                        <label><i class="fas fa-home"></i> Property Type</label>
                        <select name="property_type" class="form-control">
                            <option value="">All Types</option>
                            <?php foreach ($propTypes as $pt): ?>
                            <option value="<?= e($pt['slug']) ?>"><?= e($pt['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="search-field">
                        <label><i class="fas fa-map-marker-alt"></i> Location</label>
                        <select name="location" class="form-control">
                            <option value="">All Areas</option>
                            <option value="Ruiru East">Ruiru East</option>
                            <option value="Kimbo">Kimbo</option>
                            <option value="Membley">Membley</option>
                            <option value="Gitambaya">Gitambaya</option>
                            <option value="Murera">Murera</option>
                            <option value="Ruiru CBD">Ruiru CBD</option>
                            <option value="Ruiru West">Ruiru West</option>
                            <option value="Thika Road">Thika Road</option>
                        </select>
                    </div>
                    <div class="search-field">
                        <label><i class="fas fa-bed"></i> Bedrooms</label>
                        <select name="bedrooms" class="form-control">
                            <option value="">Any</option>
                            <option value="1">1+</option>
                            <option value="2">2+</option>
                            <option value="3">3+</option>
                            <option value="4">4+</option>
                            <option value="5">5+</option>
                        </select>
                    </div>
                    <div class="search-field">
                        <button type="submit" class="btn btn-primary" style="width:100%;border-radius:12px;">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            <!-- Stats -->
            <div class="hero-stats" style="margin-top:36px;">
                <div class="hero-stat">
                    <span class="number" data-counter data-target="<?= $stats['properties'] ?>" data-suffix="+">0+</span>
                    <span class="label">Active Listings</span>
                </div>
                <div class="hero-stat">
                    <span class="number" data-counter data-target="<?= $stats['sold'] ?>" data-suffix="+">0+</span>
                    <span class="label">Properties Sold</span>
                </div>
                <div class="hero-stat">
                    <span class="number" data-counter data-target="<?= $stats['clients'] ?>" data-suffix="+">0+</span>
                    <span class="label">Happy Clients</span>
                </div>
                <div class="hero-stat">
                    <span class="number" data-counter data-target="10" data-suffix="+">0+</span>
                    <span class="label">Years Experience</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== STATS SECTION ===== -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card" data-aos="fade-up">
                <div class="stat-icon"><i class="fas fa-home"></i></div>
                <span class="stat-number" data-counter data-target="<?= $stats['properties'] ?>" data-suffix="+">0</span>
                <p class="stat-label">Properties Listed</p>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-icon"><i class="fas fa-handshake"></i></div>
                <span class="stat-number" data-counter data-target="500" data-suffix="+">0</span>
                <p class="stat-label">Properties Sold</p>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <span class="stat-number" data-counter data-target="<?= $stats['clients'] ?>" data-suffix="+">0</span>
                <p class="stat-label">Happy Clients</p>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                <span class="stat-number" data-counter data-target="<?= $stats['agents'] ?>" data-suffix="+">0</span>
                <p class="stat-label">Expert Agents</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== FEATURED PROPERTIES ===== -->
<?php if (!empty($featuredProps)): ?>
<section class="section-padding" id="featured">
    <div class="container">
        <div class="section-header">
            <div class="section-tag"><i class="fas fa-star"></i> Featured Properties</div>
            <h2>Our <span>Premium</span> Listings</h2>
            <div class="divider"></div>
            <p>Hand-picked exceptional properties in the best locations across Ruiru and its surrounding areas</p>
        </div>

        <div class="properties-grid">
            <?php foreach ($featuredProps as $i => $p): ?>
            <div class="property-card" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
                <div class="property-image">
                    <img src="<?= propertyImageUrl($p['cover_image']) ?>"
                         alt="<?= e($p['title']) ?>"
                         loading="lazy">
                    <div class="property-image-overlay"></div>

                    <div class="property-badges">
                        <span class="badge badge-gold">Featured</span>
                        <?php if ($p['price_type'] === 'rent'): ?>
                        <span class="badge badge-info">For Rent</span>
                        <?php else: ?>
                        <span class="badge badge-success">For Sale</span>
                        <?php endif; ?>
                        <?= statusBadge($p['status']) ?>
                    </div>

                    <div class="property-actions">
                        <a href="property-detail.php?id=<?= $p['id'] ?>" class="property-action-btn" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="https://wa.me/<?= setting('whatsapp_number') ?>?text=I'm interested in <?= urlencode($p['title']) ?>"
                           target="_blank" class="property-action-btn" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>

                    <div class="property-price">
                        <?= $p['price_type'] === 'rent' ? formatRentPrice($p['price']) : formatPrice($p['price']) ?>
                        <small><?= e($p['type_name']) ?> · <?= e($p['location_area']) ?></small>
                    </div>
                </div>

                <div class="property-body">
                    <h3 class="property-title">
                        <a href="property-detail.php?id=<?= $p['id'] ?>"><?= e($p['title']) ?></a>
                    </h3>
                    <p class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= e($p['address'] ?: $p['location_area'] . ', ' . $p['city']) ?>
                    </p>

                    <?php if ($p['bedrooms'] > 0 || $p['bathrooms'] > 0 || $p['size_sqm'] > 0): ?>
                    <div class="property-features">
                        <?php if ($p['bedrooms'] > 0): ?>
                        <div class="property-feature">
                            <i class="fas fa-bed"></i>
                            <span><?= $p['bedrooms'] ?> Beds</span>
                        </div>
                        <?php endif; ?>
                        <?php if ($p['bathrooms'] > 0): ?>
                        <div class="property-feature">
                            <i class="fas fa-bath"></i>
                            <span><?= $p['bathrooms'] ?> Baths</span>
                        </div>
                        <?php endif; ?>
                        <?php if ($p['size_sqm'] > 0): ?>
                        <div class="property-feature">
                            <i class="fas fa-ruler-combined"></i>
                            <span><?= number_format($p['size_sqm']) ?> sqm</span>
                        </div>
                        <?php endif; ?>
                        <?php if ($p['garage']): ?>
                        <div class="property-feature">
                            <i class="fas fa-car"></i>
                            <span>Garage</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="property-footer">
                        <div class="agent-mini">
                            <img src="<?= agentPhotoUrl($p['agent_photo']) ?>" alt="<?= e($p['agent_name'] ?? 'Agent') ?>">
                            <span><?= e($p['agent_name'] ?? 'Our Agent') ?></span>
                        </div>
                        <a href="property-detail.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-glass">
                            Details <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;margin-top:40px;">
            <a href="properties.php" class="btn btn-outline btn-lg">
                View All Properties <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== WHY CHOOSE US ===== -->
<section class="section-padding" style="background: linear-gradient(180deg, var(--bg-section) 0%, var(--bg-dark) 100%);">
    <div class="container">
        <div class="section-header">
            <div class="section-tag"><i class="fas fa-shield-alt"></i> Why Choose Us</div>
            <h2>Ruiru's Most <span>Trusted</span> Agency</h2>
            <div class="divider"></div>
            <p>We deliver exceptional real estate services backed by deep local knowledge and professional expertise</p>
        </div>

        <div class="features-grid">
            <div class="feature-card" data-aos="fade-up">
                <div class="feature-icon"><i class="fas fa-map-marked-alt"></i></div>
                <h3>Deep Local Knowledge</h3>
                <p>Over 10 years of experience in the Ruiru property market. We know every neighborhood, estate, and growth corridor.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-icon"><i class="fas fa-file-contract"></i></div>
                <h3>Clean Title Deeds</h3>
                <p>Every property we list is thoroughly verified. We conduct full due diligence to ensure clean title deeds and no encumbrances.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-icon"><i class="fas fa-comments-dollar"></i></div>
                <h3>Best Market Prices</h3>
                <p>Our extensive market data ensures you get the best value — whether buying, selling, or renting a property in Ruiru.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-icon"><i class="fas fa-headset"></i></div>
                <h3>24/7 Support</h3>
                <p>Our dedicated team is available around the clock via phone, email, and WhatsApp to answer your property questions.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Investment Advisory</h3>
                <p>Expert guidance on property investment in Ruiru's rapidly appreciating market. Maximize your returns with our insights.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-icon"><i class="fas fa-handshake"></i></div>
                <h3>End-to-End Service</h3>
                <p>From property search to title transfer, we guide you through every step of the buying or selling process seamlessly.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== LATEST PROPERTIES ===== -->
<?php if (!empty($latestProps)): ?>
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <div class="section-tag"><i class="fas fa-clock"></i> Latest Listings</div>
            <h2>Newly <span>Added</span> Properties</h2>
            <div class="divider"></div>
            <p>Fresh property listings added to our portfolio — be the first to view and secure your preferred property</p>
        </div>

        <div class="properties-grid">
            <?php foreach ($latestProps as $i => $p): ?>
            <div class="property-card" data-aos="fade-up" data-aos-delay="<?= $i * 60 ?>">
                <div class="property-image">
                    <img src="<?= propertyImageUrl($p['cover_image']) ?>"
                         alt="<?= e($p['title']) ?>" loading="lazy">
                    <div class="property-image-overlay"></div>
                    <div class="property-badges">
                        <?php if ($p['price_type'] === 'rent'): ?>
                        <span class="badge badge-info">For Rent</span>
                        <?php else: ?>
                        <span class="badge badge-success">For Sale</span>
                        <?php endif; ?>
                    </div>
                    <div class="property-actions">
                        <a href="property-detail.php?id=<?= $p['id'] ?>" class="property-action-btn">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="https://wa.me/<?= setting('whatsapp_number') ?>?text=I'm interested in <?= urlencode($p['title']) ?>"
                           target="_blank" class="property-action-btn">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                    <div class="property-price">
                        <?= $p['price_type'] === 'rent' ? formatRentPrice($p['price']) : formatPrice($p['price']) ?>
                        <small><?= e($p['type_name']) ?></small>
                    </div>
                </div>
                <div class="property-body">
                    <h3 class="property-title">
                        <a href="property-detail.php?id=<?= $p['id'] ?>"><?= e($p['title']) ?></a>
                    </h3>
                    <p class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= e($p['location_area']) ?>, <?= e($p['city']) ?>
                    </p>
                    <?php if ($p['bedrooms'] > 0 || $p['size_sqm'] > 0): ?>
                    <div class="property-features">
                        <?php if ($p['bedrooms'] > 0): ?>
                        <div class="property-feature"><i class="fas fa-bed"></i> <span><?= $p['bedrooms'] ?> Beds</span></div>
                        <?php endif; ?>
                        <?php if ($p['bathrooms'] > 0): ?>
                        <div class="property-feature"><i class="fas fa-bath"></i> <span><?= $p['bathrooms'] ?> Baths</span></div>
                        <?php endif; ?>
                        <?php if ($p['size_sqm'] > 0): ?>
                        <div class="property-feature"><i class="fas fa-ruler-combined"></i> <span><?= number_format($p['size_sqm']) ?> sqm</span></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <div class="property-footer">
                        <div class="agent-mini">
                            <img src="<?= agentPhotoUrl($p['agent_photo']) ?>" alt="<?= e($p['agent_name'] ?? 'Agent') ?>">
                            <span><?= e($p['agent_name'] ?? 'Agent') ?></span>
                        </div>
                        <a href="property-detail.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-glass">
                            Details <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;margin-top:40px;">
            <a href="properties.php" class="btn btn-primary btn-lg">
                <i class="fas fa-th-large"></i> View All Properties
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== OUR AGENTS ===== -->
<?php if (!empty($agents)): ?>
<section class="section-padding" style="background: var(--bg-section);">
    <div class="container">
        <div class="section-header">
            <div class="section-tag"><i class="fas fa-user-tie"></i> Meet The Team</div>
            <h2>Our Expert <span>Agents</span></h2>
            <div class="divider"></div>
            <p>Dedicated professionals with deep knowledge of the Ruiru property market ready to serve you</p>
        </div>

        <div class="agents-grid">
            <?php foreach ($agents as $i => $agent): ?>
            <div class="agent-card" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="agent-image">
                    <img src="<?= agentPhotoUrl($agent['photo']) ?>" alt="<?= e($agent['name']) ?>">
                    <div class="agent-image-overlay">
                        <?php if (!empty($agent['whatsapp'])): ?>
                        <a href="https://wa.me/<?= e($agent['whatsapp']) ?>" target="_blank" class="agent-social-btn">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <?php endif; ?>
                        <a href="tel:<?= e($agent['phone']) ?>" class="agent-social-btn">
                            <i class="fas fa-phone-alt"></i>
                        </a>
                        <a href="mailto:<?= e($agent['email']) ?>" class="agent-social-btn">
                            <i class="fas fa-envelope"></i>
                        </a>
                    </div>
                </div>
                <div class="agent-body">
                    <h3 class="agent-name">
                        <a href="agent-detail.php?id=<?= $agent['id'] ?>"><?= e($agent['name']) ?></a>
                    </h3>
                    <p class="agent-title"><?= e($agent['title']) ?></p>
                    <div class="agent-stats">
                        <div class="agent-stat">
                            <strong><?= $agent['experience_years'] ?>+</strong>
                            <span>Years Exp.</span>
                        </div>
                        <div class="agent-stat">
                            <strong><?= $agent['properties_sold'] ?></strong>
                            <span>Properties</span>
                        </div>
                        <div class="agent-stat">
                            <div class="agent-rating"><?= starRating($agent['rating']) ?></div>
                            <span><?= number_format($agent['rating'], 1) ?></span>
                        </div>
                    </div>
                    <div class="agent-contact">
                        <a href="agent-detail.php?id=<?= $agent['id'] ?>" class="btn btn-sm btn-glass">View Profile</a>
                        <a href="https://wa.me/<?= e($agent['whatsapp']) ?>" target="_blank" class="btn btn-sm" style="background:rgba(37,211,102,0.15);border:1px solid rgba(37,211,102,0.3);color:#25d366;border-radius:50px;padding:9px 16px;font-size:0.85rem;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;margin-top:40px;">
            <a href="agents.php" class="btn btn-outline">
                Meet All Agents <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== CALL TO ACTION BANNER ===== -->
<section style="padding: 80px 0; background: linear-gradient(135deg, rgba(212,168,67,0.12) 0%, rgba(30,64,175,0.1) 100%); border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border);">
    <div class="container" style="text-align: center;">
        <div data-aos="fade-up">
            <div class="section-tag" style="display:inline-flex;margin-bottom:20px;"><i class="fas fa-home"></i> List Your Property</div>
            <h2 style="font-size:2.2rem;margin-bottom:16px;">Have a Property to <span style="color:var(--primary)">Sell or Rent?</span></h2>
            <p style="color:var(--text-secondary);max-width:560px;margin:0 auto 32px;font-size:1.05rem;">
                Let our expert agents help you get the best value for your property in Ruiru's thriving real estate market.
            </p>
            <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
                <a href="contact.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane"></i> List My Property
                </a>
                <a href="mortgage-calculator.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-calculator"></i> Mortgage Calculator
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ===== TESTIMONIALS ===== -->
<?php if (!empty($testimonials)): ?>
<section class="section-padding" style="background: var(--bg-dark);">
    <div class="container">
        <div class="section-header">
            <div class="section-tag"><i class="fas fa-quote-left"></i> Client Testimonials</div>
            <h2>What Our <span>Clients Say</span></h2>
            <div class="divider"></div>
            <p>Real stories from real clients who found their perfect property with us in Ruiru</p>
        </div>

        <div class="testimonials-slider">
            <?php foreach ($testimonials as $i => $t): ?>
            <div class="testimonial-card" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="testimonial-rating"><?= starRating($t['rating']) ?></div>
                <p class="testimonial-text"><?= e($t['message']) ?></p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar"><?= strtoupper(substr($t['client_name'], 0, 1)) ?></div>
                    <div class="testimonial-author-info">
                        <strong><?= e($t['client_name']) ?></strong>
                        <span><?= e($t['client_title'] ?? '') ?><?php if ($t['property_type']): ?> · <?= e($t['property_type']) ?><?php endif; ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== BLOG SECTION ===== -->
<?php if (!empty($posts)): ?>
<section class="section-padding" style="background: var(--bg-section);">
    <div class="container">
        <div class="section-header">
            <div class="section-tag"><i class="fas fa-newspaper"></i> Latest News</div>
            <h2>Real Estate <span>Tips & Insights</span></h2>
            <div class="divider"></div>
            <p>Stay informed with the latest property trends, investment tips, and market updates in Ruiru</p>
        </div>

        <div class="blog-grid">
            <?php foreach ($posts as $i => $post): ?>
            <div class="blog-card" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="blog-image">
                    <img src="<?= propertyImageUrl($post['cover_image']) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
                    <div class="blog-category">
                        <span class="badge badge-gold"><?= e($post['category']) ?></span>
                    </div>
                </div>
                <div class="blog-body">
                    <div class="blog-meta">
                        <span><i class="fas fa-calendar"></i> <?= date('M j, Y', strtotime($post['published_at'])) ?></span>
                        <span><i class="fas fa-user"></i> <?= e($post['author_name'] ?? 'Admin') ?></span>
                        <span><i class="fas fa-eye"></i> <?= $post['views'] ?></span>
                    </div>
                    <h3 class="blog-title">
                        <a href="blog-detail.php?id=<?= $post['id'] ?>"><?= e($post['title']) ?></a>
                    </h3>
                    <p class="blog-excerpt"><?= e(truncate($post['excerpt'], 130)) ?></p>
                    <a href="blog-detail.php?id=<?= $post['id'] ?>" class="blog-read-more">
                        Read Article <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;margin-top:40px;">
            <a href="blog.php" class="btn btn-outline">
                View All Articles <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
    <button class="lightbox-close" id="lightboxClose"><i class="fas fa-times"></i></button>
    <img id="lightboxImg" src="" alt="Property Image">
</div>

<?php require_once 'includes/footer.php'; ?>
