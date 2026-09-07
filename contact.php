<?php
/**
 * Contact Page
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Contact Us';
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $type    = $_POST['inquiry_type'] ?? 'general';
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $message) {
        $stmt = $pdo->prepare("INSERT INTO inquiries (name, email, phone, subject, message, inquiry_type, ip_address) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$name, $email, $phone, $subject, $message, $type, $_SERVER['REMOTE_ADDR']]);
        $flash = ['type' => 'success', 'message' => '✅ Thank you! Your message has been received. We\'ll respond within 24 hours.'];
    } else {
        $flash = ['type' => 'danger', 'message' => 'Please fill in all required fields.'];
    }
}

$agents = $pdo->query("SELECT * FROM agents WHERE is_active=1 ORDER BY sort_order LIMIT 4")->fetchAll();

require_once 'includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="section-tag" style="display:inline-flex;margin-bottom:12px;"><i class="fas fa-envelope"></i> Get In Touch</div>
        <h1>Contact <span class="text-gold">Us</span></h1>
        <p style="color:var(--text-secondary);max-width:500px;margin:12px auto 0;">
            We're here to help you find your perfect property. Reach out and our team will respond promptly.
        </p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Contact</span>
        </div>
    </div>
</section>

<section class="section-padding">
<div class="container">

    <div class="contact-grid">
        <!-- Info -->
        <div>
            <div class="contact-info-card" data-aos="fade-right">
                <h3 style="margin-bottom:28px;"><i class="fas fa-map-marker-alt text-gold"></i> Find Us</h3>

                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="contact-info-text">
                        <h4>Office Address</h4>
                        <p><?= e(setting('company_address')) ?></p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-phone-alt"></i></div>
                    <div class="contact-info-text">
                        <h4>Phone Numbers</h4>
                        <a href="tel:<?= e(setting('company_phone')) ?>"><?= e(setting('company_phone')) ?></a><br>
                        <a href="tel:<?= e(setting('company_phone2')) ?>"><?= e(setting('company_phone2')) ?></a>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-envelope"></i></div>
                    <div class="contact-info-text">
                        <h4>Email Address</h4>
                        <a href="mailto:<?= e(setting('company_email')) ?>"><?= e(setting('company_email')) ?></a>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fab fa-whatsapp"></i></div>
                    <div class="contact-info-text">
                        <h4>WhatsApp</h4>
                        <a href="https://wa.me/<?= e(setting('whatsapp_number')) ?>" target="_blank">
                            Chat with us instantly
                        </a>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-clock"></i></div>
                    <div class="contact-info-text">
                        <h4>Working Hours</h4>
                        <p>Mon – Sat: 8:00 AM – 6:00 PM<br>Sunday: 10:00 AM – 3:00 PM</p>
                    </div>
                </div>

                <!-- Social -->
                <div style="padding-top:20px;border-top:1px solid var(--glass-border);">
                    <h4 style="margin-bottom:12px;font-size:0.9rem;color:var(--text-secondary);text-transform:uppercase;letter-spacing:1px;">Follow Us</h4>
                    <div class="social-links">
                        <a href="<?= e(setting('facebook_url')) ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= e(setting('instagram_url')) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="<?= e(setting('twitter_url')) ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                        <a href="https://wa.me/<?= e(setting('whatsapp_number')) ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div class="map-container" style="margin-top:24px;" data-aos="fade-up">
                <iframe
                    src="https://www.openstreetmap.org/export/embed.html?bbox=36.94,−1.16,36.99,−1.12&layer=mapnik&marker=-1.1453,36.9630"
                    height="280" style="width:100%;border:none;" loading="lazy"></iframe>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="contact-form-card" data-aos="fade-left">
            <h3 style="margin-bottom:8px;"><i class="fas fa-paper-plane text-gold"></i> Send Us a Message</h3>
            <p style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:28px;">
                Fill in the form below and one of our agents will get back to you within 24 hours.
            </p>

            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>" data-auto-dismiss>
                <?= e($flash['message']) ?>
            </div>
            <?php endif; ?>

            <form method="POST" data-validate>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+254 7xx xxx xxx">
                    </div>
                    <div class="form-group">
                        <label>Inquiry Type</label>
                        <select name="inquiry_type" class="form-control">
                            <option value="general">General Inquiry</option>
                            <option value="property">Property Interest</option>
                            <option value="valuation">Property Valuation</option>
                            <option value="partnership">Partnership</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="What is this about?"
                           value="<?= e($_GET['subject'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Your Message *</label>
                    <textarea name="message" class="form-control" rows="6"
                              placeholder="Tell us what you're looking for — property type, location, budget, any questions..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width:100%;border-radius:12px;">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
    </div>

    <!-- Agents Contact -->
    <?php if (!empty($agents)): ?>
    <div style="margin-top:70px;">
        <div class="section-header">
            <div class="section-tag"><i class="fas fa-user-tie"></i> Direct Contact</div>
            <h2>Speak Directly to an <span class="text-gold">Agent</span></h2>
            <div class="divider"></div>
            <p style="color:var(--text-secondary);max-width:500px;margin:0 auto;">
                Contact one of our specialist agents directly for faster, personalized service
            </p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:20px;">
            <?php foreach ($agents as $agent): ?>
            <div class="glass-card" style="padding:24px;text-align:center;" data-aos="fade-up">
                <img src="<?= agentPhotoUrl($agent['photo']) ?>"
                     alt="<?= e($agent['name']) ?>"
                     style="width:70px;height:70px;border-radius:50%;object-fit:cover;border:3px solid rgba(212,168,67,0.3);margin-bottom:12px;">
                <h4 style="margin-bottom:4px;"><?= e($agent['name']) ?></h4>
                <p style="color:var(--primary);font-size:0.82rem;margin-bottom:14px;"><?= e($agent['title']) ?></p>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <a href="tel:<?= e($agent['phone']) ?>" class="btn btn-sm btn-glass" style="justify-content:center;">
                        <i class="fas fa-phone-alt"></i> <?= e($agent['phone']) ?>
                    </a>
                    <a href="https://wa.me/<?= e($agent['whatsapp'] ?: setting('whatsapp_number')) ?>"
                       target="_blank" class="btn btn-sm" style="justify-content:center;background:rgba(37,211,102,0.15);border:1px solid rgba(37,211,102,0.3);color:#25d366;border-radius:50px;padding:9px 14px;font-size:0.85rem;display:flex;align-items:center;gap:8px;">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>
</section>

<?php require_once 'includes/footer.php'; ?>
