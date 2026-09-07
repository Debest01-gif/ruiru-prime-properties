<?php
/**
 * Admin: Edit Property
 */
$adminTitle = 'Edit Property';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect(SITE_URL . '/admin/properties.php');
}

// Fetch property
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = :id");
$stmt->execute([':id' => $id]);
$property = $stmt->fetch();

if (!$property) {
    setFlash('danger', 'Property not found.');
    redirect(SITE_URL . '/admin/properties.php');
}

$errors = [];
$types = $pdo->query("SELECT * FROM property_types ORDER BY name ASC")->fetchAll();
$agents = $pdo->query("SELECT * FROM agents WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    }

    $title            = trim($_POST['title'] ?? '');
    $slug             = trim($_POST['slug'] ?? '');
    $property_type_id = (int)($_POST['property_type_id'] ?? 0);
    $agent_id         = (int)($_POST['agent_id'] ?? 0) ?: null;
    $price            = (float)($_POST['price'] ?? 0);
    $price_type       = in_array($_POST['price_type'] ?? '', ['sale', 'rent']) ? $_POST['price_type'] : 'sale';
    $rent_period      = trim($_POST['rent_period'] ?? 'month');
    $status           = in_array($_POST['status'] ?? '', ['available', 'sold', 'rented', 'reserved']) ? $_POST['status'] : 'available';
    $featured         = isset($_POST['featured']) ? 1 : 0;
    $is_active        = isset($_POST['is_active']) ? 1 : 0;

    $bedrooms         = (int)($_POST['bedrooms'] ?? 0);
    $bathrooms        = (int)($_POST['bathrooms'] ?? 0);
    $toilets          = (int)($_POST['toilets'] ?? 0);
    $size_sqm         = (float)($_POST['size_sqm'] ?? 0);
    $floors           = (int)($_POST['floors'] ?? 1);
    $year_built       = (int)($_POST['year_built'] ?? 0) ?: null;

    $garage           = isset($_POST['garage']) ? 1 : 0;
    $swimming_pool    = isset($_POST['swimming_pool']) ? 1 : 0;
    $garden           = isset($_POST['garden']) ? 1 : 0;
    $security         = isset($_POST['security']) ? 1 : 0;
    $borehole         = isset($_POST['borehole']) ? 1 : 0;
    $solar_panel      = isset($_POST['solar_panel']) ? 1 : 0;

    $location_area    = trim($_POST['location_area'] ?? '');
    $address          = trim($_POST['address'] ?? '');
    $city             = trim($_POST['city'] ?? 'Ruiru');
    $county           = trim($_POST['county'] ?? 'Kiambu');
    $latitude         = (float)($_POST['latitude'] ?? -1.1453);
    $longitude        = (float)($_POST['longitude'] ?? 36.9630);

    $video_url        = trim($_POST['video_url'] ?? '');
    $virtual_tour_url = trim($_POST['virtual_tour_url'] ?? '');
    $description      = trim($_POST['description'] ?? '');

    // Validation
    if (empty($title)) $errors[] = 'Property title is required.';
    if ($price <= 0) $errors[] = 'Price must be greater than 0.';
    if ($property_type_id <= 0) $errors[] = 'Please select a property type.';

    // Generate slug
    if (empty($slug)) {
        $slug = makeSlug($title);
    } else {
        $slug = makeSlug($slug);
    }
    
    // Check if slug exists on other properties
    $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE slug = :slug AND id != :id");
    $checkSlug->execute([':slug' => $slug, ':id' => $id]);
    if ($checkSlug->fetchColumn() > 0) {
        $slug = $slug . '-' . time();
    }

    // Cover Image Upload
    $cover_image = $property['cover_image'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($_FILES['cover_image'], 'properties');
        if ($uploaded) {
            $cover_image = $uploaded;
        } else {
            $errors[] = 'Failed to upload cover image. Allowed types: JPG, PNG, WEBP, GIF up to 5MB.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE properties SET
                title = :title,
                slug = :slug,
                description = :description,
                price = :price,
                price_type = :price_type,
                rent_period = :rent_period,
                property_type_id = :property_type_id,
                status = :status,
                featured = :featured,
                bedrooms = :bedrooms,
                bathrooms = :bathrooms,
                toilets = :toilets,
                size_sqm = :size_sqm,
                floors = :floors,
                year_built = :year_built,
                garage = :garage,
                swimming_pool = :swimming_pool,
                garden = :garden,
                security = :security,
                borehole = :borehole,
                solar_panel = :solar_panel,
                address = :address,
                location_area = :location_area,
                city = :city,
                county = :county,
                latitude = :latitude,
                longitude = :longitude,
                agent_id = :agent_id,
                cover_image = :cover_image,
                video_url = :video_url,
                virtual_tour_url = :virtual_tour_url,
                is_active = :is_active
            WHERE id = :id
        ");

        $success = $stmt->execute([
            ':title'            => $title,
            ':slug'             => $slug,
            ':description'      => $description,
            ':price'            => $price,
            ':price_type'       => $price_type,
            ':rent_period'      => $price_type === 'rent' ? $rent_period : null,
            ':property_type_id' => $property_type_id,
            ':status'           => $status,
            ':featured'         => $featured,
            ':bedrooms'         => $bedrooms,
            ':bathrooms'        => $bathrooms,
            ':toilets'          => $toilets,
            ':size_sqm'         => $size_sqm,
            ':floors'           => $floors,
            ':year_built'       => $year_built,
            ':garage'           => $garage,
            ':swimming_pool'    => $swimming_pool,
            ':garden'           => $garden,
            ':security'         => $security,
            ':borehole'         => $borehole,
            ':solar_panel'      => $solar_panel,
            ':address'          => $address,
            ':location_area'    => $location_area,
            ':city'             => $city,
            ':county'           => $county,
            ':latitude'         => $latitude,
            ':longitude'        => $longitude,
            ':agent_id'         => $agent_id,
            ':cover_image'      => $cover_image,
            ':video_url'        => $video_url,
            ':virtual_tour_url' => $virtual_tour_url,
            ':is_active'        => $is_active,
            ':id'               => $id
        ]);

        if ($success) {
            setFlash('success', 'Property "' . $title . '" updated successfully!');
            redirect(SITE_URL . '/admin/properties.php');
        } else {
            $errors[] = 'Database error while updating property.';
        }
    }
}
$csrf = csrfToken();
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="<?= SITE_URL ?>/admin/properties.php" style="color: #94a3b8; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <i class="fas fa-arrow-left"></i> Back to Properties
        </a>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: #f8fafc; margin: 0;">Edit Property #<?= $property['id'] ?></h1>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $property['id'] ?>" target="_blank" class="admin-btn admin-btn-secondary">
            <i class="fas fa-external-link-alt"></i> View Live
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="admin-alert admin-alert-danger" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; border-radius: 12px; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #f87171;">
        <ul style="margin: 0; padding-left: 1.25rem;">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?= SITE_URL ?>/admin/property-edit.php?id=<?= $id ?>" enctype="multipart/form-data" style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.75rem; align-items: start;">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <!-- Main Column -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- Basic Info Card -->
        <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-info-circle" style="color: #3b82f6;"></i> General Information
            </h3>

            <div style="margin-bottom: 1.25rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                    Property Title <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="title" data-slug-source value="<?= e($property['title']) ?>" required
                       class="admin-form-input" style="width: 100%;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                    URL Slug
                </label>
                <input type="text" name="slug" data-slug-target value="<?= e($property['slug']) ?>"
                       class="admin-form-input" style="width: 100%; color: #94a3b8; font-family: monospace;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                    Description & Highlights
                </label>
                <textarea name="description" rows="6" class="admin-form-input" style="width: 100%; resize: vertical;"><?= e($property['description']) ?></textarea>
            </div>
        </div>

        <!-- Pricing & Classification Card -->
        <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-tag" style="color: #10b981;"></i> Pricing & Category
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                        Price (KES) <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="number" name="price" step="1000" min="0" value="<?= (float)$property['price'] ?>" required
                           class="admin-form-input" style="width: 100%;">
                </div>

                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                        Price Type
                    </label>
                    <select name="price_type" class="admin-form-select" style="width: 100%;">
                        <option value="sale" <?= $property['price_type'] === 'sale' ? 'selected' : '' ?>>For Sale</option>
                        <option value="rent" <?= $property['price_type'] === 'rent' ? 'selected' : '' ?>>For Rent</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                        Property Type <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="property_type_id" class="admin-form-select" style="width: 100%;" required>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= (int)$property['property_type_id'] === (int)$t['id'] ? 'selected' : '' ?>>
                                <?= e($t['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                        Assigned Agent
                    </label>
                    <select name="agent_id" class="admin-form-select" style="width: 100%;">
                        <option value="">None / Unassigned</option>
                        <?php foreach ($agents as $ag): ?>
                            <option value="<?= $ag['id'] ?>" <?= (int)$property['agent_id'] === (int)$ag['id'] ? 'selected' : '' ?>>
                                <?= e($ag['name']) ?> (<?= e($ag['title']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Specifications Card -->
        <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-sliders-h" style="color: #8b5cf6;"></i> Specifications & Dimensions
            </h3>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                        Bedrooms
                    </label>
                    <input type="number" name="bedrooms" min="0" value="<?= (int)$property['bedrooms'] ?>" class="admin-form-input" style="width: 100%;">
                </div>
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                        Bathrooms
                    </label>
                    <input type="number" name="bathrooms" min="0" value="<?= (int)$property['bathrooms'] ?>" class="admin-form-input" style="width: 100%;">
                </div>
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                        Toilets
                    </label>
                    <input type="number" name="toilets" min="0" value="<?= (int)$property['toilets'] ?>" class="admin-form-input" style="width: 100%;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem;">
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                        Size (Sq M / Acres)
                    </label>
                    <input type="number" step="0.1" name="size_sqm" min="0" value="<?= (float)$property['size_sqm'] ?>" class="admin-form-input" style="width: 100%;">
                </div>
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                        Floors
                    </label>
                    <input type="number" name="floors" min="1" value="<?= (int)$property['floors'] ?>" class="admin-form-input" style="width: 100%;">
                </div>
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                        Year Built
                    </label>
                    <input type="number" name="year_built" min="1950" max="2030" value="<?= e($property['year_built'] ?? '') ?>" class="admin-form-input" style="width: 100%;">
                </div>
            </div>
        </div>

        <!-- Amenities Checkboxes -->
        <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-check-double" style="color: #f59e0b;"></i> Key Amenities & Features
            </h3>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.6rem; color: #cbd5e1; cursor: pointer;">
                    <input type="checkbox" name="garage" value="1" <?= $property['garage'] ? 'checked' : '' ?>>
                    <span><i class="fas fa-car" style="color: #38bdf8;"></i> Garage / Parking</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.6rem; color: #cbd5e1; cursor: pointer;">
                    <input type="checkbox" name="swimming_pool" value="1" <?= $property['swimming_pool'] ? 'checked' : '' ?>>
                    <span><i class="fas fa-swimming-pool" style="color: #06b6d4;"></i> Swimming Pool</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.6rem; color: #cbd5e1; cursor: pointer;">
                    <input type="checkbox" name="garden" value="1" <?= $property['garden'] ? 'checked' : '' ?>>
                    <span><i class="fas fa-seedling" style="color: #10b981;"></i> Private Garden</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.6rem; color: #cbd5e1; cursor: pointer;">
                    <input type="checkbox" name="security" value="1" <?= $property['security'] ? 'checked' : '' ?>>
                    <span><i class="fas fa-shield-alt" style="color: #f59e0b;"></i> 24hr Security / CCTV</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.6rem; color: #cbd5e1; cursor: pointer;">
                    <input type="checkbox" name="borehole" value="1" <?= $property['borehole'] ? 'checked' : '' ?>>
                    <span><i class="fas fa-tint" style="color: #3b82f6;"></i> Borehole Water</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.6rem; color: #cbd5e1; cursor: pointer;">
                    <input type="checkbox" name="solar_panel" value="1" <?= $property['solar_panel'] ? 'checked' : '' ?>>
                    <span><i class="fas fa-solar-panel" style="color: #eab308;"></i> Solar Panels</span>
                </label>
            </div>
        </div>

        <!-- Location Card -->
        <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-map-marker-alt" style="color: #ef4444;"></i> Location Details
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                        Area / Neighborhood (Ruiru)
                    </label>
                    <input type="text" name="location_area" value="<?= e($property['location_area']) ?>"
                           class="admin-form-input" style="width: 100%;">
                </div>

                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                        Street / Estate Address
                    </label>
                    <input type="text" name="address" value="<?= e($property['address']) ?>"
                           class="admin-form-input" style="width: 100%;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                        Latitude
                    </label>
                    <input type="number" step="any" name="latitude" value="<?= (float)$property['latitude'] ?>" class="admin-form-input" style="width: 100%;">
                </div>
                <div>
                    <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                        Longitude
                    </label>
                    <input type="number" step="any" name="longitude" value="<?= (float)$property['longitude'] ?>" class="admin-form-input" style="width: 100%;">
                </div>
            </div>
        </div>

    </div>

    <!-- Right Sidebar Column -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- Publishing Control -->
        <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem;">
                Listing Status
            </h3>

            <div style="margin-bottom: 1.25rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                    Listing Status
                </label>
                <select name="status" class="admin-form-select" style="width: 100%;">
                    <option value="available" <?= $property['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="reserved" <?= $property['status'] === 'reserved' ? 'selected' : '' ?>>Reserved</option>
                    <option value="sold" <?= $property['status'] === 'sold' ? 'selected' : '' ?>>Sold</option>
                    <option value="rented" <?= $property['status'] === 'rented' ? 'selected' : '' ?>>Rented</option>
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.6rem; color: #cbd5e1; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" <?= $property['is_active'] ? 'checked' : '' ?>>
                    <span><strong>Active & Visible</strong> on website</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.6rem; color: #cbd5e1; cursor: pointer;">
                    <input type="checkbox" name="featured" value="1" <?= $property['featured'] ? 'checked' : '' ?>>
                    <span><strong>Feature</strong> on Homepage</span>
                </label>
            </div>

            <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem;">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>

        <!-- Cover Image Card -->
        <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-camera" style="color: #38bdf8;"></i> Cover Image
            </h3>

            <div style="text-align: center; margin-bottom: 1.25rem;">
                <img id="coverPreview" src="<?= e(propertyImageUrl($property['cover_image'])) ?>" alt="Cover Image"
                     style="width: 100%; height: 170px; object-fit: cover; border-radius: 12px; background: #0f172a; margin-bottom: 1rem; border: 1px solid rgba(255,255,255,0.1);">
                <label style="display: block; border: 2px dashed rgba(255,255,255,0.15); border-radius: 12px; padding: 1rem; cursor: pointer; background: rgba(30,41,59,0.3); transition: border-color 0.2s ease;">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 1.5rem; color: #38bdf8; margin-bottom: 0.25rem; display: block;"></i>
                    <span style="font-size: 0.85rem; color: #94a3b8; display: block;">Change Cover Photo</span>
                    <input type="file" name="cover_image" accept="image/*" data-preview="#coverPreview" style="display: none;">
                </label>
            </div>
        </div>

        <!-- Video & Virtual Tour Card -->
        <div style="background: rgba(15,23,42,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #f8fafc; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-video" style="color: #ec4899;"></i> Virtual Media
            </h3>

            <div style="margin-bottom: 1rem;">
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                    YouTube Video URL
                </label>
                <input type="url" name="video_url" value="<?= e($property['video_url'] ?? '') ?>"
                       class="admin-form-input" style="width: 100%;">
            </div>

            <div>
                <label class="admin-form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                    Virtual Tour URL (3D Walkthrough)
                </label>
                <input type="url" name="virtual_tour_url" value="<?= e($property['virtual_tour_url'] ?? '') ?>"
                       class="admin-form-input" style="width: 100%;">
            </div>
        </div>

    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
