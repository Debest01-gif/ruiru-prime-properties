<?php
/**
 * Helper Functions
 * Ruiru Prime Properties
 */

/**
 * Format price in KES
 */
function formatPrice(float $price): string {
    return 'KES ' . number_format($price, 0);
}

/**
 * Format price with period for rentals
 */
function formatRentPrice(float $price, string $period = 'month'): string {
    return 'KES ' . number_format($price, 0) . '/' . $period;
}

/**
 * Generate a URL-friendly slug
 */
function makeSlug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Get property image URL or fallback
 */
function propertyImageUrl(string $image = '', bool $thumb = false): string {
    if (!empty($image)) {
        $path = __DIR__ . '/../uploads/properties/' . $image;
        if (file_exists($path)) {
            return UPLOADS_URL . 'properties/' . $image;
        }
    }
    // Return gradient placeholder
    return SITE_URL . '/assets/images/property-placeholder.jpg';
}

/**
 * Get agent photo URL or fallback
 */
function agentPhotoUrl(string $photo = ''): string {
    if (!empty($photo)) {
        $path = __DIR__ . '/../uploads/agents/' . $photo;
        if (file_exists($path)) {
            return UPLOADS_URL . 'agents/' . $photo;
        }
    }
    return SITE_URL . '/assets/images/agent-placeholder.jpg';
}

/**
 * Sanitize output
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Truncate text
 */
function truncate(string $text, int $length = 150): string {
    $text = strip_tags($text);
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Time ago format
 */
function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff/60) . ' min ago';
    if ($diff < 86400) return floor($diff/3600) . ' hours ago';
    if ($diff < 2592000) return floor($diff/86400) . ' days ago';
    if ($diff < 31536000) return floor($diff/2592000) . ' months ago';
    return floor($diff/31536000) . ' years ago';
}

/**
 * Flash message system
 */
function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render star rating
 */
function starRating(float $rating): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<i class="fas fa-star"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<i class="fas fa-star-half-alt"></i>';
        } else {
            $html .= '<i class="far fa-star"></i>';
        }
    }
    return $html;
}

/**
 * Upload image helper
 */
function uploadImage(array $file, string $folder = 'properties'): ?string {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if (!in_array($file['type'], $allowedTypes)) return null;
    if ($file['size'] > $maxSize) return null;

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '.' . strtolower($ext);
    $uploadDir = __DIR__ . '/../uploads/' . $folder . '/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        return $filename;
    }
    return null;
}

/**
 * Get status badge HTML
 */
function statusBadge(string $status): string {
    $badges = [
        'available' => '<span class="badge badge-success">Available</span>',
        'sold'      => '<span class="badge badge-danger">Sold</span>',
        'rented'    => '<span class="badge badge-warning">Rented</span>',
        'reserved'  => '<span class="badge badge-info">Reserved</span>',
    ];
    return $badges[$status] ?? '<span class="badge">' . e($status) . '</span>';
}

/**
 * Pagination helper
 */
function paginate(int $total, int $perPage, int $current, string $url): string {
    $pages = ceil($total / $perPage);
    if ($pages <= 1) return '';

    $html = '<div class="pagination">';
    if ($current > 1) {
        $html .= '<a href="' . $url . '?page=' . ($current - 1) . '" class="page-btn"><i class="fas fa-chevron-left"></i></a>';
    }
    for ($i = 1; $i <= $pages; $i++) {
        $active = $i == $current ? ' active' : '';
        $html .= '<a href="' . $url . '?page=' . $i . '" class="page-btn' . $active . '">' . $i . '</a>';
    }
    if ($current < $pages) {
        $html .= '<a href="' . $url . '?page=' . ($current + 1) . '" class="page-btn"><i class="fas fa-chevron-right"></i></a>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * CSRF Token
 */
function csrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $token = $_POST['csrf_token'] ?? '';
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect helper
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn(): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require admin login
 */
function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        redirect(SITE_URL . '/admin/login.php');
    }
}

/**
 * Get property amenities as array
 */
function getAmenities(array $property): array {
    $amenities = [];
    if ($property['garage']) $amenities[] = ['icon' => 'fa-car', 'label' => 'Garage'];
    if ($property['swimming_pool']) $amenities[] = ['icon' => 'fa-swimming-pool', 'label' => 'Swimming Pool'];
    if ($property['garden']) $amenities[] = ['icon' => 'fa-seedling', 'label' => 'Garden'];
    if ($property['security']) $amenities[] = ['icon' => 'fa-shield-alt', 'label' => '24hr Security'];
    if ($property['borehole']) $amenities[] = ['icon' => 'fa-tint', 'label' => 'Borehole'];
    if ($property['solar_panel']) $amenities[] = ['icon' => 'fa-solar-panel', 'label' => 'Solar Panels'];
    return $amenities;
}
