<?php
/**
 * About Page
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = 'About Us';
$teamCount = $pdo->query("SELECT COUNT(*) FROM agents WHERE is_active=1")->fetchColumn();
$propCount = $pdo->query("SELECT COUNT(*) FROM properties WHERE is_active=1")->fetchColumn();
$testimonials = $pdo->query("SELECT * FROM testimonials WHERE is_approved=1 ORDER BY RAND() LIMIT 3")->fetchAll();

require_once 'includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <div class="section-tag" style="display:inline-flex;margin-bottom:12px;"><i class="fas fa-building"></i> About Us</div>
        <h1>Ruiru's Most <span class="text-gold">Trusted</span> Real Estate Agency</h1>
        <p style="color:var(--text-secondary);max-width:600px;margin:12px auto 0;">
            Over a decade of expertise connecting families and investors with their perfect properties in Ruiru
        </p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>About Us</span>
        </div>
    </div>
</section>

<!-- Who We Are -->
<section class="section-padding">
    <div class="container">
        <div class="about-grid">
            <div data-aos="fade-right">
                <div class="about-image-wrapper">
                    <img src="assets/images/about-office.jpg"
                         onerror="this.style.height='400px';this.style.background='linear-gradient(135deg, rgba(212,168,67,0.15), rgba(30,64,175,0.15))';this.style.display='flex';this.alt='';"
                         alt="Our Office in Ruiru"
                         style="width:100%;height:400px;object-fit:cover;border-radius:24px;border:1px solid var(--glass-border);">
                    <div class="about-badge">
                        <strong>10+</strong>
                        <span>Years in Ruiru</span>
                    </div>
                </div>
            </div>
            <div data-aos="fade-left">
                <div class="section-tag" style="display:inline-flex;margin-bottom:16px;"><i class="fas fa-info-circle"></i> Who We Are</div>
                <h2 style="margin-bottom:16px;">Your Trusted Partner in <span class="text-gold">Ruiru Real Estate</span></h2>
                <p style="color:var(--text-secondary);margin-bottom:16px;line-height:1.9;">
                    <?= e(setting('company_description')) ?>
                </p>
                <p style="color:var(--text-secondary);margin-bottom:28px;line-height:1.9;">
                    Ruiru's real estate landscape is our specialty. We understand every neighborhood, every growth corridor, and every opportunity in this dynamic town. Whether you're a first-time home buyer, a seasoned investor, or a business seeking commercial space, we have the expertise to guide you.
                </p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:32px;">
                    <?php
                    $highlights = [
                        ['fas fa-check-circle', 'Verified Properties'],
                        ['fas fa-file-contract', 'Clear Title Deeds'],
                        ['fas fa-handshake', 'Transparent Dealings'],
                        ['fas fa-map-marker-alt', 'Local Expertise'],
                        ['fas fa-shield-alt', 'Trusted Agency'],
                        ['fas fa-clock', '24/7 Support'],
                    ];
                    foreach ($highlights as $h):
                    ?>
                    <div style="display:flex;align-items:center;gap:10px;color:var(--text-secondary);font-size:0.9rem;">
                        <i class="fas <?= $h[0] ?>" style="color:var(--primary);font-size:1rem;"></i>
                        <?= $h[1] ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="display:flex;gap:16px;flex-wrap:wrap;">
                    <a href="properties.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Browse Properties
                    </a>
                    <a href="contact.php" class="btn btn-outline">
                        <i class="fas fa-phone-alt"></i> Talk to Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card" data-aos="fade-up">
                <div class="stat-icon"><i class="fas fa-home"></i></div>
                <span class="stat-number" data-counter data-target="<?= $propCount ?>" data-suffix="+">0</span>
                <p class="stat-label">Active Listings</p>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-icon"><i class="fas fa-handshake"></i></div>
                <span class="stat-number" data-counter data-target="500" data-suffix="+">0</span>
                <p class="stat-label">Properties Sold</p>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-icon"><i class="fas fa-smile"></i></div>
                <span class="stat-number" data-counter data-target="1200" data-suffix="+">0</span>
                <p class="stat-label">Happy Clients</p>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                <span class="stat-number" data-counter data-target="<?= $teamCount ?>" data-suffix="">0</span>
                <p class="stat-label">Expert Agents</p>
            </div>
        </div>
    </div>
</section>

<!-- Our Mission -->
<section class="section-padding" style="background:var(--bg-section);">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:24px;" data-aos="fade-up">
            <?php
            $pillars = [
                ['fas fa-bullseye', 'Our Mission', 'To make property ownership in Ruiru accessible to every family and business by providing transparent, professional, and client-focused real estate services.'],
                ['fas fa-eye', 'Our Vision', 'To become the most recognized real estate brand in Kiambu County, known for integrity, excellence, and transforming the property landscape of Ruiru.'],
                ['fas fa-heart', 'Our Values', 'Integrity, transparency, client satisfaction, and community development. We believe in doing real estate the right way — every time, for every client.'],
            ];
            foreach ($pillars as $i => $p):
            ?>
            <div class="glass-card" style="padding:36px;text-align:center;" data-aos-delay="<?= $i*100 ?>">
                <div class="feature-icon" style="margin:0 auto 20px;"><i class="fas <?= $p[0] ?>"></i></div>
                <h3 style="margin-bottom:12px;"><?= $p[1] ?></h3>
                <p style="color:var(--text-secondary);font-size:0.9rem;line-height:1.8;"><?= $p[2] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Testimonials -->
<?php if (!empty($testimonials)): ?>
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <div class="section-tag"><i class="fas fa-quote-left"></i> Testimonials</div>
            <h2>What <span>Clients Say</span></h2>
            <div class="divider"></div>
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
                        <span><?= e($t['client_title'] ?? '') ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
