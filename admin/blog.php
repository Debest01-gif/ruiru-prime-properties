<?php
/**
 * Admin: Blog Management
 */
$adminTitle = 'Blog Posts';
require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';
$errors = [];

// Handle Delete and Toggle Published
if ($action && $id > 0 && $token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        setFlash('danger', 'Security token invalid.');
        redirect(SITE_URL . '/admin/blog.php');
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Article deleted successfully.');
        redirect(SITE_URL . '/admin/blog.php');
    } elseif ($action === 'toggle_publish') {
        $stmt = $pdo->prepare("
            UPDATE blog_posts 
            SET is_published = IF(is_published=1, 0, 1),
                published_at = IF(is_published=0, NOW(), published_at) 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Article publication status updated.');
        redirect(SITE_URL . '/admin/blog.php');
    }
}

// Edit post mode
$editPost = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $editPost = $stmt->fetch();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Invalid security token.';
    }

    $title        = trim($_POST['title'] ?? '');
    $slug         = trim($_POST['slug'] ?? '');
    $category     = trim($_POST['category'] ?? 'Market Insights');
    $tags         = trim($_POST['tags'] ?? '');
    $excerpt      = trim($_POST['excerpt'] ?? '');
    $content      = trim($_POST['content'] ?? '');
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $postId       = (int)($_POST['post_id'] ?? 0);

    if (empty($title)) $errors[] = 'Article title is required.';
    if (empty($content)) $errors[] = 'Article content is required.';

    if (empty($slug)) {
        $slug = makeSlug($title);
    } else {
        $slug = makeSlug($slug);
    }

    // Slug unique check
    $check = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = :slug AND id != :id");
    $check->execute([':slug' => $slug, ':id' => $postId]);
    if ($check->fetchColumn() > 0) {
        $slug .= '-' . time();
    }

    // Cover image upload
    $cover_image = $editPost['cover_image'] ?? null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($_FILES['cover_image'], 'blog');
        if ($uploaded) {
            $cover_image = $uploaded;
        } else {
            $errors[] = 'Failed to upload cover image.';
        }
    }

    if (empty($errors)) {
        if ($postId > 0) {
            $stmt = $pdo->prepare("
                UPDATE blog_posts SET
                    title = :title, slug = :slug, category = :category, tags = :tags,
                    excerpt = :excerpt, content = :content, cover_image = :cover_image,
                    is_published = :is_published,
                    published_at = IF(:is_pub=1 AND published_at IS NULL, NOW(), published_at)
                WHERE id = :id
            ");
            $stmt->execute([
                ':title'        => $title,
                ':slug'         => $slug,
                ':category'     => $category,
                ':tags'         => $tags,
                ':excerpt'      => $excerpt,
                ':content'      => $content,
                ':cover_image'  => $cover_image,
                ':is_published' => $is_published,
                ':is_pub'       => $is_published,
                ':id'           => $postId
            ]);
            setFlash('success', 'Article updated successfully.');
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO blog_posts (
                    title, slug, category, tags, excerpt, content, cover_image,
                    is_published, published_at, author_id
                ) VALUES (
                    :title, :slug, :category, :tags, :excerpt, :content, :cover_image,
                    :is_published, IF(:is_pub=1, NOW(), NULL), :author_id
                )
            ");
            $stmt->execute([
                ':title'        => $title,
                ':slug'         => $slug,
                ':category'     => $category,
                ':tags'         => $tags,
                ':excerpt'      => $excerpt,
                ':content'      => $content,
                ':cover_image'  => $cover_image,
                ':is_published' => $is_published,
                ':is_pub'       => $is_published,
                ':author_id'    => $_SESSION['admin_id'] ?? 1
            ]);
            setFlash('success', 'New article published successfully.');
        }
        redirect(SITE_URL . '/admin/blog.php');
    }
}

// Fetch all posts
$posts = $pdo->query("SELECT * FROM blog_posts ORDER BY id DESC")->fetchAll();
$flash = getFlash();
$csrf = csrfToken();
?>

