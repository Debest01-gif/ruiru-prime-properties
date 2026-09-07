<?php
/**
 * Admin Dashboard
 */
$adminTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// Fetch Statistics
$totalProps = (int)$pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$activeProps = (int)$pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'available'")->fetchColumn();
$featuredProps = (int)$pdo->query("SELECT COUNT(*) FROM properties WHERE featured = 1")->fetchColumn();
$totalInquiries = (int)$pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$newInquiriesCount = (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
$totalAgents = (int)$pdo->query("SELECT COUNT(*) FROM agents WHERE is_active = 1")->fetchColumn();
$totalPosts = (int)$pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
$portfolioVal = (float)$pdo->query("SELECT COALESCE(SUM(price), 0) FROM properties WHERE status = 'available'")->fetchColumn();

// Recent Inquiries
$recentInquiries = $pdo->query("
    SELECT i.*, p.title as property_title 
    FROM inquiries i 
    LEFT JOIN properties p ON i.property_id = p.id 
    ORDER BY i.created_at DESC 
    LIMIT 6
")->fetchAll();

// Recent Properties
$recentProperties = $pdo->query("
    SELECT p.*, pt.name as category_name, a.name as agent_name 
    FROM properties p 
    LEFT JOIN property_types pt ON p.property_type_id = pt.id 
    LEFT JOIN agents a ON p.agent_id = a.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
")->fetchAll();

// Chart Data: Properties by Category
$catStats = $pdo->query("
    SELECT pt.name, COUNT(p.id) as count 
    FROM property_types pt 
    LEFT JOIN properties p ON p.property_type_id = pt.id 
    GROUP BY pt.id, pt.name
")->fetchAll();
$catLabels = array_column($catStats, 'name');
$catCounts = array_map('intval', array_column($catStats, 'count'));

// Chart Data: Inquiries by Status
$statusStats = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM inquiries 
    GROUP BY status
")->fetchAll();
$statusMap = ['new' => 0, 'contacted' => 0, 'closed' => 0];
foreach ($statusStats as $row) {
    $statusMap[$row['status']] = (int)$row['count'];
}
?>

<div class="dashboard-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: #f8fafc; margin-bottom: 0.35rem;">Overview & Analytics</h1>
        <p style="color: #94a3b8; font-size: 0.95rem;">Real-time performance metrics and Ruiru real estate portfolio status.</p>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <a href="<?= SITE_URL ?>/admin/property-add.php" class="admin-btn admin-btn-primary">
            <i class="fas fa-plus"></i> Add Property
        </a>
        <a href="<?= SITE_URL ?>/admin/inquiries.php" class="admin-btn admin-btn-secondary">
            <i class="fas fa-envelope-open-text"></i> View Inquiries
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="admin-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <div class="stat-card" style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
        <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(59,130,246,0.15); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fas fa-home"></i>
        </div>
        <div>
            <div style="color: #94a3b8; font-size: 0.85rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Properties</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #f8fafc; line-height: 1.2; margin: 0.25rem 0;"><?= $totalProps ?></div>
            <div style="color: #10b981; font-size: 0.8rem; font-weight: 600;"><?= $activeProps ?> Available · <?= $featuredProps ?> Featured</div>
        </div>
    </div>

    <div class="stat-card" style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
        <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(245,158,11,0.15); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fas fa-paper-plane"></i>
        </div>
        <div>
            <div style="color: #94a3b8; font-size: 0.85rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Inquiries</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #f8fafc; line-height: 1.2; margin: 0.25rem 0;"><?= $totalInquiries ?></div>
            <div style="color: <?= $newInquiriesCount > 0 ? '#ef4444' : '#10b981' ?>; font-size: 0.8rem; font-weight: 600;">
                <?= $newInquiriesCount ?> New unread
            </div>
        </div>
    </div>

    <div class="stat-card" style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
        <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(16,185,129,0.15); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fas fa-coins"></i>
        </div>
        <div>
            <div style="color: #94a3b8; font-size: 0.85rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Listing Value</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #f8fafc; line-height: 1.2; margin: 0.25rem 0;">KES <?= number_format($portfolioVal / 1000000, 1) ?>M</div>
            <div style="color: #64748b; font-size: 0.8rem;">Active listed assets</div>
        </div>
    </div>

    <div class="stat-card" style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
        <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(139,92,246,0.15); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <div style="color: #94a3b8; font-size: 0.85rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Team & Articles</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #f8fafc; line-height: 1.2; margin: 0.25rem 0;"><?= $totalAgents ?> <span style="font-size:1rem;font-weight:400;color:#64748b;">Agents</span></div>
            <div style="color: #a78bfa; font-size: 0.8rem; font-weight: 600;"><?= $totalPosts ?> Blog articles published</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-chart-pie" style="color: #3b82f6;"></i> Properties by Category
        </h3>
        <div style="height: 250px; position: relative;">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-chart-bar" style="color: #f59e0b;"></i> Inquiries Funnel Status
        </h3>
        <div style="height: 250px; position: relative;">
            <canvas id="inquiriesChart"></canvas>
        </div>
    </div>
</div>

<!-- Bottom Row: Recent Inquiries & Recent Properties -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(440px, 1fr)); gap: 1.5rem;">
    
    <!-- Recent Inquiries Panel -->
    <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-envelope-open" style="color: #38bdf8;"></i> Recent Inquiries
            </h3>
            <a href="<?= SITE_URL ?>/admin/inquiries.php" style="color: #38bdf8; font-size: 0.85rem; text-decoration: none; font-weight: 600;">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <?php if (empty($recentInquiries)): ?>
            <p style="color: #64748b; text-align: center; padding: 2rem 0;">No inquiries received yet.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                <?php foreach ($recentInquiries as $inq): ?>
                    <div style="background: rgba(30,41,59,0.5); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 1rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                        <div>
                            <div style="font-weight: 600; color: #f8fafc; font-size: 0.95rem;">
                                <?= e($inq['name']) ?>
                                <?php if ($inq['status'] === 'new'): ?>
                                    <span style="background: rgba(239,68,68,0.2); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); font-size: 0.7rem; font-weight: 700; padding: 2px 6px; border-radius: 6px; margin-left: 6px;">NEW</span>
                                <?php elseif ($inq['status'] === 'contacted'): ?>
                                    <span style="background: rgba(245,158,11,0.2); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); font-size: 0.7rem; font-weight: 700; padding: 2px 6px; border-radius: 6px; margin-left: 6px;">CONTACTED</span>
                                <?php else: ?>
                                    <span style="background: rgba(16,185,129,0.2); color: #10b981; border: 1px solid rgba(16,185,129,0.3); font-size: 0.7rem; font-weight: 700; padding: 2px 6px; border-radius: 6px; margin-left: 6px;">CLOSED</span>
                                <?php endif; ?>
                            </div>
                            <div style="color: #94a3b8; font-size: 0.8rem; margin-top: 2px;">
                                <?= e($inq['email']) ?> · <?= e($inq['phone'] ?: 'No phone') ?>
                            </div>
                            <?php if ($inq['property_title']): ?>
                                <div style="color: #38bdf8; font-size: 0.75rem; margin-top: 4px;">
                                    <i class="fas fa-tag"></i> <?= e($inq['property_title']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="text-align: right; flex-shrink: 0;">
                            <span style="color: #64748b; font-size: 0.75rem; display: block; margin-bottom: 6px;">
                                <?= date('M d, H:i', strtotime($inq['created_at'])) ?>
                            </span>
                            <a href="<?= SITE_URL ?>/admin/inquiries.php?id=<?= $inq['id'] ?>" class="admin-btn admin-btn-sm admin-btn-secondary">
                                Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Properties Panel -->
    <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-building" style="color: #10b981;"></i> Recently Added Listings
            </h3>
            <a href="<?= SITE_URL ?>/admin/properties.php" style="color: #10b981; font-size: 0.85rem; text-decoration: none; font-weight: 600;">
                All Properties <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <?php if (empty($recentProperties)): ?>
            <p style="color: #64748b; text-align: center; padding: 2rem 0;">No properties added yet.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                <?php foreach ($recentProperties as $prop): ?>
                    <div style="background: rgba(30,41,59,0.5); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 0.85rem; display: flex; align-items: center; gap: 1rem;">
                        <img src="<?= e(propertyImageUrl($prop['cover_image'])) ?>" 
                             alt="<?= e($prop['title']) ?>"
                             style="width: 64px; height: 64px; border-radius: 8px; object-fit: cover; flex-shrink: 0; background: #0f172a;">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; color: #f8fafc; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= e($prop['title']) ?>
                            </div>
                            <div style="color: #10b981; font-weight: 700; font-size: 0.85rem; margin-top: 2px;">
                                <?= formatPrice((float)$prop['price']) ?><?= $prop['price_type'] === 'rent' ? '/mo' : '' ?>
                            </div>
                            <div style="color: #64748b; font-size: 0.75rem; margin-top: 2px;">
                                <i class="fas fa-map-marker-alt"></i> <?= e($prop['location_area'] ?: $prop['address']) ?> · <?= e($prop['category_name']) ?>
                            </div>
                        </div>
                        <div style="flex-shrink: 0; display: flex; gap: 0.4rem;">
                            <a href="<?= SITE_URL ?>/admin/property-edit.php?id=<?= $prop['id'] ?>" class="admin-btn admin-btn-sm admin-btn-secondary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $prop['id'] ?>" target="_blank" class="admin-btn admin-btn-sm admin-btn-secondary" title="View Live">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php
$extraScript = "
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category Doughnut Chart
    const ctxCat = document.getElementById('categoryChart');
    if (ctxCat) {
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: " . json_encode($catLabels) . ",
                datasets: [{
                    data: " . json_encode($catCounts) . ",
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#94a3b8', font: { family: 'Inter', size: 12 } }
                    }
                },
                cutout: '68%'
            }
        });
    }

    // Inquiries Bar Chart
    const ctxInq = document.getElementById('inquiriesChart');
    if (ctxInq) {
        new Chart(ctxInq, {
            type: 'bar',
            data: {
                labels: ['New', 'Contacted', 'Closed'],
                datasets: [{
                    label: 'Inquiries',
                    data: [" . $statusMap['new'] . ", " . $statusMap['contacted'] . ", " . $statusMap['closed'] . "],
                    backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                    borderRadius: 8,
                    barThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { family: 'Inter' } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#94a3b8', font: { family: 'Inter' } },
                        grid: { color: 'rgba(255,255,255,0.05)' }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
";

require_once __DIR__ . '/includes/footer.php';
?>
