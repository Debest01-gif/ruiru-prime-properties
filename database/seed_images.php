<?php
/**
 * Image Downloader & Seeder for Ruiru Prime Properties
 * Downloads real estate imagery for demo purposes
 */
$images = [
    'uploads/properties/property1.jpg' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=900&auto=format&fit=crop&q=80',
    'uploads/properties/property2.jpg' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=900&auto=format&fit=crop&q=80',
    'uploads/properties/property3.jpg' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=900&auto=format&fit=crop&q=80',
    'uploads/properties/property4.jpg' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=900&auto=format&fit=crop&q=80',
    'uploads/properties/property5.jpg' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=900&auto=format&fit=crop&q=80',
    'uploads/properties/property6.jpg' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=900&auto=format&fit=crop&q=80',
    'uploads/properties/property7.jpg' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=900&auto=format&fit=crop&q=80',
    'uploads/properties/property8.jpg' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=900&auto=format&fit=crop&q=80',
    'uploads/properties/property9.jpg' => 'https://images.unsplash.com/photo-1500076656116-558758c991c1?w=900&auto=format&fit=crop&q=80',
    'uploads/agents/agent1.jpg'         => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&auto=format&fit=crop&q=80',
    'uploads/agents/agent2.jpg'         => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&auto=format&fit=crop&q=80',
    'uploads/agents/agent3.jpg'         => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&auto=format&fit=crop&q=80',
    'uploads/agents/agent4.jpg'         => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400&auto=format&fit=crop&q=80',
    'assets/images/agent-placeholder.jpg'    => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=400&auto=format&fit=crop&q=80',
    'assets/images/about-office.jpg'         => 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=900&auto=format&fit=crop&q=80',
    'assets/images/hero-bg.jpg'              => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1600&auto=format&fit=crop&q=80',
    'uploads/blog/blog1.jpg'           => 'https://images.unsplash.com/photo-1460472178825-e5240623afd5?w=800&auto=format&fit=crop&q=80',
    'uploads/blog/blog2.jpg'           => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&auto=format&fit=crop&q=80',
    'uploads/blog/blog3.jpg'           => 'https://images.unsplash.com/photo-1450133064473-71024230f91b?w=800&auto=format&fit=crop&q=80',
];

$baseDir = dirname(__DIR__);

foreach ($images as $relPath => $url) {
    $fullPath = $baseDir . '/' . str_replace('\\', '/', $relPath);
    $dir = dirname($fullPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    if (!file_exists($fullPath) || filesize($fullPath) < 1000) {
        echo "Downloading: $relPath ... ";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $data = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 && $data) {
            file_put_contents($fullPath, $data);
            echo "OK (" . strlen($data) . " bytes)\n";
        } else {
            echo "FAILED (HTTP $code)\n";
        }
    } else {
        echo "Already exists: $relPath\n";
    }
}
echo "Done!\n";
