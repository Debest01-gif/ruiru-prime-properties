<?php
/**
 * Admin: Testimonials Management
 */
$adminTitle = 'Testimonials';
require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';
$errors = [];

// Handle Action Toggles and Deletion
if ($action && $id > 0 && $token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        setFlash('danger', 'Security token invalid.');
        redirect(SITE_URL . '/admin/testimonials.php');
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Testimonial removed successfully.');
        redirect(SITE_URL . '/admin/testimonials.php');
    } elseif ($action === 'toggle_approval') {
        $stmt = $pdo->prepare("UPDATE testimonials SET is_approved = IF(is_approved=1, 0, 1) WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Approval status updated.');
        redirect(SITE_URL . '/admin/testimonials.php');
    } elseif ($action === 'toggle_featured') {
        $stmt = $pdo->prepare("UPDATE testimonials SET is_featured = IF(is_featured=1, 0, 1) WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Featured testimonial status updated.');
        redirect(SITE_URL . '/admin/testimonials.php');
    }
}

// Edit mode
$editTestimonial = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $editTestimonial = $stmt->fetch();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Invalid security token.';
    }

    $client_name   = trim($_POST['client_name'] ?? '');
    $client_title  = trim($_POST['client_title'] ?? '');
    $property_type = trim($_POST['property_type'] ?? 'Residential');
    $rating        = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $message       = trim($_POST['message'] ?? '');
    $is_approved   = isset($_POST['is_approved']) ? 1 : 0;
    $is_featured   = isset($_POST['is_featured']) ? 1 : 0;
    $testimonialId = (int)($_POST['testimonial_id'] ?? 0);

    if (empty($client_name)) $errors[] = 'Client name is required.';
    if (empty($message)) $errors[] = 'Review message is required.';

    if (empty($errors)) {
        if ($testimonialId > 0) {
            $stmt = $pdo->prepare("
                UPDATE testimonials SET
                    client_name = :client_name,
                    client_title = :client_title,
                    property_type = :property_type,
                    rating = :rating,
                    message = :message,
                    is_approved = :is_approved,
                    is_featured = :is_featured
                WHERE id = :id
            ");
            $stmt->execute([
                ':client_name'   => $client_name,
                ':client_title'  => $client_title,
                ':property_type' => $property_type,
                ':rating'        => $rating,
                ':message'       => $message,
                ':is_approved'   => $is_approved,
                ':is_featured'   => $is_featured,
                ':id'            => $testimonialId
            ]);
            setFlash('success', 'Testimonial updated successfully.');
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO testimonials (
                    client_name, client_title, property_type, rating, message,
                    is_approved, is_featured
                ) VALUES (
                    :client_name, :client_title, :property_type, :rating, :message,
                    :is_approved, :is_featured
                )
            ");
            $stmt->execute([
                ':client_name'   => $client_name,
                ':client_title'  => $client_title,
                ':property_type' => $property_type,
                ':rating'        => $rating,
                ':message'       => $message,
                ':is_approved'   => $is_approved,
                ':is_featured'   => $is_featured
            ]);
            setFlash('success', 'New client testimonial added successfully.');
        }
        redirect(SITE_URL . '/admin/testimonials.php');
    }
}

$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY id DESC")->fetchAll();
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

