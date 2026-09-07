-- ============================================================
-- Ruiru Prime Properties - Database Schema
-- Version: 1.0
-- ============================================================

CREATE DATABASE IF NOT EXISTS ruiru_realestate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ruiru_realestate;

-- ============================================================
-- SETTINGS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO settings (setting_key, setting_value) VALUES
('company_name', 'Ruiru Prime Properties'),
('company_tagline', 'Your Dream Home Awaits in Ruiru'),
('company_email', 'info@ruiruprimeproperties.co.ke'),
('company_phone', '+254 700 123 456'),
('company_phone2', '+254 720 987 654'),
('company_address', 'Kimbo Road, Ruiru Town, Kiambu County'),
('company_description', 'We are Ruiru\'s leading real estate agency specializing in residential and commercial properties. With over 10 years of experience in the Ruiru property market, we help families and investors find their perfect property.'),
('google_maps_url', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.816!2d36.96!3d-1.145!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f3f0!2sRuiru!5e0!3m2!1sen!2ske!4v1'),
('facebook_url', 'https://facebook.com'),
('twitter_url', 'https://twitter.com'),
('instagram_url', 'https://instagram.com'),
('whatsapp_number', '254700123456'),
('hero_title', 'Find Your Dream Property in Ruiru'),
('hero_subtitle', 'Discover premium residential and commercial properties in the heart of Kiambu County'),
('currency', 'KES'),
('properties_per_page', '9');

-- ============================================================
-- USERS (ADMIN) TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'agent') DEFAULT 'admin',
    avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Default admin: password = admin123
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@ruiruprimeproperties.co.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ============================================================
-- AGENTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    title VARCHAR(100) DEFAULT 'Property Agent',
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    whatsapp VARCHAR(20),
    bio TEXT,
    photo VARCHAR(255) DEFAULT NULL,
    specialization VARCHAR(200),
    experience_years INT DEFAULT 1,
    properties_sold INT DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 5.0,
    facebook_url VARCHAR(255),
    linkedin_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO agents (name, title, email, phone, whatsapp, bio, specialization, experience_years, properties_sold, rating, sort_order) VALUES
('James Kamau', 'Senior Property Consultant', 'james@ruiruprimeproperties.co.ke', '+254 711 111 111', '254711111111', 'James is a seasoned real estate professional with over 8 years of experience in the Ruiru property market. He specializes in residential properties and has helped over 200 families find their dream homes.', 'Residential, Land', 8, 215, 4.9, 1),
('Grace Wanjiku', 'Commercial Property Specialist', 'grace@ruiruprimeproperties.co.ke', '+254 722 222 222', '254722222222', 'Grace brings 6 years of expertise in commercial real estate. She has an exceptional track record in office spaces, retail units, and industrial properties across the greater Ruiru area.', 'Commercial, Office Spaces', 6, 87, 4.8, 2),
('Peter Mwangi', 'Investment Advisor', 'peter@ruiruprimeproperties.co.ke', '+254 733 333 333', '254733333333', 'Peter is our resident investment guru. With a background in finance and 5 years in real estate, he helps clients make smart property investments that maximize returns.', 'Investment, Land', 5, 143, 4.7, 3),
('Faith Ndungu', 'Residential Sales Agent', 'faith@ruiruprimeproperties.co.ke', '+254 744 444 444', '254744444444', 'Faith is passionate about connecting people with their perfect home. Her warm personality and in-depth knowledge of Ruiru neighborhoods make her a client favorite.', 'Apartments, Houses', 3, 67, 5.0, 4);

-- ============================================================
-- PROPERTY CATEGORIES / TYPES
-- ============================================================
CREATE TABLE IF NOT EXISTS property_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'fa-home'
);

INSERT INTO property_types (name, slug, icon) VALUES
('House', 'house', 'fa-home'),
('Apartment', 'apartment', 'fa-building'),
('Land', 'land', 'fa-map'),
('Commercial', 'commercial', 'fa-store'),
('Office Space', 'office', 'fa-briefcase'),
('Warehouse', 'warehouse', 'fa-warehouse');

