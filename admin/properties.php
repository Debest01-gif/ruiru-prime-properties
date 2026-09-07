<?php
/**
 * Admin: Properties Management
 */
$adminTitle = 'Properties';
require_once __DIR__ . '/includes/header.php';

// Handle Actions (Delete, Toggle Featured, Toggle Active)
$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';

if ($action && $id > 0) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        setFlash('danger', 'Invalid security token. Please try again.');
        redirect(SITE_URL . '/admin/properties.php');
    }

    if ($action === 'delete') {
        // Fetch property to delete image if needed
        $stmt = $pdo->prepare("SELECT cover_image FROM properties WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $prop = $stmt->fetch();
        if ($prop) {
            // Delete record
            $del = $pdo->prepare("DELETE FROM properties WHERE id = :id");
            $del->execute([':id' => $id]);
            setFlash('success', 'Property successfully deleted.');
        }
        redirect(SITE_URL . '/admin/properties.php');
    } elseif ($action === 'toggle_featured') {
        $stmt = $pdo->prepare("UPDATE properties SET featured = IF(featured=1, 0, 1) WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Featured status updated.');
        redirect(SITE_URL . '/admin/properties.php');
    } elseif ($action === 'toggle_active') {
        $stmt = $pdo->prepare("UPDATE properties SET is_active = IF(is_active=1, 0, 1) WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Property visibility status updated.');
        redirect(SITE_URL . '/admin/properties.php');
    }
}

// Filters
$q = trim($_GET['q'] ?? '');
$typeId = (int)($_GET['type_id'] ?? 0);
$status = $_GET['status'] ?? '';
$priceType = $_GET['price_type'] ?? '';

$conditions = ['1=1'];
$params = [];

if ($q !== '') {
    $conditions[] = '(p.title LIKE :q OR p.address LIKE :q OR p.location_area LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}
if ($typeId > 0) {
    $conditions[] = 'p.property_type_id = :type_id';
    $params[':type_id'] = $typeId;
}
if ($status !== '') {
    $conditions[] = 'p.status = :status';
    $params[':status'] = $status;
}
if ($priceType !== '') {
    $conditions[] = 'p.price_type = :price_type';
    $params[':price_type'] = $priceType;
}

$whereClause = implode(' AND ', $conditions);

// Fetch properties
$query = "
    SELECT p.*, pt.name as type_name, a.name as agent_name 
    FROM properties p 
    LEFT JOIN property_types pt ON p.property_type_id = pt.id 
    LEFT JOIN agents a ON p.agent_id = a.id 
    WHERE $whereClause 
    ORDER BY p.id DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$properties = $stmt->fetchAll();

// Property types for filter dropdown
$types = $pdo->query("SELECT * FROM property_types ORDER BY name ASC")->fetchAll();
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
        <h1 style="font-size: 1.75rem; font-weight: 800; color: #f8fafc; margin-bottom: 0.35rem;">Properties Management</h1>
        <p style="color: #94a3b8; font-size: 0.95rem;">Manage, edit, feature, and publish all real estate listings.</p>
    </div>
    <a href="<?= SITE_URL ?>/admin/property-add.php" class="admin-btn admin-btn-primary">
        <i class="fas fa-plus"></i> Add New Property
    </a>
</div>

<!-- Filters Bar -->
<div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.25rem; margin-bottom: 1.75rem;">
    <form method="GET" action="<?= SITE_URL ?>/admin/properties.php" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
        <div style="flex: 2; min-width: 200px;">
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search by title or location..."
                   class="admin-form-input" style="width: 100%;">
        </div>
        <div style="flex: 1; min-width: 140px;">
            <select name="type_id" class="admin-form-select" style="width: 100%;">
                <option value="">All Types</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $typeId === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex: 1; min-width: 130px;">
            <select name="status" class="admin-form-select" style="width: 100%;">
                <option value="">All Statuses</option>
                <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="sold" <?= $status === 'sold' ? 'selected' : '' ?>>Sold</option>
                <option value="rented" <?= $status === 'rented' ? 'selected' : '' ?>>Rented</option>
                <option value="reserved" <?= $status === 'reserved' ? 'selected' : '' ?>>Reserved</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 120px;">
            <select name="price_type" class="admin-form-select" style="width: 100%;">
                <option value="">Sale / Rent</option>
                <option value="sale" <?= $priceType === 'sale' ? 'selected' : '' ?>>For Sale</option>
                <option value="rent" <?= $priceType === 'rent' ? 'selected' : '' ?>>For Rent</option>
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="admin-btn admin-btn-secondary">
                <i class="fas fa-filter"></i> Filter
            </button>
            <?php if ($q || $typeId || $status || $priceType): ?>
                <a href="<?= SITE_URL ?>/admin/properties.php" class="admin-btn admin-btn-secondary" title="Reset Filters">
                    <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Table Card -->
<div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden;">
    <div style="padding: 1rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center;">
        <span style="color: #94a3b8; font-size: 0.9rem; font-weight: 500;">
            Showing <strong><?= count($properties) ?></strong> listings
        </span>
        <input type="text" id="adminTableFilter" placeholder="Quick table search..." 
               class="admin-form-input" style="padding: 0.4rem 0.85rem; font-size: 0.85rem; width: 220px;">
    </div>

    <div style="overflow-x: auto;">
        <table class="admin-table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(30,41,59,0.4); color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">
                    <th style="padding: 1rem 1.25rem;">Property</th>
                    <th style="padding: 1rem 1.25rem;">Type</th>
                    <th style="padding: 1rem 1.25rem;">Price</th>
                    <th style="padding: 1rem 1.25rem;">Agent</th>
                    <th style="padding: 1rem 1.25rem; text-align: center;">Status</th>
                    <th style="padding: 1rem 1.25rem; text-align: center;">Featured</th>
                    <th style="padding: 1rem 1.25rem; text-align: center;">Visibility</th>
                    <th style="padding: 1rem 1.25rem; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($properties)): ?>
                    <tr>
                        <td colspan="8" style="padding: 3rem; text-align: center; color: #64748b;">
                            <i class="fas fa-home" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.4; display: block;"></i>
                            No properties found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($properties as $prop): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.04); transition: background 0.15s ease;">
                            <!-- Property Thumbnail & Title -->
                            <td style="padding: 1rem 1.25rem;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <img src="<?= e(propertyImageUrl($prop['cover_image'])) ?>" 
                                         alt="<?= e($prop['title']) ?>"
                                         style="width: 56px; height: 56px; border-radius: 10px; object-fit: cover; background: #0f172a; flex-shrink: 0;">
                                    <div>
                                        <a href="<?= SITE_URL ?>/admin/property-edit.php?id=<?= $prop['id'] ?>" 
                                           style="color: #f8fafc; font-weight: 600; text-decoration: none; font-size: 0.95rem; display: block; max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= e($prop['title']) ?>
                                        </a>
                                        <span style="color: #64748b; font-size: 0.8rem; display: block; margin-top: 2px;">
                                            <i class="fas fa-map-marker-alt"></i> <?= e($prop['location_area'] ?: $prop['address']) ?>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Type -->
                            <td style="padding: 1rem 1.25rem; color: #cbd5e1; font-size: 0.9rem;">
                                <span style="background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; border: 1px solid rgba(255,255,255,0.08);">
                                    <?= e($prop['type_name'] ?? 'General') ?>
                                </span>
                            </td>

                            <!-- Price -->
                            <td style="padding: 1rem 1.25rem; font-weight: 700; color: #10b981; font-size: 0.95rem; white-space: nowrap;">
                                <?= formatPrice((float)$prop['price']) ?>
                                <span style="display: block; font-size: 0.75rem; color: #94a3b8; font-weight: 400; text-transform: uppercase;">
                                    For <?= e($prop['price_type']) ?>
                                </span>
                            </td>

                            <!-- Agent -->
                            <td style="padding: 1rem 1.25rem; color: #94a3b8; font-size: 0.85rem;">
                                <?= e($prop['agent_name'] ?? 'Unassigned') ?>
                            </td>

                            <!-- Status Badge -->
                            <td style="padding: 1rem 1.25rem; text-align: center;">
                                <?= statusBadge($prop['status']) ?>
                            </td>

                            <!-- Featured Toggle -->
                            <td style="padding: 1rem 1.25rem; text-align: center;">
                                <a href="<?= SITE_URL ?>/admin/properties.php?action=toggle_featured&id=<?= $prop['id'] ?>&token=<?= $csrf ?>" 
                                   title="<?= $prop['featured'] ? 'Remove from Featured' : 'Mark as Featured' ?>"
                                   style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: <?= $prop['featured'] ? 'rgba(245,158,11,0.2)' : 'rgba(255,255,255,0.05)' ?>; color: <?= $prop['featured'] ? '#f59e0b' : '#64748b' ?>; text-decoration: none; border: 1px solid <?= $prop['featured'] ? 'rgba(245,158,11,0.4)' : 'rgba(255,255,255,0.08)' ?>;">
                                    <i class="fas fa-star"></i>
                                </a>
                            </td>

                            <!-- Visibility Toggle -->
                            <td style="padding: 1rem 1.25rem; text-align: center;">
                                <a href="<?= SITE_URL ?>/admin/properties.php?action=toggle_active&id=<?= $prop['id'] ?>&token=<?= $csrf ?>" 
                                   title="<?= $prop['is_active'] ? 'Hide Listing' : 'Make Active' ?>"
                                   style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: <?= $prop['is_active'] ? 'rgba(16,185,129,0.2)' : 'rgba(239,68,68,0.2)' ?>; color: <?= $prop['is_active'] ? '#10b981' : '#ef4444' ?>; text-decoration: none; border: 1px solid <?= $prop['is_active'] ? 'rgba(16,185,129,0.4)' : 'rgba(239,68,68,0.4)' ?>;">
                                    <i class="fas fa-<?= $prop['is_active'] ? 'eye' : 'eye-slash' ?>"></i>
                                </a>
                            </td>

                            <!-- Action Buttons -->
                            <td style="padding: 1rem 1.25rem; text-align: right; white-space: nowrap;">
                                <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                    <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $prop['id'] ?>" target="_blank" 
                                       class="admin-btn admin-btn-sm admin-btn-secondary" title="Preview Public Page">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    <a href="<?= SITE_URL ?>/admin/property-edit.php?id=<?= $prop['id'] ?>" 
                                       class="admin-btn admin-btn-sm admin-btn-secondary" title="Edit Property">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= SITE_URL ?>/admin/properties.php?action=delete&id=<?= $prop['id'] ?>&token=<?= $csrf ?>" 
                                       class="admin-btn admin-btn-sm admin-btn-danger confirm-delete"
                                       data-confirm="Are you sure you want to permanently delete '<?= addslashes($prop['title']) ?>'? This cannot be undone."
                                       title="Delete Property">
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
