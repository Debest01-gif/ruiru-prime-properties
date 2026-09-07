<?php
/**
 * Admin: Agents Management
 */
$adminTitle = 'Agents';
require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';
$errors = [];

// Handle Delete and Toggle
if ($action && $id > 0 && $token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        setFlash('danger', 'Security token invalid. Please try again.');
        redirect(SITE_URL . '/admin/agents.php');
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM agents WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Agent deleted successfully.');
        redirect(SITE_URL . '/admin/agents.php');
    } elseif ($action === 'toggle_active') {
        $stmt = $pdo->prepare("UPDATE agents SET is_active = IF(is_active=1, 0, 1) WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Agent status updated.');
        redirect(SITE_URL . '/admin/agents.php');
    }
}

// Handle Add / Edit Agent
$editAgent = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM agents WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $editAgent = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Invalid security token.';
    }

    $name             = trim($_POST['name'] ?? '');
    $title            = trim($_POST['title'] ?? 'Property Agent');
    $email            = trim($_POST['email'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $whatsapp         = trim($_POST['whatsapp'] ?? '');
    $bio              = trim($_POST['bio'] ?? '');
    $specialization   = trim($_POST['specialization'] ?? '');
    $experience_years = (int)($_POST['experience_years'] ?? 1);
    $properties_sold  = (int)($_POST['properties_sold'] ?? 0);
    $rating           = (float)($_POST['rating'] ?? 5.0);
    $is_active        = isset($_POST['is_active']) ? 1 : 0;
    $sort_order       = (int)($_POST['sort_order'] ?? 0);
    $agentId          = (int)($_POST['agent_id'] ?? 0);

    if (empty($name)) $errors[] = 'Agent name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';

    // Photo upload
    $photo = $editAgent['photo'] ?? null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($_FILES['photo'], 'agents');
        if ($uploaded) {
            $photo = $uploaded;
        } else {
            $errors[] = 'Failed to upload agent photo.';
        }
    }

    if (empty($errors)) {
        if ($agentId > 0) {
            // Update
            $stmt = $pdo->prepare("
                UPDATE agents SET
                    name = :name, title = :title, email = :email, phone = :phone,
                    whatsapp = :whatsapp, bio = :bio, photo = :photo,
                    specialization = :specialization, experience_years = :experience_years,
                    properties_sold = :properties_sold, rating = :rating,
                    is_active = :is_active, sort_order = :sort_order
                WHERE id = :id
            ");
            $stmt->execute([
                ':name'             => $name,
                ':title'            => $title,
                ':email'            => $email,
                ':phone'            => $phone,
                ':whatsapp'         => $whatsapp,
                ':bio'              => $bio,
                ':photo'            => $photo,
                ':specialization'   => $specialization,
                ':experience_years' => $experience_years,
                ':properties_sold'  => $properties_sold,
                ':rating'           => $rating,
                ':is_active'        => $is_active,
                ':sort_order'       => $sort_order,
                ':id'               => $agentId
            ]);
            setFlash('success', 'Agent updated successfully.');
        } else {
            // Insert
            $stmt = $pdo->prepare("
                INSERT INTO agents (
                    name, title, email, phone, whatsapp, bio, photo,
                    specialization, experience_years, properties_sold, rating,
                    is_active, sort_order
                ) VALUES (
                    :name, :title, :email, :phone, :whatsapp, :bio, :photo,
                    :specialization, :experience_years, :properties_sold, :rating,
                    :is_active, :sort_order
                )
            ");
            $stmt->execute([
                ':name'             => $name,
                ':title'            => $title,
                ':email'            => $email,
                ':phone'            => $phone,
                ':whatsapp'         => $whatsapp,
                ':bio'              => $bio,
                ':photo'            => $photo,
                ':specialization'   => $specialization,
                ':experience_years' => $experience_years,
                ':properties_sold'  => $properties_sold,
                ':rating'           => $rating,
                ':is_active'        => $is_active,
                ':sort_order'       => $sort_order
            ]);
            setFlash('success', 'New agent added successfully.');
        }
        redirect(SITE_URL . '/admin/agents.php');
    }
}

// Fetch all agents
$agents = $pdo->query("SELECT * FROM agents ORDER BY sort_order ASC, id DESC")->fetchAll();
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
        <h1 style="font-size: 1.75rem; font-weight: 800; color: #f8fafc; margin-bottom: 0.35rem;">Real Estate Agents</h1>
        <p style="color: #94a3b8; font-size: 0.95rem;">Manage your Ruiru property consultant team profiles, contact channels, and metrics.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 1.75rem; align-items: start;">
    
    <!-- Agents Table Card -->
    <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden;">
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #94a3b8; font-size: 0.9rem; font-weight: 500;">
                <strong><?= count($agents) ?></strong> registered agents
            </span>
            <input type="text" id="adminTableFilter" placeholder="Search agents..." 
                   class="admin-form-input" style="padding: 0.4rem 0.85rem; font-size: 0.85rem; width: 180px;">
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(30,41,59,0.4); color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 1rem 1.25rem;">Agent</th>
                        <th style="padding: 1rem 1.25rem;">Specialization</th>
                        <th style="padding: 1rem 1.25rem;">Track Record</th>
                        <th style="padding: 1rem 1.25rem; text-align: center;">Status</th>
                        <th style="padding: 1rem 1.25rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($agents)): ?>
                        <tr>
                            <td colspan="5" style="padding: 3rem; text-align: center; color: #64748b;">
                                No agents added yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($agents as $ag): ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                <!-- Photo & Info -->
                                <td style="padding: 1rem 1.25rem;">
                                    <div style="display: flex; align-items: center; gap: 0.85rem;">
                                        <img src="<?= e(agentPhotoUrl($ag['photo'] ?? '')) ?>" 
                                             alt="<?= e($ag['name']) ?>"
                                             style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; background: #1e293b; flex-shrink: 0; border: 2px solid rgba(255,255,255,0.1);">
                                        <div>
                                            <div style="font-weight: 600; color: #f8fafc; font-size: 0.95rem;"><?= e($ag['name']) ?></div>
                                            <div style="color: #38bdf8; font-size: 0.8rem;"><?= e($ag['title']) ?></div>
                                            <div style="color: #64748b; font-size: 0.75rem; margin-top: 2px;">
                                                <?= e($ag['phone'] ?: $ag['email']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Specialization -->
                                <td style="padding: 1rem 1.25rem; color: #cbd5e1; font-size: 0.85rem;">
                                    <?= e($ag['specialization'] ?: 'General') ?>
                                </td>

                                <!-- Track Record -->
                                <td style="padding: 1rem 1.25rem; font-size: 0.85rem;">
                                    <div style="color: #10b981; font-weight: 600;">
                                        <?= (int)$ag['properties_sold'] ?> deals closed
                                    </div>
                                    <div style="color: #eab308; font-size: 0.75rem;">
                                        <i class="fas fa-star"></i> <?= number_format((float)$ag['rating'], 1) ?> rating · <?= (int)$ag['experience_years'] ?> yrs exp
                                    </div>
                                </td>

                                <!-- Status -->
                                <td style="padding: 1rem 1.25rem; text-align: center;">
                                    <a href="<?= SITE_URL ?>/admin/agents.php?action=toggle_active&id=<?= $ag['id'] ?>&token=<?= $csrf ?>" 
                                       title="Toggle Active"
                                       style="display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 8px; background: <?= $ag['is_active'] ? 'rgba(16,185,129,0.2)' : 'rgba(239,68,68,0.2)' ?>; color: <?= $ag['is_active'] ? '#10b981' : '#ef4444' ?>; text-decoration: none;">
                                        <i class="fas fa-<?= $ag['is_active'] ? 'check' : 'times' ?>"></i>
                                    </a>
                                </td>

                                <!-- Actions -->
                                <td style="padding: 1rem 1.25rem; text-align: right; white-space: nowrap;">
                                    <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                        <a href="<?= SITE_URL ?>/admin/agents.php?action=edit&id=<?= $ag['id'] ?>" 
                                           class="admin-btn admin-btn-sm admin-btn-secondary" title="Edit Agent">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= SITE_URL ?>/admin/agents.php?action=delete&id=<?= $ag['id'] ?>&token=<?= $csrf ?>" 
                                           class="admin-btn admin-btn-sm admin-btn-danger confirm-delete"
                                           data-confirm="Delete agent <?= addslashes($ag['name']) ?>?"
                                           title="Delete Agent">
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

    <!-- Add / Edit Form Card -->
    <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
        <h3 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-user-plus" style="color: #3b82f6;"></i>
            <?= $editAgent ? 'Edit Agent' : 'Add New Agent' ?>
        </h3>

        <form method="POST" action="<?= SITE_URL ?>/admin/agents.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <?php if ($editAgent): ?>
                <input type="hidden" name="agent_id" value="<?= $editAgent['id'] ?>">
            <?php endif; ?>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Full Name *</label>
                <input type="text" name="name" value="<?= e($editAgent['name'] ?? '') ?>" required class="admin-form-input" style="width: 100%;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Job Title</label>
                <input type="text" name="title" value="<?= e($editAgent['title'] ?? 'Property Agent') ?>" class="admin-form-input" style="width: 100%;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Email Address *</label>
                <input type="email" name="email" value="<?= e($editAgent['email'] ?? '') ?>" required class="admin-form-input" style="width: 100%;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Phone Number</label>
                <input type="text" name="phone" value="<?= e($editAgent['phone'] ?? '') ?>" placeholder="+254 7..." class="admin-form-input" style="width: 100%;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">WhatsApp (e.g. 254700...)</label>
                <input type="text" name="whatsapp" value="<?= e($editAgent['whatsapp'] ?? '') ?>" placeholder="2547..." class="admin-form-input" style="width: 100%;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Specialization</label>
                <input type="text" name="specialization" value="<?= e($editAgent['specialization'] ?? '') ?>" placeholder="e.g. Residential, Land" class="admin-form-input" style="width: 100%;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.8rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Experience (Yrs)</label>
                    <input type="number" name="experience_years" min="0" value="<?= (int)($editAgent['experience_years'] ?? 2) ?>" class="admin-form-input" style="width: 100%;">
                </div>
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.8rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Deals Closed</label>
                    <input type="number" name="properties_sold" min="0" value="<?= (int)($editAgent['properties_sold'] ?? 10) ?>" class="admin-form-input" style="width: 100%;">
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Short Bio</label>
                <textarea name="bio" rows="3" class="admin-form-input" style="width: 100%;"><?= e($editAgent['bio'] ?? '') ?></textarea>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Agent Photo</label>
                <?php if ($editAgent && !empty($editAgent['photo'])): ?>
                    <img id="agentPhotoPreview" src="<?= e(agentPhotoUrl($editAgent['photo'])) ?>" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; display: block; margin-bottom: 0.5rem;">
                <?php else: ?>
                    <img id="agentPhotoPreview" src="" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; display: none; margin-bottom: 0.5rem;">
                <?php endif; ?>
                <input type="file" name="photo" accept="image/*" data-preview="#agentPhotoPreview" class="admin-form-input" style="width: 100%; font-size: 0.8rem;">
            </div>

            <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 1.5rem;">
                <input type="checkbox" name="is_active" value="1" <?= (!$editAgent || $editAgent['is_active']) ? 'checked' : '' ?> id="agentActive">
                <label for="agentActive" style="color: #cbd5e1; font-size: 0.85rem; cursor: pointer;">Active & Visible on website</label>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="admin-btn admin-btn-primary" style="flex: 1; justify-content: center;">
                    <i class="fas fa-save"></i> <?= $editAgent ? 'Update Agent' : 'Save Agent' ?>
                </button>
                <?php if ($editAgent): ?>
                    <a href="<?= SITE_URL ?>/admin/agents.php" class="admin-btn admin-btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
