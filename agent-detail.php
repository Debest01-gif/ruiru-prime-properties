<?php
/**
 * Agent Profile & Listings Page
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    redirect(SITE_URL . '/agents.php');
}

$stmt = $pdo->prepare("SELECT * FROM agents WHERE id = :id AND is_active = 1");
$stmt->execute([':id' => $id]);
$agent = $stmt->fetch();

if (!$agent) {
    redirect(SITE_URL . '/agents.php');
}

$pageTitle = $agent['name'] . ' - ' . $agent['title'];

// Handle direct inquiry to this agent
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $flash = ['type' => 'danger', 'msg' => 'Please fill in all required fields (Name, Email, Message).'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $flash = ['type' => 'danger', 'msg' => 'Please provide a valid email address.'];
    } else {
        $inqStmt = $pdo->prepare("
            INSERT INTO inquiries (agent_id, name, email, phone, subject, message, inquiry_type, status, ip_address)
            VALUES (:aid, :name, :email, :phone, :sub, :msg, 'general', 'new', :ip)
        ");
        $inqStmt->execute([
            ':aid'   => $agent['id'],
            ':name'  => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':sub'   => 'Direct inquiry to agent ' . $agent['name'],
            ':msg'   => $message,
            ':ip'    => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);
        $flash = ['type' => 'success', 'msg' => 'Thank you! Your message has been sent directly to ' . $agent['name'] . '. They will get back to you shortly.'];
    }
}

// Fetch properties listed by this agent
$propStmt = $pdo->prepare("
    SELECT p.*, pt.name as type_name 
    FROM properties p 
    LEFT JOIN property_types pt ON p.property_type_id = pt.id 
    WHERE p.agent_id = :id AND p.is_active = 1 
    ORDER BY p.featured DESC, p.id DESC
");
$propStmt->execute([':id' => $id]);
$agentProperties = $propStmt->fetchAll();

require_once 'includes/header.php';
?>

<!-- Agent Hero Banner -->
<section class="page-hero">
    <div class="container">
        <div class="hero-content" data-aos="fade-up">
            <span class="badge badge-gold mb-2"><i class="fas fa-user-tie"></i> Verified Consultant</span>
            <h1><?= e($agent['name']) ?></h1>
            <p><?= e($agent['title']) ?> · Specializing in <?= e($agent['specialization'] ?: 'Residential & Land') ?></p>
        </div>
    </div>
</section>

<section class="section py-5">
    <div class="container">
        
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" style="padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; background: <?= $flash['type'] === 'success' ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)' ?>; border: 1px solid <?= $flash['type'] === 'success' ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)' ?>; color: <?= $flash['type'] === 'success' ? '#34d399' : '#f87171' ?>;">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> <?= e($flash['msg']) ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 360px 1fr; gap: 2.5rem; align-items: start;">
            
            <!-- Left: Agent Profile Sidebar -->
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                
                <!-- Profile Card -->
                <div class="glass-card" style="border-radius: 20px; padding: 2rem; text-align: center; border: 1px solid rgba(255,255,255,0.08);">
                    <img src="<?= e(agentPhotoUrl($agent['photo'])) ?>" alt="<?= e($agent['name']) ?>"
                         style="width: 140px; height: 140px; border-radius: 50%; object-fit: cover; margin: 0 auto 1.25rem; border: 4px solid rgba(245,158,11,0.3); box-shadow: 0 8px 25px rgba(0,0,0,0.3);">
                    
                    <h2 style="font-size: 1.4rem; font-weight: 800; color: #f8fafc; margin-bottom: 0.25rem;"><?= e($agent['name']) ?></h2>
                    <p style="color: #38bdf8; font-size: 0.9rem; font-weight: 600; margin-bottom: 1.25rem;"><?= e($agent['title']) ?></p>

                    <!-- Stats Row -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; background: rgba(15,23,42,0.6); padding: 1rem 0.5rem; border-radius: 14px; margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,0.05);">
                        <div>
                            <div style="font-size: 1.2rem; font-weight: 800; color: #f8fafc;"><?= (int)$agent['experience_years'] ?>+</div>
                            <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase;">Yrs Exp</div>
                        </div>
                        <div>
                            <div style="font-size: 1.2rem; font-weight: 800; color: #10b981;"><?= (int)$agent['properties_sold'] ?></div>
                            <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase;">Sold</div>
                        </div>
                        <div>
                            <div style="font-size: 1.2rem; font-weight: 800; color: #eab308;"><?= number_format((float)$agent['rating'], 1) ?></div>
                            <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase;">Rating</div>
                        </div>
                    </div>

                    <!-- Contact Details -->
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; text-align: left; margin-bottom: 1.5rem; font-size: 0.9rem;">
                        <?php if ($agent['phone']): ?>
                            <a href="tel:<?= e($agent['phone']) ?>" style="color: #cbd5e1; text-decoration: none; display: flex; align-items: center; gap: 0.75rem;">
                                <i class="fas fa-phone-alt text-primary" style="width: 16px;"></i> <?= e($agent['phone']) ?>
                            </a>
                        <?php endif; ?>
                        <a href="mailto:<?= e($agent['email']) ?>" style="color: #cbd5e1; text-decoration: none; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-envelope text-primary" style="width: 16px;"></i> <?= e($agent['email']) ?>
                        </a>
                        <?php if ($agent['specialization']): ?>
                            <div style="color: #94a3b8; display: flex; align-items: center; gap: 0.75rem;">
                                <i class="fas fa-tag text-gold" style="width: 16px;"></i> <?= e($agent['specialization']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Direct WhatsApp Button -->
                    <?php if ($agent['whatsapp']): ?>
                        <a href="https://wa.me/<?= e($agent['whatsapp']) ?>?text=<?= urlencode("Hello " . $agent['name'] . ", I found your profile on Ruiru Prime Properties and would like to inquire about your listings.") ?>" 
                           target="_blank" class="btn btn-block" style="background: #22c55e; color: #fff; width: 100%; justify-content: center; font-weight: 700; margin-bottom: 0.5rem;">
                            <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Contact Agent Form -->
                <div class="glass-card" style="border-radius: 20px; padding: 1.75rem; border: 1px solid rgba(255,255,255,0.08);">
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-paper-plane text-primary"></i> Send Direct Message
                    </h3>
                    <form method="POST" action="<?= SITE_URL ?>/agent-detail.php?id=<?= $agent['id'] ?>">
                        <div style="margin-bottom: 1rem;">
                            <input type="text" name="name" required placeholder="Your Full Name *" class="form-control" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 10px; width: 100%; padding: 0.65rem 1rem;">
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <input type="email" name="email" required placeholder="Your Email Address *" class="form-control" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 10px; width: 100%; padding: 0.65rem 1rem;">
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <input type="tel" name="phone" placeholder="Phone / WhatsApp" class="form-control" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 10px; width: 100%; padding: 0.65rem 1rem;">
                        </div>
                        <div style="margin-bottom: 1.25rem;">
                            <textarea name="message" rows="4" required placeholder="Hi <?= e($agent['name']) ?>, I am interested in property investments in Ruiru..." class="form-control" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 10px; width: 100%; padding: 0.65rem 1rem;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            Send Message
                        </button>
                    </form>
                </div>

            </div>

            <!-- Right: Bio & Listings -->
            <div style="display: flex; flex-direction: column; gap: 2.5rem;">
                
                <!-- About Bio -->
                <div class="glass-card" style="border-radius: 20px; padding: 2rem; border: 1px solid rgba(255,255,255,0.08);">
                    <h3 style="font-size: 1.35rem; font-weight: 800; color: #f8fafc; margin-bottom: 1rem;">
                        About <?= e($agent['name']) ?>
                    </h3>
                    <p style="color: #cbd5e1; font-size: 1.05rem; line-height: 1.7; margin-bottom: 1.25rem;">
                        <?= nl2br(e($agent['bio'] ?: 'A committed and experienced property consultant providing personalized property acquisition and investment services across Ruiru, Kimbo, Membley, and greater Kiambu County.')) ?>
                    </p>
                </div>

                <!-- Properties Listed By Agent -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h3 style="font-size: 1.35rem; font-weight: 800; color: #f8fafc;">
                            Properties by <?= e($agent['name']) ?> (<?= count($agentProperties) ?>)
                        </h3>
                    </div>

                    <?php if (empty($agentProperties)): ?>
                        <div class="glass-card text-center p-5">
                            <i class="fas fa-home fa-3x mb-3 text-muted"></i>
                            <h4>No active listings currently</h4>
                            <p class="text-muted">Check back soon or message <?= e($agent['name']) ?> directly to request upcoming listings.</p>
                        </div>
                    <?php else: ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                            <?php foreach ($agentProperties as $p): ?>
                                <div class="glass-card property-card" style="border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.08);">
                                    <div style="height: 190px; overflow: hidden; position: relative;">
                                        <img src="<?= e(propertyImageUrl($p['cover_image'])) ?>" alt="<?= e($p['title']) ?>"
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                        <span class="badge" style="position: absolute; top: 12px; left: 12px; background: rgba(15,23,42,0.85); backdrop-filter: blur(8px); color: #38bdf8; font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 20px;">
                                            <?= e($p['type_name']) ?>
                                        </span>
                                        <span class="badge" style="position: absolute; top: 12px; right: 12px; background: <?= $p['price_type'] === 'rent' ? '#d97706' : '#2563eb' ?>; color: #fff; font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 20px;">
                                            <?= $p['price_type'] === 'rent' ? 'For Rent' : 'For Sale' ?>
                                        </span>
                                    </div>
                                    <div style="padding: 1.25rem;">
                                        <div style="color: #10b981; font-weight: 800; font-size: 1.15rem; margin-bottom: 0.35rem;">
                                            <?= formatPrice((float)$p['price']) ?><?= $p['price_type'] === 'rent' ? '/mo' : '' ?>
                                        </div>
                                        <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1.35;">
                                            <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $p['id'] ?>" style="color: #f8fafc; text-decoration: none;">
                                                <?= e($p['title']) ?>
                                            </a>
                                        </h4>
                                        <div style="color: #64748b; font-size: 0.8rem; margin-bottom: 1rem;">
                                            <i class="fas fa-map-marker-alt text-primary"></i> <?= e($p['location_area'] ?: $p['address']) ?>
                                        </div>
                                        <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" style="width: 100%; justify-content: center;">
                                            View Property
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
