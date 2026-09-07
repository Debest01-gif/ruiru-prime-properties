<?php
/**
 * Properties Listing Page
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Properties';
$perPage = (int) setting('properties_per_page', '9');
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Filters
$q            = trim($_GET['q'] ?? '');
$type         = $_GET['type'] ?? '';          // sale | rent
$propertyType = $_GET['property_type'] ?? '';
$location     = $_GET['location'] ?? '';
$bedrooms     = (int)($_GET['bedrooms'] ?? 0);
$minPrice     = (int)($_GET['min_price'] ?? 0);
$maxPrice     = (int)($_GET['max_price'] ?? 0);
$sortBy       = $_GET['sort'] ?? 'newest';

// Build query
$conditions = ['p.is_active = 1'];
$params = [];

if ($q) {
    $conditions[] = '(p.title LIKE :q OR p.address LIKE :q OR p.location_area LIKE :q OR p.description LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}
if ($type === 'sale' || $type === 'rent') {
    $conditions[] = 'p.price_type = :type';
    $params[':type'] = $type;
}
if ($propertyType) {
    $conditions[] = 'pt.slug = :pt';
    $params[':pt'] = $propertyType;
}
if ($location) {
    $conditions[] = 'p.location_area = :loc';
    $params[':loc'] = $location;
}
if ($bedrooms > 0) {
    $conditions[] = 'p.bedrooms >= :beds';
    $params[':beds'] = $bedrooms;
}
if ($minPrice > 0) {
    $conditions[] = 'p.price >= :minp';
    $params[':minp'] = $minPrice;
}
if ($maxPrice > 0) {
    $conditions[] = 'p.price <= :maxp';
    $params[':maxp'] = $maxPrice;
}

$whereClause = 'WHERE ' . implode(' AND ', $conditions);

$orderClause = match($sortBy) {
    'price_asc'  => 'ORDER BY p.price ASC',
    'price_desc' => 'ORDER BY p.price DESC',
    'oldest'     => 'ORDER BY p.created_at ASC',
    default      => 'ORDER BY p.featured DESC, p.created_at DESC',
};

$baseQuery = "
    FROM properties p
    LEFT JOIN property_types pt ON p.property_type_id = pt.id
    LEFT JOIN agents a ON p.agent_id = a.id
    $whereClause
";

// Count
$countStmt = $pdo->prepare("SELECT COUNT(*) $baseQuery");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

// Fetch
$stmt = $pdo->prepare("SELECT p.*, pt.name as type_name, pt.slug as type_slug, a.name as agent_name, a.photo as agent_photo $baseQuery $orderClause LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$properties = $stmt->fetchAll();

// Property types for filter
$propTypes = $pdo->query("SELECT * FROM property_types ORDER BY id")->fetchAll();

require_once 'includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <h1>Our <span class="text-gold">Properties</span></h1>
        <p style="color:var(--text-secondary);max-width:500px;margin:10px auto 0;">
            Explore <?= $total ?> premium properties available in Ruiru and the surrounding areas
        </p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Properties</span>
        </div>
    </div>
</section>

<section class="section-padding">
<div class="container">

    <!-- Active Filters Banner -->
    <?php if ($q || $type || $propertyType || $location || $bedrooms || $minPrice || $maxPrice): ?>
    <div class="alert alert-info" style="margin-bottom:24px;">
        <i class="fas fa-filter"></i>
        Showing <?= $total ?> result<?= $total != 1 ? 's' : '' ?> for your search.
        <a href="properties.php" style="margin-left:10px;color:inherit;font-weight:700;text-decoration:underline;">Clear Filters</a>
    </div>
    <?php endif; ?>

    <div class="properties-layout">

        <!-- ===== FILTER SIDEBAR ===== -->
        <aside>
            <form action="properties.php" method="GET" class="filter-sidebar" id="filterForm">
                <div class="filter-title"><i class="fas fa-sliders-h"></i> Filter Properties</div>

                <!-- Keyword -->
                <div class="filter-section">
                    <h4>Search</h4>
                    <input type="text" name="q" class="form-control" placeholder="Keyword, area, type..."
                           value="<?= e($q) ?>">
                </div>

                <!-- Listing Type -->
                <div class="filter-section">
                    <h4>Listing Type</h4>
                    <label class="filter-checkbox">
                        <input type="radio" name="type" value="" <?= $type === '' ? 'checked' : '' ?>>
                        <span class="checkmark"></span> All Properties
                    </label>
                    <label class="filter-checkbox">
                        <input type="radio" name="type" value="sale" <?= $type === 'sale' ? 'checked' : '' ?>>
                        <span class="checkmark"></span> For Sale
                    </label>
                    <label class="filter-checkbox">
                        <input type="radio" name="type" value="rent" <?= $type === 'rent' ? 'checked' : '' ?>>
                        <span class="checkmark"></span> For Rent
                    </label>
                </div>

                <!-- Property Type -->
                <div class="filter-section">
                    <h4>Property Type</h4>
                    <?php foreach ($propTypes as $pt): ?>
                    <label class="filter-checkbox">
                        <input type="radio" name="property_type" value="<?= e($pt['slug']) ?>"
                               <?= $propertyType === $pt['slug'] ? 'checked' : '' ?>>
                        <span class="checkmark"></span> <?= e($pt['name']) ?>
                    </label>
                    <?php endforeach; ?>
                    <label class="filter-checkbox">
                        <input type="radio" name="property_type" value="" <?= $propertyType === '' ? 'checked' : '' ?>>
                        <span class="checkmark"></span> All Types
                    </label>
                </div>

                <!-- Location -->
                <div class="filter-section">
                    <h4>Location Area</h4>
                    <select name="location" class="form-control">
                        <option value="">All Locations</option>
                        <?php
                        $areas = ['Ruiru East','Kimbo','Membley','Gitambaya','Murera','Ruiru CBD','Ruiru West','Thika Road'];
                        foreach ($areas as $area):
                        ?>
                        <option value="<?= e($area) ?>" <?= $location === $area ? 'selected' : '' ?>><?= e($area) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Bedrooms -->
                <div class="filter-section">
                    <h4>Bedrooms</h4>
                    <select name="bedrooms" class="form-control">
                        <option value="0" <?= $bedrooms === 0 ? 'selected' : '' ?>>Any</option>
                        <option value="1" <?= $bedrooms === 1 ? 'selected' : '' ?>>1+</option>
                        <option value="2" <?= $bedrooms === 2 ? 'selected' : '' ?>>2+</option>
                        <option value="3" <?= $bedrooms === 3 ? 'selected' : '' ?>>3+</option>
                        <option value="4" <?= $bedrooms === 4 ? 'selected' : '' ?>>4+</option>
                        <option value="5" <?= $bedrooms === 5 ? 'selected' : '' ?>>5+</option>
                    </select>
                </div>

                <!-- Price Range -->
                <div class="filter-section">
                    <h4>Price Range (KES)</h4>
                    <div class="price-range">
                        <input type="number" name="min_price" class="form-control" placeholder="Min"
                               value="<?= $minPrice ?: '' ?>">
                        <span style="color:var(--text-muted);">—</span>
                        <input type="number" name="max_price" class="form-control" placeholder="Max"
                               value="<?= $maxPrice ?: '' ?>">
                    </div>
                </div>

                <!-- Sort -->
                <div class="filter-section">
                    <h4>Sort By</h4>
                    <select name="sort" class="form-control">
                        <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="oldest" <?= $sortBy === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;border-radius:12px;">
                    <i class="fas fa-search"></i> Apply Filters
                </button>
                <a href="properties.php" class="btn btn-glass" style="width:100%;border-radius:12px;margin-top:10px;text-align:center;justify-content:center;">
                    <i class="fas fa-times"></i> Reset
                </a>
            </form>
        </aside>

        <!-- ===== PROPERTIES GRID ===== -->
        <div>
            <!-- Results header -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
                <p style="color:var(--text-secondary);font-size:0.9rem;">
                    Showing <strong style="color:var(--text-primary);"><?= count($properties) ?></strong> of
                    <strong style="color:var(--primary);"><?= $total ?></strong> properties
                </p>
            </div>

            <?php if (empty($properties)): ?>
            <div class="empty-state">
                <i class="fas fa-home"></i>
                <h3>No Properties Found</h3>
                <p>Try adjusting your search filters or <a href="properties.php">view all properties</a>.</p>
            </div>
            <?php else: ?>
            <div class="properties-grid">
                <?php foreach ($properties as $i => $p): ?>
                <div class="property-card" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 80 ?>">
                    <div class="property-image">
                        <img src="<?= propertyImageUrl($p['cover_image']) ?>"
                             alt="<?= e($p['title']) ?>" loading="lazy">
                        <div class="property-image-overlay"></div>
                        <div class="property-badges">
                            <?php if ($p['featured']): ?><span class="badge badge-gold">Featured</span><?php endif; ?>
                            <span class="badge <?= $p['price_type'] === 'rent' ? 'badge-info' : 'badge-success' ?>">
                                <?= $p['price_type'] === 'rent' ? 'For Rent' : 'For Sale' ?>
                            </span>
                            <?= statusBadge($p['status']) ?>
                        </div>
                        <div class="property-actions">
                            <a href="property-detail.php?id=<?= $p['id'] ?>" class="property-action-btn"><i class="fas fa-eye"></i></a>
                            <a href="https://wa.me/<?= setting('whatsapp_number') ?>?text=<?= urlencode('Hi! I saw '.$p['title'].' on your website and would like more info.') ?>"
                               target="_blank" class="property-action-btn"><i class="fab fa-whatsapp"></i></a>
                        </div>
                        <div class="property-price">
                            <?= $p['price_type'] === 'rent' ? formatRentPrice($p['price']) : formatPrice($p['price']) ?>
                            <small><?= e($p['type_name']) ?></small>
                        </div>
                    </div>
                    <div class="property-body">
                        <h3 class="property-title">
                            <a href="property-detail.php?id=<?= $p['id'] ?>"><?= e($p['title']) ?></a>
                        </h3>
                        <p class="property-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= e($p['address'] ?: $p['location_area'] . ', ' . $p['city']) ?>
                        </p>
                        <div class="property-features">
                            <?php if ($p['bedrooms'] > 0): ?>
                            <div class="property-feature"><i class="fas fa-bed"></i> <span><?= $p['bedrooms'] ?> Beds</span></div>
                            <?php endif; ?>
                            <?php if ($p['bathrooms'] > 0): ?>
                            <div class="property-feature"><i class="fas fa-bath"></i> <span><?= $p['bathrooms'] ?> Baths</span></div>
                            <?php endif; ?>
                            <?php if ($p['size_sqm'] > 0): ?>
                            <div class="property-feature"><i class="fas fa-ruler-combined"></i> <span><?= number_format($p['size_sqm']) ?> sqm</span></div>
                            <?php endif; ?>
                            <?php if ($p['garage']): ?>
                            <div class="property-feature"><i class="fas fa-car"></i> <span>Garage</span></div>
                            <?php endif; ?>
                        </div>
                        <div class="property-footer">
                            <div class="agent-mini">
                                <img src="<?= agentPhotoUrl($p['agent_photo']) ?>" alt="<?= e($p['agent_name'] ?? 'Agent') ?>">
                                <span><?= e($p['agent_name'] ?? 'Our Agent') ?></span>
                            </div>
                            <a href="property-detail.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-glass">
                                Details <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php
            $paginationUrl = 'properties.php?' . http_build_query(array_filter([
                'q' => $q, 'type' => $type, 'property_type' => $propertyType,
                'location' => $location, 'bedrooms' => $bedrooms ?: '',
                'min_price' => $minPrice ?: '', 'max_price' => $maxPrice ?: '', 'sort' => $sortBy
            ]));
            echo paginate($total, $perPage, $page, $paginationUrl);
            ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</section>

<?php require_once 'includes/footer.php'; ?>