<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: #f8fafc; margin-bottom: 0.35rem;">Client Reviews & Testimonials</h1>
        <p style="color: #94a3b8; font-size: 0.95rem;">Curate and approve testimonials to build instant trust with buyers and property owners.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 360px; gap: 1.75rem; align-items: start;">

    <!-- Testimonials List Card -->
    <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden;">
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.06);">
            <span style="color: #94a3b8; font-size: 0.9rem; font-weight: 500;">
                <strong><?= count($testimonials) ?></strong> client reviews
            </span>
        </div>

        <div style="display: flex; flex-direction: column; divide-y: 1px solid rgba(255,255,255,0.05);">
            <?php if (empty($testimonials)): ?>
                <div style="padding: 3rem; text-align: center; color: #64748b;">
                    No testimonials submitted yet.
                </div>
            <?php else: ?>
                <?php foreach ($testimonials as $t): ?>
                    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                <strong style="color: #f8fafc; font-size: 0.95rem;"><?= e($t['client_name']) ?></strong>
                                <span style="color: #64748b; font-size: 0.8rem;">· <?= e($t['client_title']) ?></span>
                                <span style="color: #eab308; font-size: 0.75rem; margin-left: auto;">
                                    <?= starRating((float)$t['rating']) ?>
                                </span>
                            </div>
                            <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.5; margin: 0.5rem 0;">
                                "<?= e($t['message']) ?>"
                            </p>
                            <div style="display: flex; gap: 0.5rem; align-items: center; font-size: 0.75rem; color: #64748b;">
                                <span><?= e($t['property_type']) ?></span>
                                <span>•</span>
                                <span><?= date('M d, Y', strtotime($t['created_at'])) ?></span>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem; flex-shrink: 0;">
                            <div style="display: flex; gap: 0.4rem;">
                                <!-- Approved Badge/Toggle -->
                                <a href="<?= SITE_URL ?>/admin/testimonials.php?action=toggle_approval&id=<?= $t['id'] ?>&token=<?= $csrf ?>"
                                   title="Toggle Approval"
                                   style="padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-decoration: none; background: <?= $t['is_approved'] ? 'rgba(16,185,129,0.2)' : 'rgba(239,68,68,0.2)' ?>; color: <?= $t['is_approved'] ? '#10b981' : '#ef4444' ?>; border: 1px solid <?= $t['is_approved'] ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)' ?>;">
                                    <?= $t['is_approved'] ? 'Approved' : 'Pending' ?>
                                </a>

                                <!-- Featured Star Toggle -->
                                <a href="<?= SITE_URL ?>/admin/testimonials.php?action=toggle_featured&id=<?= $t['id'] ?>&token=<?= $csrf ?>"
                                   title="Toggle Featured"
                                   style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 6px; background: <?= $t['is_featured'] ? 'rgba(245,158,11,0.2)' : 'rgba(255,255,255,0.05)' ?>; color: <?= $t['is_featured'] ? '#f59e0b' : '#64748b' ?>; text-decoration: none;">
                                    <i class="fas fa-star" style="font-size: 0.75rem;"></i>
                                </a>
                            </div>

                            <div style="display: flex; gap: 0.4rem;">
                                <a href="<?= SITE_URL ?>/admin/testimonials.php?action=edit&id=<?= $t['id'] ?>" class="admin-btn admin-btn-sm admin-btn-secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= SITE_URL ?>/admin/testimonials.php?action=delete&id=<?= $t['id'] ?>&token=<?= $csrf ?>" 
                                   class="admin-btn admin-btn-sm admin-btn-danger confirm-delete"
                                   data-confirm="Delete testimonial by <?= addslashes($t['client_name']) ?>?" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add / Edit Form Card -->
    <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
        <h3 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-comment-dots" style="color: #f59e0b;"></i>
            <?= $editTestimonial ? 'Edit Review' : 'Add Testimonial' ?>
        </h3>

        <form method="POST" action="<?= SITE_URL ?>/admin/testimonials.php">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <?php if ($editTestimonial): ?>
                <input type="hidden" name="testimonial_id" value="<?= $editTestimonial['id'] ?>">
            <?php endif; ?>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Client Name *</label>
                <input type="text" name="client_name" value="<?= e($editTestimonial['client_name'] ?? '') ?>" required class="admin-form-input" style="width: 100%;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Title / Location</label>
                <input type="text" name="client_title" value="<?= e($editTestimonial['client_title'] ?? '') ?>" placeholder="e.g. Business Owner, Ruiru" class="admin-form-input" style="width: 100%;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.8rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Property Type</label>
                    <input type="text" name="property_type" value="<?= e($editTestimonial['property_type'] ?? 'Residential') ?>" class="admin-form-input" style="width: 100%;">
                </div>
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.8rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Rating (Stars)</label>
                    <select name="rating" class="admin-form-select" style="width: 100%;">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= (int)($editTestimonial['rating'] ?? 5) === $i ? 'selected' : '' ?>>
                                <?= $i ?> Stars
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Review Message *</label>
                <textarea name="message" rows="4" required class="admin-form-input" style="width: 100%;"><?= e($editTestimonial['message'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.6rem; margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; color: #cbd5e1; font-size: 0.85rem; cursor: pointer;">
                    <input type="checkbox" name="is_approved" value="1" <?= (!$editTestimonial || $editTestimonial['is_approved']) ? 'checked' : '' ?>>
                    <span>Approved & Visible</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; color: #cbd5e1; font-size: 0.85rem; cursor: pointer;">
                    <input type="checkbox" name="is_featured" value="1" <?= ($editTestimonial && $editTestimonial['is_featured']) ? 'checked' : '' ?>>
                    <span>Feature on Homepage</span>
                </label>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="admin-btn admin-btn-primary" style="flex: 1; justify-content: center;">
                    <i class="fas fa-save"></i> <?= $editTestimonial ? 'Update Review' : 'Save Review' ?>
                </button>
                <?php if ($editTestimonial): ?>
                    <a href="<?= SITE_URL ?>/admin/testimonials.php" class="admin-btn admin-btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
