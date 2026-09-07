<?php
/**
 * Agents Page
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Our Agents';
$agents = $pdo->query("SELECT * FROM agents WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
require_once 'includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="section-tag" style="display:inline-flex;margin-bottom:12px;"><i class="fas fa-user-tie"></i> Our Team</div>
        <h1>Meet Our Expert <span class="text-gold">Agents</span></h1>
        <p style="color:var(--text-secondary);max-width:560px;margin:12px auto 0;">
            Dedicated professionals with unrivalled knowledge of the Ruiru and Kiambu property market
        </p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Our Agents</span>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="agents-grid">
            <?php foreach ($agents as $i => $agent): ?>
            <div class="agent-card" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="agent-image">
                    <img src="<?= agentPhotoUrl($agent['photo']) ?>" alt="<?= e($agent['name']) ?>">
                    <div class="agent-image-overlay">
                        <?php if ($agent['whatsapp']): ?>
                        <a href="https://wa.me/<?= e($agent['whatsapp']) ?>" target="_blank" class="agent-social-btn"><i class="fab fa-whatsapp"></i></a>
                        <?php endif; ?>
                        <a href="tel:<?= e($agent['phone']) ?>" class="agent-social-btn"><i class="fas fa-phone-alt"></i></a>
                        <a href="mailto:<?= e($agent['email']) ?>" class="agent-social-btn"><i class="fas fa-envelope"></i></a>
                        <?php if ($agent['facebook_url']): ?>
                        <a href="<?= e($agent['facebook_url']) ?>" target="_blank" class="agent-social-btn"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="agent-body">
                    <h3 class="agent-name">
                        <a href="agent-detail.php?id=<?= $agent['id'] ?>"><?= e($agent['name']) ?></a>
                    </h3>
                    <p class="agent-title"><?= e($agent['title']) ?></p>

                    <?php if ($agent['specialization']): ?>
                    <p style="color:var(--text-muted);font-size:0.8rem;margin-bottom:12px;">
                        <i class="fas fa-tag text-gold"></i> <?= e($agent['specialization']) ?>
                    </p>
                    <?php endif; ?>

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
                            <span><?= number_format($agent['rating'], 1) ?> Rating</span>
                        </div>
                    </div>
                    <div class="agent-contact">
                        <a href="agent-detail.php?id=<?= $agent['id'] ?>" class="btn btn-sm btn-glass" style="flex:1;justify-content:center;">View Profile</a>
                        <a href="https://wa.me/<?= e($agent['whatsapp'] ?: setting('whatsapp_number')) ?>"
                           target="_blank" class="btn btn-sm"
                           style="background:rgba(37,211,102,0.15);border:1px solid rgba(37,211,102,0.3);color:#25d366;border-radius:50px;padding:9px 14px;font-size:0.85rem;display:flex;align-items:center;gap:6px;">
                            <i class="fab fa-whatsapp"></i> Chat
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Join Team CTA -->
        <div style="margin-top:70px;text-align:center;padding:60px 30px;background:var(--glass-bg);backdrop-filter:blur(10px);border:1px solid var(--glass-border);border-radius:var(--radius-lg);" data-aos="fade-up">
            <div class="section-tag" style="display:inline-flex;margin-bottom:16px;"><i class="fas fa-briefcase"></i> Careers</div>
            <h2 style="margin-bottom:12px;">Join Our <span class="text-gold">Winning Team</span></h2>
            <p style="color:var(--text-secondary);max-width:520px;margin:0 auto 28px;">
                Are you a passionate real estate professional? Join <?= e(setting('company_name')) ?> and grow your career in Ruiru's booming property market.
            </p>
            <a href="contact.php?subject=Join+Our+Team" class="btn btn-primary btn-lg">
                <i class="fas fa-paper-plane"></i> Apply Now
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
