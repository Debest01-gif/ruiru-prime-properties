<?php
/**
 * Admin: Inquiries Management
 */
$adminTitle = 'Client Inquiries';
require_once __DIR__ . '/includes/header.php';

// Handle status updates and deletion
$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';

if ($action && $id > 0) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        setFlash('danger', 'Security token expired. Please try again.');
        redirect(SITE_URL . '/admin/inquiries.php');
    }

    if ($action === 'status') {
        $newStatus = $_GET['new_status'] ?? 'read';
        if (in_array($newStatus, ['new', 'read', 'replied', 'closed'])) {
            $stmt = $pdo->prepare("UPDATE inquiries SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $newStatus, ':id' => $id]);
            setFlash('success', 'Inquiry status updated to ' . ucfirst($newStatus) . '.');
        }
        redirect(SITE_URL . '/admin/inquiries.php' . (isset($_GET['filter']) ? '?filter=' . urlencode($_GET['filter']) : ''));
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM inquiries WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Inquiry deleted successfully.');
        redirect(SITE_URL . '/admin/inquiries.php');
    }
}

// Filter
$statusFilter = $_GET['status'] ?? '';
$where = '1=1';
$params = [];
if ($statusFilter && in_array($statusFilter, ['new', 'read', 'replied', 'closed'])) {
    $where = 'i.status = :st';
    $params[':st'] = $statusFilter;
}