-- ============================================================
-- PROPERTIES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(15,2) NOT NULL,
    price_type ENUM('sale', 'rent') DEFAULT 'sale',
    rent_period VARCHAR(20) DEFAULT NULL,
    property_type_id INT,
    status ENUM('available', 'sold', 'rented', 'reserved') DEFAULT 'available',
    featured TINYINT(1) DEFAULT 0,
    bedrooms INT DEFAULT 0,
    bathrooms INT DEFAULT 0,
    toilets INT DEFAULT 0,
    size_sqm DECIMAL(10,2) DEFAULT 0,
    floors INT DEFAULT 1,
    garage TINYINT(1) DEFAULT 0,
    swimming_pool TINYINT(1) DEFAULT 0,
    garden TINYINT(1) DEFAULT 0,
    security TINYINT(1) DEFAULT 0,
    borehole TINYINT(1) DEFAULT 0,
    solar_panel TINYINT(1) DEFAULT 0,
    address VARCHAR(255),
    location_area VARCHAR(100),
    city VARCHAR(100) DEFAULT 'Ruiru',
    county VARCHAR(100) DEFAULT 'Kiambu',
    latitude DECIMAL(10,8) DEFAULT -1.1453,
    longitude DECIMAL(11,8) DEFAULT 36.9630,
    agent_id INT,
    cover_image VARCHAR(255),
    video_url VARCHAR(255),
    virtual_tour_url VARCHAR(255),
    year_built INT,
    views INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_type_id) REFERENCES property_types(id),
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL
);

INSERT INTO properties (title, slug, description, price, price_type, property_type_id, status, featured, bedrooms, bathrooms, size_sqm, garage, security, garden, location_area, address, agent_id, cover_image, year_built) VALUES
('Elegant 4-Bedroom Mansion in Ruiru East', 'elegant-4-bedroom-mansion-ruiru-east', 'A stunning 4-bedroom mansion nestled in the serene Ruiru East estate. This property features high-end finishes throughout, spacious living areas, a gourmet kitchen, and breathtaking views of the surrounding landscape. The compound is fully secured with electric fence, CCTV, and 24-hour security. Ideal for a growing family seeking luxury and comfort.', 18500000, 'sale', 1, 'available', 1, 4, 3, 320.00, 1, 1, 1, 'Ruiru East', 'Ruiru East Estate, Off Thika Road', 1, 'property1.jpg', 2020),
('Modern 3-Bedroom Apartment, Kimbo', 'modern-3-bedroom-apartment-kimbo', 'Contemporary 3-bedroom apartment in the heart of Kimbo, Ruiru. Features include open-plan living and dining, master en-suite, balcony with scenic views, and a fitted kitchen. The block has 24hr security, ample parking, and backup generator.', 8900000, 'sale', 2, 'available', 1, 3, 2, 145.00, 0, 1, 0, 'Kimbo', 'Kimbo Road, Ruiru', 2, 'property2.jpg', 2022),
('Prime Commercial Plot - 0.5 Acres', 'prime-commercial-plot-0-5-acres', 'An exceptional 0.5-acre commercial plot strategically located along Thika Road in Ruiru. Perfectly positioned for retail development, a petrol station, or commercial complex. Title deed ready. Road reserve access. High foot traffic area with excellent visibility.', 12000000, 'sale', 4, 'available', 1, 0, 0, 2023.00, 0, 0, 0, 'Thika Road', 'Thika Superhighway, Ruiru Exit', 3, 'property3.jpg', NULL),
('Cozy 2-Bedroom Bungalow, Gitambaya', 'cozy-2-bedroom-bungalow-gitambaya', 'A charming 2-bedroom bungalow in the quiet Gitambaya area of Ruiru. Features a spacious compound, modern kitchen, separate DSQ, and beautiful garden. Perfect for a small family or as a rental investment. Water and electricity connected. Title deed ready.', 6500000, 'sale', 1, 'available', 0, 2, 1, 110.00, 0, 0, 1, 'Gitambaya', 'Gitambaya Estate, Ruiru', 4, 'property4.jpg', 2019),
('Executive Office Space - CBD Ruiru', 'executive-office-space-cbd-ruiru', 'Premium executive office space available for rent in Ruiru CBD. Ground floor unit of 80 sqm with reception area, 3 private offices, boardroom, and modern washrooms. Ample parking. Ideal for a law firm, consultancy, or financial institution.', 45000, 'rent', 5, 'available', 1, 0, 1, 80.00, 0, 1, 0, 'Ruiru CBD', 'Ground Floor, Ruiru Business Centre, Kimbo Road', 2, 'property5.jpg', 2018),
('Residential Plot - 50x100ft, Murera', 'residential-plot-50x100ft-murera', 'Ready-to-build residential plot measuring 50x100ft (approximately 0.11 acres) in the fast-growing Murera area of Ruiru. Flat terrain, corner plot, road access, water and electricity available. Great neighborhood with nearby schools, shops, and matatu routes. Title deed available.', 2800000, 'sale', 3, 'available', 0, 0, 0, 465.00, 0, 0, 0, 'Murera', 'Murera Road, Ruiru', 3, 'property6.jpg', NULL),
('Spacious 5-Bedroom House, Ruiru West', 'spacious-5-bedroom-house-ruiru-west', 'An impressive 5-bedroom home in Ruiru West with ample space for a large family. Features include a large living room, family room, study, modern kitchen, and staff quarters. The compound has a mature garden, borehole, solar panels, and electric fence. School bus route accessible.', 22000000, 'sale', 1, 'available', 1, 5, 4, 420.00, 1, 1, 1, 'Ruiru West', 'Ruiru West Estate, Off Northern Bypass', 1, 'property7.jpg', 2021),
('Studio Apartment for Rent - Ruiru Town', 'studio-apartment-rent-ruiru-town', 'Fully furnished studio apartment available for rent in Ruiru Town. Ideal for young professionals or students. Features a kitchenette, modern bathroom, and high-speed WiFi ready infrastructure. Walking distance to Ruiru Market and matatu stages. Utilities negotiable.', 15000, 'rent', 2, 'available', 0, 0, 1, 35.00, 0, 1, 0, 'Ruiru Town', 'Ruiru Town Centre, Near Stage', 4, 'property8.jpg', 2020),
('1-Acre Agricultural Land, Membley', 'one-acre-agricultural-land-membley', '1-acre agricultural land in Membley, perfect for farming, poultry, or subdivision for residential plots. The land is flat, fenced, and has access to river water. Title deed ready. Located in a quiet area with good murram road access.', 5500000, 'sale', 3, 'available', 0, 0, 0, 4047.00, 0, 0, 0, 'Membley', 'Membley Estate Road, Ruiru', 3, 'property9.jpg', NULL);

