<?php
/**
 * Admin: Site Settings & Account
 */
$adminTitle = 'Site Settings';
require_once __DIR__ . '/includes/header.php';

$errors = [];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Invalid security token.';
    }

    $formType = $_POST['form_type'] ?? 'settings';

    if ($formType === 'settings' && empty($errors)) {
        $settingsToUpdate = [
            'company_name'        => trim($_POST['company_name'] ?? ''),
            'company_tagline'     => trim($_POST['company_tagline'] ?? ''),
            'company_email'       => trim($_POST['company_email'] ?? ''),
            'company_phone'       => trim($_POST['company_phone'] ?? ''),
            'company_phone2'      => trim($_POST['company_phone2'] ?? ''),
            'whatsapp_number'     => trim($_POST['whatsapp_number'] ?? ''),
            'company_address'     => trim($_POST['company_address'] ?? ''),
            'company_description' => trim($_POST['company_description'] ?? ''),
            'facebook_url'        => trim($_POST['facebook_url'] ?? ''),
            'twitter_url'         => trim($_POST['twitter_url'] ?? ''),
            'instagram_url'       => trim($_POST['instagram_url'] ?? ''),
            'hero_title'          => trim($_POST['hero_title'] ?? ''),
            'hero_subtitle'       => trim($_POST['hero_subtitle'] ?? ''),
            'currency'            => trim($_POST['currency'] ?? 'KES'),
            'properties_per_page' => (int)($_POST['properties_per_page'] ?? 9),
            'google_maps_url'     => trim($_POST['google_maps_url'] ?? ''),
        ];

        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES (:key, :val) 
            ON DUPLICATE KEY UPDATE setting_value = :val2
        ");

        foreach ($settingsToUpdate as $key => $val) {
            $stmt->execute([
                ':key'  => $key,
                ':val'  => $val,
                ':val2' => $val
            ]);
        }

        setFlash('success', 'Site settings updated successfully!');
        redirect(SITE_URL . '/admin/settings.php');
    }

    if ($formType === 'password' && empty($errors)) {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass     = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (empty($currentPass) || empty($newPass)) {
            $errors[] = 'Current and new password are required.';
        } elseif ($newPass !== $confirmPass) {
            $errors[] = 'New passwords do not match.';
        } elseif (strlen($newPass) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } else {
            // Verify current password
            $uStmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
            $uStmt->execute([':id' => $_SESSION['admin_id']]);
            $hash = $uStmt->fetchColumn();

            if (!password_verify($currentPass, $hash)) {
                $errors[] = 'Incorrect current password.';
            } else {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                $up = $pdo->prepare("UPDATE users SET password = :p WHERE id = :id");
                $up->execute([':p' => $newHash, ':id' => $_SESSION['admin_id']]);
                setFlash('success', 'Admin password changed successfully.');
                redirect(SITE_URL . '/admin/settings.php');
            }
        }
    }
}

