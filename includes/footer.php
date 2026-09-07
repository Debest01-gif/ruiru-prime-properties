<?php
/**
 * Shared Footer
 * Ruiru Prime Properties
 */
?>

<!-- ===== WHATSAPP FLOAT BUTTON ===== -->
<a href="https://wa.me/<?= e(setting('whatsapp_number')) ?>?text=Hello! I'm interested in your properties in Ruiru." 
   class="whatsapp-float" target="_blank" aria-label="Chat on WhatsApp">
    <i class="fab fa-whatsapp"></i>
    <span class="whatsapp-tooltip">Chat with us!</span>
</a>

<!-- ===== SCROLL TO TOP ===== -->
<button class="scroll-top" id="scrollTop" aria-label="Scroll to top">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- ===== FOOTER ===== -->
<footer class="footer">
    <div class="footer-top">
        <div class="container">
            <div class="footer-grid">

                <!-- Company Info -->
                <div class="footer-col footer-about">
                    <div class="footer-brand">
                        <div class="brand-logo"><i class="fas fa-building"></i></div>
                        <div class="brand-text">
                            <span class="brand-name"><?= e(setting('company_name')) ?></span>
                            <span class="brand-sub">Premium Real Estate</span>
                        </div>
                    </div>
                    <p><?= e(truncate(setting('company_description'), 180)) ?></p>
                    <div class="social-links">
                        <a href="<?= e(setting('facebook_url')) ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= e(setting('instagram_url')) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="<?= e(setting('twitter_url')) ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                        <a href="https://wa.me/<?= e(setting('whatsapp_number')) ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?= SITE_URL ?>/index.php"><i class="fas fa-angle-right"></i> Home</a></li>
                        <li><a href="<?= SITE_URL ?>/properties.php"><i class="fas fa-angle-right"></i> All Properties</a></li>
                        <li><a href="<?= SITE_URL ?>/properties.php?type=sale"><i class="fas fa-angle-right"></i> For Sale</a></li>
                        <li><a href="<?= SITE_URL ?>/properties.php?type=rent"><i class="fas fa-angle-right"></i> For Rent</a></li>
                        <li><a href="<?= SITE_URL ?>/agents.php"><i class="fas fa-angle-right"></i> Our Agents</a></li>
                        <li><a href="<?= SITE_URL ?>/about.php"><i class="fas fa-angle-right"></i> About Us</a></li>
                        <li><a href="<?= SITE_URL ?>/blog.php"><i class="fas fa-angle-right"></i> Blog</a></li>
                        <li><a href="<?= SITE_URL ?>/contact.php"><i class="fas fa-angle-right"></i> Contact</a></li>
                    </ul>
                </div>

                <!-- Property Types -->
                <div class="footer-col">
                    <h4>Property Types</h4>
                    <ul>
                        <?php
                        $types = $pdo->query("SELECT name, slug FROM property_types")->fetchAll();
                        foreach ($types as $type):
                        ?>
                        <li>
                            <a href="<?= SITE_URL ?>/properties.php?property_type=<?= e($type['slug']) ?>">
                                <i class="fas fa-angle-right"></i> <?= e($type['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        <li><a href="<?= SITE_URL ?>/mortgage-calculator.php"><i class="fas fa-angle-right"></i> Mortgage Calculator</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="footer-col">
                    <h4>Contact Us</h4>
                    <div class="footer-contact-list">
                        <div class="footer-contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= e(setting('company_address')) ?></span>
                        </div>
                        <div class="footer-contact-item">
                            <i class="fas fa-phone-alt"></i>
                            <div>
                                <a href="tel:<?= e(setting('company_phone')) ?>"><?= e(setting('company_phone')) ?></a><br>
                                <a href="tel:<?= e(setting('company_phone2')) ?>"><?= e(setting('company_phone2')) ?></a>
                            </div>
                        </div>
                        <div class="footer-contact-item">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:<?= e(setting('company_email')) ?>"><?= e(setting('company_email')) ?></a>
                        </div>
                        <div class="footer-contact-item">
                            <i class="fas fa-clock"></i>
                            <span>Mon - Sat: 8:00 AM - 6:00 PM<br>Sunday: 10:00 AM - 3:00 PM</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= e(setting('company_name')) ?>. All rights reserved. | Ruiru, Kiambu County, Kenya</p>
            <p>Designed with <i class="fas fa-heart" style="color:#d4a843"></i> for Real Estate Excellence</p>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
