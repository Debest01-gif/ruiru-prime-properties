<?php
/**
 * Blog & News Listing Page
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Blog & Market Insights';

// Category and Search filter
$cat = trim($_GET['cat'] ?? '');
$q   = trim($_GET['q'] ?? '');

$conditions = ['is_published = 1'];
$params = [];

if ($cat !== '') {
    $conditions[] = 'category = :cat';
    $params[':cat'] = $cat;
}
if ($q !== '') {
    $conditions[] = '(title LIKE :q OR excerpt LIKE :q OR tags LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}

$where = implode(' AND ', $conditions);

// Fetch categories for filter
$categories = $pdo->query("
    SELECT category, COUNT(*) as count 
    FROM blog_posts 
    WHERE is_published = 1 
    GROUP BY category 
    ORDER BY count DESC
")->fetchAll();

// Fetch articles
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE $where ORDER BY published_at DESC, id DESC");
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Recent posts for sidebar
$recentPosts = $pdo->query("SELECT id, title, slug, published_at, cover_image FROM blog_posts WHERE is_published = 1 ORDER BY published_at DESC LIMIT 4")->fetchAll();

require_once 'includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <div class="hero-content" data-aos="fade-up">
            <span class="badge badge-gold mb-2"><i class="fas fa-newspaper"></i> Ruiru Real Estate Intelligence</span>
            <h1>Market Insights & Guides</h1>
            <p>Expert property analysis, infrastructure updates, legal advice, and investment strategies in Ruiru and Kiambu County.</p>
        </div>
    </div>
</section>

<!-- Main Blog Section -->
<section class="section py-5">
    <div class="container">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2.5rem; align-items: start;">
            
            <!-- Left: Articles List -->
            <div>
                <!-- Filter Pills -->
                <div style="display: flex; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 2rem;">
                    <a href="<?= SITE_URL ?>/blog.php" 
                       style="padding: 0.5rem 1.1rem; border-radius: 30px; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.2s ease; background: <?= empty($cat) ? 'var(--primary, #2563eb)' : 'rgba(255,255,255,0.06)' ?>; color: #fff; border: 1px solid <?= empty($cat) ? 'var(--primary, #2563eb)' : 'rgba(255,255,255,0.1)' ?>;">
                        All Articles
                    </a>
                    <?php foreach ($categories as $c): ?>
                        <a href="<?= SITE_URL ?>/blog.php?cat=<?= urlencode($c['category']) ?>" 
                           style="padding: 0.5rem 1.1rem; border-radius: 30px; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.2s ease; background: <?= $cat === $c['category'] ? 'var(--primary, #2563eb)' : 'rgba(255,255,255,0.06)' ?>; color: #fff; border: 1px solid <?= $cat === $c['category'] ? 'var(--primary, #2563eb)' : 'rgba(255,255,255,0.1)' ?>;">
                            <?= e($c['category']) ?> (<?= $c['count'] ?>)
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($posts)): ?>
                    <div class="glass-card text-center p-5">
                        <i class="fas fa-newspaper fa-3x mb-3 text-muted"></i>
                        <h3>No articles found</h3>
                        <p class="text-muted">No blog posts match your search or category criteria.</p>
                        <a href="<?= SITE_URL ?>/blog.php" class="btn btn-primary mt-3">View All Articles</a>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 2rem;">
                        <?php foreach ($posts as $post): ?>
                            <article class="glass-card" style="border-radius: 20px; overflow: hidden; display: flex; flex-direction: column; border: 1px solid rgba(255,255,255,0.08); transition: transform 0.3s ease, box-shadow 0.3s ease;" data-aos="fade-up">
                                <?php if (!empty($post['cover_image'])): ?>
                                    <div style="height: 260px; overflow: hidden; position: relative;">
                                        <img src="<?= e(propertyImageUrl($post['cover_image'])) ?>" 
                                             alt="<?= e($post['title']) ?>"
                                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;"
                                             onmouseover="this.style.transform='scale(1.05)'"
                                             onmouseout="this.style.transform='scale(1)'">
                                        <span style="position: absolute; top: 1rem; left: 1rem; background: rgba(15,23,42,0.85); backdrop-filter: blur(8px); color: #38bdf8; font-size: 0.8rem; font-weight: 700; padding: 4px 12px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);">
                                            <?= e($post['category']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <div style="padding: 2rem;">
                                    <div style="display: flex; gap: 1rem; align-items: center; color: #94a3b8; font-size: 0.85rem; margin-bottom: 0.75rem;">
                                        <span><i class="far fa-calendar-alt text-primary"></i> <?= date('F d, Y', strtotime($post['published_at'] ?? $post['created_at'])) ?></span>
                                        <span>•</span>
                                        <span><i class="far fa-clock text-primary"></i> 4 min read</span>
                                        <?php if (!empty($post['category']) && empty($post['cover_image'])): ?>
                                            <span>•</span>
                                            <span style="color: #38bdf8; font-weight: 600;"><?= e($post['category']) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem; line-height: 1.35;">
                                        <a href="<?= SITE_URL ?>/blog-detail.php?slug=<?= urlencode($post['slug']) ?>" style="color: #f8fafc; text-decoration: none; transition: color 0.2s ease;">
                                            <?= e($post['title']) ?>
                                        </a>
                                    </h2>

                                    <p style="color: #94a3b8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem;">
                                        <?= e($post['excerpt'] ?: truncate(strip_tags($post['content']), 180)) ?>
                                    </p>

                                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 1.25rem;">
                                        <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                            <?php if (!empty($post['tags'])): 
                                                $tagsArr = explode(',', $post['tags']);
                                                foreach (array_slice($tagsArr, 0, 3) as $tg):
                                            ?>
                                                <span style="font-size: 0.75rem; color: #64748b; background: rgba(255,255,255,0.04); padding: 2px 8px; border-radius: 6px;">#<?= trim(e($tg)) ?></span>
                                            <?php endforeach; endif; ?>
                                        </div>
                                        <a href="<?= SITE_URL ?>/blog-detail.php?slug=<?= urlencode($post['slug']) ?>" class="btn btn-sm btn-outline-primary" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                            Read Full Guide <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: Sidebar -->
            <aside style="display: flex; flex-direction: column; gap: 2rem;">
                
                <!-- Search Box -->
                <div class="glass-card" style="border-radius: 16px; padding: 1.5rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: #f8fafc; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-search text-primary"></i> Search Articles
                    </h3>
                    <form method="GET" action="<?= SITE_URL ?>/blog.php" style="display: flex; gap: 0.5rem;">
                        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search Ruiru real estate..." 
                               class="form-control" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 10px; padding: 0.6rem 1rem; flex: 1;">
                        <button type="submit" class="btn btn-primary" style="border-radius: 10px; padding: 0.6rem 1rem;">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Recent Articles -->
                <div class="glass-card" style="border-radius: 16px; padding: 1.5rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-fire" style="color: #f59e0b;"></i> Popular Reads
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($recentPosts as $rp): ?>
                            <a href="<?= SITE_URL ?>/blog-detail.php?slug=<?= urlencode($rp['slug']) ?>" style="text-decoration: none; display: flex; gap: 0.75rem; align-items: center;">
                                <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(59,130,246,0.15); color: #38bdf8; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.1rem;">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <h4 style="font-size: 0.85rem; font-weight: 600; color: #e2e8f0; line-height: 1.35; margin-bottom: 2px;">
                                        <?= e($rp['title']) ?>
                                    </h4>
                                    <span style="font-size: 0.75rem; color: #64748b;">
                                        <?= date('M d, Y', strtotime($rp['published_at'])) ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Call to Action Card -->
                <div class="glass-card" style="border-radius: 16px; padding: 1.75rem; background: linear-gradient(135deg, rgba(37,99,235,0.2) 0%, rgba(15,23,42,0.8) 100%); border: 1px solid rgba(59,130,246,0.3); text-align: center;">
                    <i class="fas fa-handshake fa-2x mb-3" style="color: #38bdf8;"></i>
                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #f8fafc; margin-bottom: 0.5rem;">Need Property Consultation?</h3>
                    <p style="color: #94a3b8; font-size: 0.85rem; line-height: 1.5; margin-bottom: 1.25rem;">
                        Our licensed Ruiru property consultants are ready to advise you on land titles, price valuations, and safe acquisitions.
                    </p>
                    <a href="<?= SITE_URL ?>/contact.php" class="btn btn-primary btn-block" style="width: 100%; justify-content: center;">
                        Talk to an Agent Today
                    </a>
                </div>

            </aside>

        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