<?php if ($flash): ?>
    <div class="admin-alert admin-alert-<?= e($flash['type']) ?>" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; border-radius: 12px; background: <?= $flash['type'] === 'success' ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)' ?>; border: 1px solid <?= $flash['type'] === 'success' ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)' ?>; color: <?= $flash['type'] === 'success' ? '#34d399' : '#f87171' ?>; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <span><?= e($flash['message']) ?></span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;font-size:1.1rem;">&times;</button>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="admin-alert admin-alert-danger" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; border-radius: 12px; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #f87171;">
        <ul style="margin: 0; padding-left: 1.25rem;">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: #f8fafc; margin-bottom: 0.35rem;">Blog & News Articles</h1>
        <p style="color: #94a3b8; font-size: 0.95rem;">Publish insightful Ruiru real estate guides, price trends, and buyer tips.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 380px; gap: 1.75rem; align-items: start;">

    <!-- Posts Table -->
    <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden;">
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #94a3b8; font-size: 0.9rem; font-weight: 500;">
                <strong><?= count($posts) ?></strong> articles
            </span>
            <input type="text" id="adminTableFilter" placeholder="Filter articles..." 
                   class="admin-form-input" style="padding: 0.4rem 0.85rem; font-size: 0.85rem; width: 180px;">
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(30,41,59,0.4); color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 1rem 1.25rem;">Title</th>
                        <th style="padding: 1rem 1.25rem;">Category</th>
                        <th style="padding: 1rem 1.25rem; text-align: center;">Status</th>
                        <th style="padding: 1rem 1.25rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="4" style="padding: 3rem; text-align: center; color: #64748b;">
                                No articles published yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $p): ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                <td style="padding: 1rem 1.25rem;">
                                    <a href="<?= SITE_URL ?>/admin/blog.php?action=edit&id=<?= $p['id'] ?>" 
                                       style="color: #f8fafc; font-weight: 600; text-decoration: none; font-size: 0.95rem; display: block; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= e($p['title']) ?>
                                    </a>
                                    <div style="color: #64748b; font-size: 0.75rem; margin-top: 2px;">
                                        <?= date('M d, Y', strtotime($p['created_at'])) ?> · <?= (int)$p['views'] ?> views
                                    </div>
                                </td>

                                <td style="padding: 1rem 1.25rem; font-size: 0.85rem; color: #cbd5e1;">
                                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.08);">
                                        <?= e($p['category']) ?>
                                    </span>
                                </td>

                                <td style="padding: 1rem 1.25rem; text-align: center;">
                                    <a href="<?= SITE_URL ?>/admin/blog.php?action=toggle_publish&id=<?= $p['id'] ?>&token=<?= $csrf ?>" 
                                       title="<?= $p['is_published'] ? 'Unpublish' : 'Publish' ?>"
                                       style="display: inline-flex; align-items: center; justify-content: center; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-decoration: none; background: <?= $p['is_published'] ? 'rgba(16,185,129,0.2)' : 'rgba(100,116,139,0.2)' ?>; color: <?= $p['is_published'] ? '#10b981' : '#94a3b8' ?>; border: 1px solid <?= $p['is_published'] ? 'rgba(16,185,129,0.3)' : 'rgba(100,116,139,0.3)' ?>;">
                                        <?= $p['is_published'] ? 'Published' : 'Draft' ?>
                                    </a>
                                </td>

                                <td style="padding: 1rem 1.25rem; text-align: right; white-space: nowrap;">
                                    <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                        <a href="<?= SITE_URL ?>/admin/blog.php?action=edit&id=<?= $p['id'] ?>" 
                                           class="admin-btn admin-btn-sm admin-btn-secondary" title="Edit Article">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= SITE_URL ?>/admin/blog.php?action=delete&id=<?= $p['id'] ?>&token=<?= $csrf ?>" 
                                           class="admin-btn admin-btn-sm admin-btn-danger confirm-delete"
                                           data-confirm="Delete article '<?= addslashes($p['title']) ?>'?"
                                           title="Delete Article">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add / Edit Article Card -->
    <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
        <h3 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-pen" style="color: #3b82f6;"></i>
            <?= $editPost ? 'Edit Article' : 'Compose Article' ?>
        </h3>

        <form method="POST" action="<?= SITE_URL ?>/admin/blog.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <?php if ($editPost): ?>
                <input type="hidden" name="post_id" value="<?= $editPost['id'] ?>">
            <?php endif; ?>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Article Title *</label>
                <input type="text" name="title" data-slug-source value="<?= e($editPost['title'] ?? '') ?>" required class="admin-form-input" style="width: 100%;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">URL Slug</label>
                <input type="text" name="slug" data-slug-target value="<?= e($editPost['slug'] ?? '') ?>" class="admin-form-input" style="width: 100%; font-family: monospace; font-size: 0.8rem; color: #94a3b8;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Category</label>
                <input type="text" name="category" value="<?= e($editPost['category'] ?? 'Market Insights') ?>" placeholder="e.g. Market Insights, Buyer Guides" class="admin-form-input" style="width: 100%;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Short Excerpt</label>
                <textarea name="excerpt" rows="2" class="admin-form-input" style="width: 100%;"><?= e($editPost['excerpt'] ?? '') ?></textarea>
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Content (HTML / Markdown supported) *</label>
                <textarea name="content" rows="7" required class="admin-form-input" style="width: 100%; font-size: 0.85rem; line-height: 1.5;"><?= e($editPost['content'] ?? '') ?></textarea>
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Tags (comma separated)</label>
                <input type="text" name="tags" value="<?= e($editPost['tags'] ?? '') ?>" placeholder="ruiru, investment, land" class="admin-form-input" style="width: 100%;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Cover Image</label>
                <input type="file" name="cover_image" accept="image/*" class="admin-form-input" style="width: 100%; font-size: 0.8rem;">
            </div>

            <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 1.5rem;">
                <input type="checkbox" name="is_published" value="1" <?= (!$editPost || $editPost['is_published']) ? 'checked' : '' ?> id="postPublish">
                <label for="postPublish" style="color: #cbd5e1; font-size: 0.85rem; cursor: pointer;"><strong>Publish immediately</strong> on website</label>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="admin-btn admin-btn-primary" style="flex: 1; justify-content: center;">
                    <i class="fas fa-save"></i> <?= $editPost ? 'Update Post' : 'Save Article' ?>
                </button>
                <?php if ($editPost): ?>
                    <a href="<?= SITE_URL ?>/admin/blog.php" class="admin-btn admin-btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
