<?php
/**
 * Blog Article Detail Page
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$id   = (int)($_GET['id'] ?? 0);

if ($slug) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = :slug AND is_published = 1");
    $stmt->execute([':slug' => $slug]);
    $post = $stmt->fetch();
} elseif ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = :id AND is_published = 1");
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch();
} else {
    redirect(SITE_URL . '/blog.php');
}

if (!$post) {
    redirect(SITE_URL . '/blog.php');
}

// Increment view count
$pdo->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = :id")->execute([':id' => $post['id']]);

$pageTitle = $post['title'];

// Fetch related articles
$relStmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id != :id AND is_published = 1 ORDER BY id DESC LIMIT 3");
$relStmt->execute([':id' => $post['id']]);
$related = $relStmt->fetchAll();

$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

require_once 'includes/header.php';
?>

<!-- Article Header Hero -->
<section class="page-hero" style="padding: 6rem 0 3.5rem;">
    <div class="container">
        <div style="max-width: 820px; margin: 0 auto; text-align: center;" data-aos="fade-up">
            <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(59,130,246,0.15); border: 1px solid rgba(59,130,246,0.3); color: #38bdf8; font-size: 0.8rem; font-weight: 700; padding: 4px 14px; border-radius: 20px; margin-bottom: 1rem; text-transform: uppercase;">
                <i class="fas fa-bookmark"></i> <?= e($post['category']) ?>
            </div>
            <h1 style="font-size: 2.35rem; font-weight: 800; line-height: 1.3; color: #f8fafc; margin-bottom: 1.25rem;">
                <?= e($post['title']) ?>
            </h1>
            <div style="display: flex; justify-content: center; align-items: center; gap: 1.25rem; color: #94a3b8; font-size: 0.9rem; flex-wrap: wrap;">
                <span><i class="far fa-user text-primary"></i> Ruiru Property Intelligence</span>
                <span>•</span>
                <span><i class="far fa-calendar text-primary"></i> <?= date('F d, Y', strtotime($post['published_at'] ?? $post['created_at'])) ?></span>
                <span>•</span>
                <span><i class="far fa-eye text-primary"></i> <?= (int)$post['views'] + 1 ?> views</span>
            </div>
        </div>
    </div>
</section>

<!-- Article Body Section -->
<section class="section py-5">
    <div class="container">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2.5rem; align-items: start;">
            
            <!-- Left: Full Content -->
            <article class="glass-card" style="border-radius: 20px; padding: 2.5rem; border: 1px solid rgba(255,255,255,0.08);">
                
                <?php if (!empty($post['cover_image'])): ?>
                    <div style="margin-bottom: 2rem; border-radius: 16px; overflow: hidden;">
                        <img src="<?= e(propertyImageUrl($post['cover_image'])) ?>" alt="<?= e($post['title']) ?>"
                             style="width: 100%; max-height: 440px; object-fit: cover;">
                    </div>
                <?php endif; ?>

                <?php if (!empty($post['excerpt'])): ?>
                    <div style="font-size: 1.15rem; font-weight: 500; color: #cbd5e1; line-height: 1.7; margin-bottom: 2rem; padding-left: 1.25rem; border-left: 4px solid #3b82f6; font-style: italic;">
                        <?= e($post['excerpt']) ?>
                    </div>
                <?php endif; ?>

                <!-- Article Body HTML -->
                <div class="article-content" style="color: #e2e8f0; font-size: 1.05rem; line-height: 1.8;">
                    <?= $post['content'] ?>
                </div>

                <!-- Tags -->
                <?php if (!empty($post['tags'])): ?>
                    <div style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <span style="color: #94a3b8; font-size: 0.85rem; font-weight: 600;"><i class="fas fa-tags"></i> Tags:</span>
                        <?php foreach (explode(',', $post['tags']) as $tag): ?>
                            <a href="<?= SITE_URL ?>/blog.php?q=<?= urlencode(trim($tag)) ?>" 
                               style="font-size: 0.8rem; color: #38bdf8; background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.2); padding: 3px 10px; border-radius: 6px; text-decoration: none;">
                                #<?= trim(e($tag)) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Social Share Bar -->
                <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(15,23,42,0.5); border-radius: 14px; border: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div style="font-weight: 600; color: #f8fafc; font-size: 0.95rem;">
                        <i class="fas fa-share-alt text-primary"></i> Share this article:
                    </div>
                    <div style="display: flex; gap: 0.6rem;">
                        <a href="https://wa.me/?text=<?= urlencode($post['title'] . ' ' . $currentUrl) ?>" target="_blank" 
                           class="btn btn-sm" style="background: #22c55e; color: #fff; font-weight: 600;">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($post['title']) ?>&url=<?= urlencode($currentUrl) ?>" target="_blank" 
                           class="btn btn-sm" style="background: #1da1f2; color: #fff; font-weight: 600;">
                            <i class="fab fa-twitter"></i> X
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>" target="_blank" 
                           class="btn btn-sm" style="background: #1877f2; color: #fff; font-weight: 600;">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                    </div>
                </div>

            </article>

            <!-- Right: Sidebar -->
            <aside style="display: flex; flex-direction: column; gap: 2rem;">
                
                <!-- Related Articles -->
                <div class="glass-card" style="border-radius: 16px; padding: 1.5rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-book-open text-primary"></i> Related Articles
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <?php foreach ($related as $rel): ?>
                            <div>
                                <span style="font-size: 0.75rem; color: #38bdf8; text-transform: uppercase; font-weight: 700;"><?= e($rel['category']) ?></span>
                                <h4 style="font-size: 0.95rem; font-weight: 600; margin: 3px 0 6px;">
                                    <a href="<?= SITE_URL ?>/blog-detail.php?slug=<?= urlencode($rel['slug']) ?>" style="color: #f8fafc; text-decoration: none; line-height: 1.35;">
                                        <?= e($rel['title']) ?>
                                    </a>
                                </h4>
                                <span style="font-size: 0.75rem; color: #64748b;">
                                    <?= date('M d, Y', strtotime($rel['published_at'] ?? $rel['created_at'])) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Featured Listings Teaser -->
                <div class="glass-card" style="border-radius: 16px; padding: 1.75rem; background: linear-gradient(135deg, rgba(37,99,235,0.2) 0%, rgba(15,23,42,0.8) 100%); border: 1px solid rgba(59,130,246,0.3); text-align: center;">
                    <i class="fas fa-home fa-2x mb-3" style="color: #38bdf8;"></i>
                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #f8fafc; margin-bottom: 0.5rem;">Ready to Find Your Home?</h3>
                    <p style="color: #94a3b8; font-size: 0.85rem; line-height: 1.5; margin-bottom: 1.25rem;">
                        Explore our verified listings in Kimbo, Membley, Ruiru East, and Ruiru CBD.
                    </p>
                    <a href="<?= SITE_URL ?>/properties.php" class="btn btn-primary btn-block" style="width: 100%; justify-content: center;">
                        Browse Properties
                    </a>
                </div>

            </aside>

        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