// Fetch all settings
$allSettings = [];
$rows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
foreach ($rows as $r) {
    $allSettings[$r['setting_key']] = $r['setting_value'];
}

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
        <h1 style="font-size: 1.75rem; font-weight: 800; color: #f8fafc; margin-bottom: 0.35rem;">Platform Configuration</h1>
        <p style="color: #94a3b8; font-size: 0.95rem;">Configure contact numbers, branding, social media links, and security credentials.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.75rem; align-items: start;">

    <!-- Main Settings Form -->
    <form method="POST" action="<?= SITE_URL ?>/admin/settings.php">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="form_type" value="settings">

        <!-- Brand & Contact Details -->
        <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-building" style="color: #3b82f6;"></i> Company Branding & Contacts
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Company Name</label>
                    <input type="text" name="company_name" value="<?= e($allSettings['company_name'] ?? '') ?>" class="admin-form-input" style="width: 100%;">
                </div>
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Tagline / Slogan</label>
                    <input type="text" name="company_tagline" value="<?= e($allSettings['company_tagline'] ?? '') ?>" class="admin-form-input" style="width: 100%;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Primary Phone</label>
                    <input type="text" name="company_phone" value="<?= e($allSettings['company_phone'] ?? '') ?>" class="admin-form-input" style="width: 100%;">
                </div>
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Secondary Phone</label>
                    <input type="text" name="company_phone2" value="<?= e($allSettings['company_phone2'] ?? '') ?>" class="admin-form-input" style="width: 100%;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Official Email</label>
                    <input type="email" name="company_email" value="<?= e($allSettings['company_email'] ?? '') ?>" class="admin-form-input" style="width: 100%;">
                </div>
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">WhatsApp Number (254...)</label>
                    <input type="text" name="whatsapp_number" value="<?= e($allSettings['whatsapp_number'] ?? '') ?>" class="admin-form-input" style="width: 100%;">
                </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Office Address (Ruiru)</label>
                <input type="text" name="company_address" value="<?= e($allSettings['company_address'] ?? '') ?>" class="admin-form-input" style="width: 100%;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Company Summary / Footer Bio</label>
                <textarea name="company_description" rows="3" class="admin-form-input" style="width: 100%;"><?= e($allSettings['company_description'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Hero & Display Settings -->
        <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-desktop" style="color: #10b981;"></i> Homepage Hero & Listing Controls
            </h3>

            <div style="margin-bottom: 1.25rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Hero Headline Title</label>
                <input type="text" name="hero_title" value="<?= e($allSettings['hero_title'] ?? '') ?>" class="admin-form-input" style="width: 100%;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Hero Subtitle</label>
                <input type="text" name="hero_subtitle" value="<?= e($allSettings['hero_subtitle'] ?? '') ?>" class="admin-form-input" style="width: 100%;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Currency Symbol</label>
                    <input type="text" name="currency" value="<?= e($allSettings['currency'] ?? 'KES') ?>" class="admin-form-input" style="width: 100%;">
                </div>
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Properties Per Page</label>
                    <input type="number" name="properties_per_page" value="<?= (int)($allSettings['properties_per_page'] ?? 9) ?>" min="3" max="50" class="admin-form-input" style="width: 100%;">
                </div>
            </div>
        </div>

        <!-- Social Media & Maps -->
        <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-share-alt" style="color: #8b5cf6;"></i> Social Channels & Maps
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Facebook URL</label>
                    <input type="url" name="facebook_url" value="<?= e($allSettings['facebook_url'] ?? '') ?>" class="admin-form-input" style="width: 100%;">
                </div>
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Twitter / X URL</label>
                    <input type="url" name="twitter_url" value="<?= e($allSettings['twitter_url'] ?? '') ?>" class="admin-form-input" style="width: 100%;">
                </div>
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Instagram URL</label>
                    <input type="url" name="instagram_url" value="<?= e($allSettings['instagram_url'] ?? '') ?>" class="admin-form-input" style="width: 100%;">
                </div>
            </div>

            <div>
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Google Maps Embed URL</label>
                <input type="text" name="google_maps_url" value="<?= e($allSettings['google_maps_url'] ?? '') ?>" class="admin-form-input" style="width: 100%;">
            </div>
        </div>

        <button type="submit" class="admin-btn admin-btn-primary" style="padding: 0.85rem 2rem; font-size: 1rem;">
            <i class="fas fa-save"></i> Save Site Configuration
        </button>
    </form>

    <!-- Admin Password Security Card -->
    <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
        <h3 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-lock" style="color: #ef4444;"></i> Change Password
        </h3>

        <form method="POST" action="<?= SITE_URL ?>/admin/settings.php">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="form_type" value="password">

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Current Password</label>
                <input type="password" name="current_password" required class="admin-form-input" style="width: 100%;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">New Password</label>
                <input type="password" name="new_password" required minlength="6" class="admin-form-input" style="width: 100%;">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">Confirm New Password</label>
                <input type="password" name="confirm_password" required minlength="6" class="admin-form-input" style="width: 100%;">
            </div>

            <button type="submit" class="admin-btn admin-btn-secondary" style="width: 100%; justify-content: center;">
                <i class="fas fa-key"></i> Update Password
            </button>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