-- ============================================================
-- PROPERTY IMAGES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    sort_order INT DEFAULT 0,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- ============================================================
-- INQUIRIES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT DEFAULT NULL,
    agent_id INT DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    inquiry_type ENUM('property', 'general', 'valuation', 'partnership') DEFAULT 'general',
    status ENUM('new', 'read', 'replied', 'closed') DEFAULT 'new',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL
);

-- ============================================================
-- TESTIMONIALS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    client_title VARCHAR(100),
    client_photo VARCHAR(255),
    message TEXT NOT NULL,
    rating INT DEFAULT 5,
    property_type VARCHAR(50),
    is_approved TINYINT(1) DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO testimonials (client_name, client_title, message, rating, property_type, is_approved, is_featured) VALUES
('Daniel Ochieng', 'Business Owner, Ruiru', 'Ruiru Prime Properties helped me find the perfect commercial space for my business. The team was professional, responsive, and guided me through the entire process. I couldn\'t be happier with my new office!', 5, 'Commercial', 1, 1),
('Mary Njeri', 'Teacher, Thika Road', 'I was a first-time home buyer and was quite nervous. James walked me through every step patiently. They found me a beautiful 3-bedroom home within my budget. Highly recommend!', 5, 'Residential', 1, 1),
('Samuel Kariuki', 'Engineer, Nairobi', 'The investment advice I received was top-notch. Peter helped me identify two plots in Ruiru that have since tripled in value. This is the team to trust for real estate investments.', 5, 'Land', 1, 1),
('Esther Wachira', 'Nurse, Ruiru', 'Excellent service from start to finish. Faith was always available to answer my questions and showed me properties that matched exactly what I described. Found my dream home!', 5, 'Residential', 1, 1);

-- ============================================================
-- BLOG POSTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT,
    cover_image VARCHAR(255),
    category VARCHAR(100) DEFAULT 'Real Estate Tips',
    author_id INT,
    tags VARCHAR(255),
    views INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO blog_posts (title, slug, excerpt, content, category, author_id, tags, is_published, published_at) VALUES