$stmt = $pdo->prepare("
    SELECT i.*, p.title as property_title, p.price as property_price, p.cover_image as property_image,
           a.name as agent_name 
    FROM inquiries i 
    LEFT JOIN properties p ON i.property_id = p.id 
    LEFT JOIN agents a ON i.agent_id = a.id 
    WHERE $where 
    ORDER BY i.id DESC
");
$stmt->execute($params);
$inquiries = $stmt->fetchAll();

// Count per status
$statusCounts = [
    'all'     => (int)$pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn(),
    'new'     => (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status='new'")->fetchColumn(),
    'read'    => (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status='read'")->fetchColumn(),
    'replied' => (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status='replied'")->fetchColumn(),
    'closed'  => (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status='closed'")->fetchColumn(),
];

// Single inquiry detail modal/view if requested via ?view=ID
$viewId = (int)($_GET['view'] ?? 0);
$selectedInquiry = null;
if ($viewId > 0) {
    foreach ($inquiries as $inq) {
        if ((int)$inq['id'] === $viewId) {
            $selectedInquiry = $inq;
            // Automatically mark as read if it was new
            if ($inq['status'] === 'new') {
                $up = $pdo->prepare("UPDATE inquiries SET status = 'read' WHERE id = :id");
                $up->execute([':id' => $viewId]);
                $selectedInquiry['status'] = 'read';
            }
            break;
        }
    }
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

<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: #f8fafc; margin-bottom: 0.35rem;">Client Inquiries & Leads</h1>
        <p style="color: #94a3b8; font-size: 0.95rem;">Track, reply via WhatsApp/Email, and manage incoming buyer and tenant requests.</p>
    </div>
</div>

<!-- Status Tabs -->
<div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
    <a href="<?= SITE_URL ?>/admin/inquiries.php" 
       style="padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; background: <?= empty($statusFilter) ? '#2563eb' : 'rgba(15,23,42,0.65)' ?>; color: #fff; border: 1px solid <?= empty($statusFilter) ? '#3b82f6' : 'rgba(255,255,255,0.08)' ?>;">
        All Leads <span style="background: rgba(255,255,255,0.2); border-radius: 20px; padding: 2px 7px; font-size: 0.75rem;"><?= $statusCounts['all'] ?></span>
    </a>
    <a href="<?= SITE_URL ?>/admin/inquiries.php?status=new" 
       style="padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; background: <?= $statusFilter === 'new' ? '#dc2626' : 'rgba(15,23,42,0.65)' ?>; color: #fff; border: 1px solid <?= $statusFilter === 'new' ? '#ef4444' : 'rgba(255,255,255,0.08)' ?>;">
        <i class="fas fa-bell"></i> New <span style="background: rgba(255,255,255,0.2); border-radius: 20px; padding: 2px 7px; font-size: 0.75rem;"><?= $statusCounts['new'] ?></span>
    </a>
    <a href="<?= SITE_URL ?>/admin/inquiries.php?status=read" 
       style="padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; background: <?= $statusFilter === 'read' ? '#d97706' : 'rgba(15,23,42,0.65)' ?>; color: #fff; border: 1px solid <?= $statusFilter === 'read' ? '#f59e0b' : 'rgba(255,255,255,0.08)' ?>;">
        Read <span style="background: rgba(255,255,255,0.2); border-radius: 20px; padding: 2px 7px; font-size: 0.75rem;"><?= $statusCounts['read'] ?></span>
    </a>
    <a href="<?= SITE_URL ?>/admin/inquiries.php?status=replied" 
       style="padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; background: <?= $statusFilter === 'replied' ? '#059669' : 'rgba(15,23,42,0.65)' ?>; color: #fff; border: 1px solid <?= $statusFilter === 'replied' ? '#10b981' : 'rgba(255,255,255,0.08)' ?>;">
        Replied <span style="background: rgba(255,255,255,0.2); border-radius: 20px; padding: 2px 7px; font-size: 0.75rem;"><?= $statusCounts['replied'] ?></span>
    </a>
    <a href="<?= SITE_URL ?>/admin/inquiries.php?status=closed" 
       style="padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; background: <?= $statusFilter === 'closed' ? '#475569' : 'rgba(15,23,42,0.65)' ?>; color: #fff; border: 1px solid <?= $statusFilter === 'closed' ? '#64748b' : 'rgba(255,255,255,0.08)' ?>;">
        Closed <span style="background: rgba(255,255,255,0.2); border-radius: 20px; padding: 2px 7px; font-size: 0.75rem;"><?= $statusCounts['closed'] ?></span>
    </a>
</div>

<!-- Modal / Detailed View if selected -->
<?php if ($selectedInquiry): ?>
    <div style="background: rgba(15,23,42,0.9); border: 1px solid rgba(59,130,246,0.3); border-radius: 16px; padding: 1.75rem; margin-bottom: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.05em;">Inquiry Details #<?= $selectedInquiry['id'] ?></span>
                <h2 style="font-size: 1.35rem; font-weight: 700; color: #f8fafc; margin-top: 0.25rem;"><?= e($selectedInquiry['name']) ?></h2>
                <div style="color: #94a3b8; font-size: 0.85rem; margin-top: 0.25rem;">
                    <i class="far fa-clock"></i> Received <?= date('F d, Y \a\t H:i', strtotime($selectedInquiry['created_at'])) ?> (<?= timeAgo($selectedInquiry['created_at']) ?>)
                </div>
            </div>
            <a href="<?= SITE_URL ?>/admin/inquiries.php<?= $statusFilter ? '?status=' . urlencode($statusFilter) : '' ?>" class="admin-btn admin-btn-sm admin-btn-secondary">
                <i class="fas fa-times"></i> Close Details
            </a>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div style="background: rgba(30,41,59,0.5); padding: 1.25rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 0.5rem; font-weight: 600;">Message Content</div>
                <p style="color: #e2e8f0; font-size: 0.95rem; line-height: 1.6; white-space: pre-line; margin: 0;">
                    <?= e($selectedInquiry['message']) ?>
                </p>
                <?php if ($selectedInquiry['property_title']): ?>
                    <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; gap: 1rem;">
                        <img src="<?= e(propertyImageUrl($selectedInquiry['property_image'])) ?>" 
                             style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
                        <div>
                            <div style="font-size: 0.75rem; color: #94a3b8;">Inquired Property:</div>
                            <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $selectedInquiry['property_id'] ?>" target="_blank" style="color: #38bdf8; font-weight: 600; text-decoration: none; font-size: 0.9rem;">
                                <?= e($selectedInquiry['property_title']) ?> <i class="fas fa-external-link-alt" style="font-size: 0.75rem;"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div style="background: rgba(30,41,59,0.5); padding: 1.25rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase;">Email Address</div>
                    <a href="mailto:<?= e($selectedInquiry['email']) ?>" style="color: #f8fafc; font-weight: 600; text-decoration: none; font-size: 0.9rem;">
                        <?= e($selectedInquiry['email']) ?>
                    </a>
                </div>

                <div>
                    <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase;">Phone Number</div>
                    <div style="color: #f8fafc; font-weight: 600; font-size: 0.9rem;">
                        <?= e($selectedInquiry['phone'] ?: 'Not provided') ?>
                    </div>
                </div>

                <!-- Quick Action WhatsApp Button -->
                <?php if (!empty($selectedInquiry['phone'])): 
                    $cleanPhone = preg_replace('/[^0-9]/', '', $selectedInquiry['phone']);
                    if (str_starts_with($cleanPhone, '0')) $cleanPhone = '254' . substr($cleanPhone, 1);
                    $waText = urlencode("Hello " . $selectedInquiry['name'] . ", thank you for reaching out to Ruiru Prime Properties regarding " . ($selectedInquiry['property_title'] ?? 'our properties') . ". How can we assist you today?");
                ?>
                    <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= $waText ?>" target="_blank" class="admin-btn" style="background: #22c55e; color: #fff; justify-content: center; font-weight: 600;">
                        <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                    </a>
                <?php endif; ?>

                <!-- Status Select Dropdown -->
                <div>
                    <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; margin-bottom: 0.4rem;">Change Status</div>
                    <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                        <a href="<?= SITE_URL ?>/admin/inquiries.php?action=status&id=<?= $selectedInquiry['id'] ?>&new_status=replied&token=<?= $csrf ?>" class="admin-btn admin-btn-sm" style="background: rgba(16,185,129,0.2); color: #10b981; border: 1px solid rgba(16,185,129,0.3);">
                            Mark Replied
                        </a>
                        <a href="<?= SITE_URL ?>/admin/inquiries.php?action=status&id=<?= $selectedInquiry['id'] ?>&new_status=closed&token=<?= $csrf ?>" class="admin-btn admin-btn-sm" style="background: rgba(100,116,139,0.2); color: #94a3b8; border: 1px solid rgba(100,116,139,0.3);">
                            Mark Closed
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Inquiries Table Card -->
<div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden;">
    <div style="padding: 1rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center;">
        <span style="color: #94a3b8; font-size: 0.9rem; font-weight: 500;">
            Showing <strong><?= count($inquiries) ?></strong> leads
        </span>
        <input type="text" id="adminTableFilter" placeholder="Filter inquiries..." 
               class="admin-form-input" style="padding: 0.4rem 0.85rem; font-size: 0.85rem; width: 220px;">
    </div>

    <div style="overflow-x: auto;">
        <table class="admin-table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(30,41,59,0.4); color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">
                    <th style="padding: 1rem 1.25rem;">Sender</th>
                    <th style="padding: 1rem 1.25rem;">Contact</th>
                    <th style="padding: 1rem 1.25rem;">Subject / Property</th>
                    <th style="padding: 1rem 1.25rem;">Type</th>
                    <th style="padding: 1rem 1.25rem; text-align: center;">Status</th>
                    <th style="padding: 1rem 1.25rem;">Date</th>
                    <th style="padding: 1rem 1.25rem; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($inquiries)): ?>
                    <tr>
                        <td colspan="7" style="padding: 3rem; text-align: center; color: #64748b;">
                            <i class="fas fa-inbox" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.4; display: block;"></i>
                            No inquiries found in this category.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($inquiries as $inq): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.04); <?= $inq['status'] === 'new' ? 'background: rgba(59,130,246,0.05);' : '' ?>">
                            <!-- Sender -->
                            <td style="padding: 1rem 1.25rem;">
                                <div style="font-weight: 600; color: #f8fafc; font-size: 0.95rem;">
                                    <?= e($inq['name']) ?>
                                    <?php if ($inq['status'] === 'new'): ?>
                                        <span style="background: #ef4444; color: #fff; font-size: 0.65rem; font-weight: 700; padding: 2px 5px; border-radius: 4px; margin-left: 4px;">NEW</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Contact -->
                            <td style="padding: 1rem 1.25rem; font-size: 0.85rem;">
                                <div style="color: #cbd5e1;"><?= e($inq['email']) ?></div>
                                <div style="color: #64748b; margin-top: 2px;"><?= e($inq['phone'] ?: '—') ?></div>
                            </td>

                            <!-- Subject / Property -->
                            <td style="padding: 1rem 1.25rem; font-size: 0.85rem; max-width: 250px;">
                                <?php if ($inq['property_title']): ?>
                                    <span style="color: #38bdf8; font-weight: 600; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <i class="fas fa-tag"></i> <?= e($inq['property_title']) ?>
                                    </span>
                                <?php endif; ?>
                                <span style="color: #94a3b8; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e($inq['subject'] ?: truncate($inq['message'], 60)) ?>
                                </span>
                            </td>

                            <!-- Type -->
                            <td style="padding: 1rem 1.25rem; font-size: 0.8rem; color: #94a3b8;">
                                <span style="background: rgba(255,255,255,0.05); padding: 3px 7px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.08); text-transform: capitalize;">
                                    <?= e($inq['inquiry_type'] ?: 'General') ?>
                                </span>
                            </td>

                            <!-- Status -->
                            <td style="padding: 1rem 1.25rem; text-align: center;">
                                <?php if ($inq['status'] === 'new'): ?>
                                    <span class="badge badge-danger">New</span>
                                <?php elseif ($inq['status'] === 'read'): ?>
                                    <span class="badge badge-warning">Read</span>
                                <?php elseif ($inq['status'] === 'replied'): ?>
                                    <span class="badge badge-success">Replied</span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(100,116,139,0.3); color: #94a3b8;">Closed</span>
                                <?php endif; ?>
                            </td>

                            <!-- Date -->
                            <td style="padding: 1rem 1.25rem; font-size: 0.8rem; color: #64748b; white-space: nowrap;">
                                <?= date('M d, H:i', strtotime($inq['created_at'])) ?>
                            </td>

                            <!-- Actions -->
                            <td style="padding: 1rem 1.25rem; text-align: right; white-space: nowrap;">
                                <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                    <a href="<?= SITE_URL ?>/admin/inquiries.php?view=<?= $inq['id'] ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?>" 
                                       class="admin-btn admin-btn-sm admin-btn-secondary" title="View Details">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="<?= SITE_URL ?>/admin/inquiries.php?action=delete&id=<?= $inq['id'] ?>&token=<?= $csrf ?>" 
                                       class="admin-btn admin-btn-sm admin-btn-danger confirm-delete"
                                       data-confirm="Delete inquiry from <?= addslashes($inq['name']) ?>?"
                                       title="Delete Inquiry">
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
