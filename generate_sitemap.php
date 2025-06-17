<?php
/*
* File: /vintage-photo-blog/generate_sitemap.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file generates an XML sitemap for search engines.
* It includes URLs for posts, categories, and static pages.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    echo "You must be logged in as an admin to generate the sitemap.";
    exit;
}

// Start XML output
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

// Add homepage
$xml .= '  <url>' . PHP_EOL;
$xml .= '    <loc>' . SITE_URL . '/</loc>' . PHP_EOL;
$xml .= '    <changefreq>daily</changefreq>' . PHP_EOL;
$xml .= '    <priority>1.0</priority>' . PHP_EOL;
$xml .= '  </url>' . PHP_EOL;

// Add static pages
$staticPages = [
    'categories.php' => ['changefreq' => 'weekly', 'priority' => '0.8'],
    'about.php' => ['changefreq' => 'monthly', 'priority' => '0.7'],
        'contact.php' => ['changefreq' => 'monthly', 'priority' => '0.7'],
];

foreach ($staticPages as $page => $data) {
    $xml .= '  <url>' . PHP_EOL;
    $xml .= '    <loc>' . SITE_URL . '/' . $page . '</loc>' . PHP_EOL;
    $xml .= '    <changefreq>' . $data['changefreq'] . '</changefreq>' . PHP_EOL;
    $xml .= '    <priority>' . $data['priority'] . '</priority>' . PHP_EOL;
    $xml .= '  </url>' . PHP_EOL;
}

// Add categories
$db = new Database();
$db->query("SELECT slug FROM categories");
$categories = $db->resultSet();

foreach ($categories as $category) {
    $xml .= '  <url>' . PHP_EOL;
    $xml .= '    <loc>' . SITE_URL . '/category.php?slug=' . $category['slug'] . '</loc>' . PHP_EOL;
    $xml .= '    <changefreq>weekly</changefreq>' . PHP_EOL;
    $xml .= '    <priority>0.8</priority>' . PHP_EOL;
    $xml .= '  </url>' . PHP_EOL;
}

// Add posts
$db->query("SELECT slug, updated_at FROM posts WHERE status = 'published'");
$posts = $db->resultSet();

foreach ($posts as $post) {
    $xml .= '  <url>' . PHP_EOL;
    $xml .= '    <loc>' . SITE_URL . '/post.php?slug=' . $post['slug'] . '</loc>' . PHP_EOL;
    $xml .= '    <lastmod>' . date('Y-m-d', strtotime($post['updated_at'])) . '</lastmod>' . PHP_EOL;
    $xml .= '    <changefreq>monthly</changefreq>' . PHP_EOL;
    $xml .= '    <priority>0.6</priority>' . PHP_EOL;
    $xml .= '  </url>' . PHP_EOL;
}

// Close XML
$xml .= '</urlset>';

// Save sitemap to file
$file = $_SERVER['DOCUMENT_ROOT'] . '/vintage-photo-blog/sitemap.xml';
if (file_put_contents($file, $xml)) {
    echo "<p>Sitemap generated successfully at: <a href='" . SITE_URL . "/sitemap.xml' target='_blank'>" . SITE_URL . "/sitemap.xml</a></p>";
} else {
    echo "<p>Error: Could not write sitemap file. Check file permissions.</p>";
}

echo "<p><a href='admin/index.php'>Return to Admin Dashboard</a></p>";
?>