('Why Ruiru is the Best Place to Buy Property in 2024', 'why-ruiru-best-place-buy-property-2024', 'Ruiru has emerged as one of the fastest-growing real estate markets in Kenya. Discover why savvy investors are flocking to this vibrant town.', '<p>Ruiru, located along the busy Thika Superhighway in Kiambu County, has transformed from a quiet town into one of Kenya\'s most dynamic real estate hotspots. Here\'s why you should seriously consider investing here.</p><h3>1. Strategic Location</h3><p>Ruiru sits just 25km from Nairobi CBD, making it easily accessible via the Thika Superhighway. The Northern Bypass further connects it to key areas like Kiambu and Westlands.</p><h3>2. Rapid Infrastructure Development</h3><p>The government has invested heavily in roads, water systems, and electricity in Ruiru. The expansion of the Northern Bypass has opened up new areas for development.</p><h3>3. Affordable Land Prices</h3><p>Compared to Nairobi suburbs, Ruiru offers significantly more affordable land and property prices while still being close to the city.</p><h3>4. Growing Population</h3><p>With a rapidly growing population, the demand for housing continues to outstrip supply, making rental investments particularly attractive.</p>', 'Market Insights', 1, 'ruiru,investment,property,kiambu', 1, '2024-01-15 10:00:00'),
('Top 5 Neighborhoods in Ruiru for Families', 'top-5-neighborhoods-ruiru-families', 'Looking to settle your family in Ruiru? We break down the best neighborhoods that offer safety, schools, and community.', '<p>Choosing the right neighborhood is one of the most important decisions when buying a home. Here are the top 5 family-friendly neighborhoods in Ruiru.</p><h3>1. Ruiru East</h3><p>Known for its well-planned estates and serene environment, Ruiru East is popular among middle and upper-class families. It features good road infrastructure and quality schools nearby.</p><h3>2. Kimbo</h3><p>Kimbo offers a mix of apartments and standalone houses at various price points, making it accessible to a wide range of buyers. It has excellent transport links to Nairobi.</p><h3>3. Membley</h3><p>One of Ruiru\'s most established neighborhoods, Membley Estate offers spacious plots and houses in a quiet, leafy setting. Ideal for those who prefer a suburban feel.</p><h3>4. Gitambaya</h3><p>An emerging neighborhood with new developments, Gitambaya offers modern housing at competitive prices with good amenities nearby.</p><h3>5. Murera</h3><p>A fast-growing area with affordable land and housing options, Murera is ideal for first-time home buyers looking to get on the property ladder.</p>', 'Lifestyle', 1, 'ruiru,neighborhoods,family,housing', 1, '2024-02-10 10:00:00'),
('Understanding the Property Buying Process in Kenya', 'understanding-property-buying-process-kenya', 'Buying property in Kenya can be complex. Our step-by-step guide will help you navigate the process confidently.', '<p>The property buying process in Kenya involves several steps that require careful attention. Here\'s your complete guide.</p><h3>Step 1: Define Your Budget</h3><p>Before anything else, determine how much you can afford. Factor in the purchase price, legal fees (about 4-5%), stamp duty (2-4%), and any renovation costs.</p><h3>Step 2: Choose Your Property</h3><p>Work with a reputable agent to identify properties that match your criteria and budget. Visit multiple properties before making a decision.</p><h3>Step 3: Conduct Due Diligence</h3><p>This is the most critical step. Verify the title deed, check for any encumbrances, confirm the land rates are up to date, and ensure the property is not on a road reserve or riparian land.</p><h3>Step 4: Make an Offer</h3><p>Once satisfied, make a formal offer. This is usually done in writing through your advocate.</p><h3>Step 5: Sign Sale Agreement</h3><p>Your advocate will prepare a Sale Agreement outlining all terms. Read it carefully before signing.</p><h3>Step 6: Pay and Transfer</h3><p>Pay the purchase price and facilitate the transfer of title at the Lands Registry.</p>', 'Guides', 1, 'buying,process,kenya,legal,guide', 1, '2024-03-05 10:00:00');

-- ============================================================
-- INDEXES FOR PERFORMANCE
-- ============================================================
ALTER TABLE properties ADD INDEX idx_status (status);
ALTER TABLE properties ADD INDEX idx_featured (featured);
ALTER TABLE properties ADD INDEX idx_price_type (price_type);
ALTER TABLE properties ADD INDEX idx_location (location_area);
ALTER TABLE inquiries ADD INDEX idx_status (status);
ALTER TABLE blog_posts ADD INDEX idx_published (is_published);
