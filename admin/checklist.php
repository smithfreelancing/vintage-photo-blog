<?php
/*
* File: /vintage-photo-blog/admin/checklist.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file provides a production readiness checklist.
* It helps ensure the site is ready for production use.
*/

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login.php');
}

$pageTitle = "Production Checklist";

// Define checklist items
$checklist = [
    'security' => [
        'title' => 'Security',
        'items' => [
            [
                'name' => 'Change default admin password',
                'description' => 'Make sure you have changed the default admin password to a strong, unique password.',
                'importance' => 'critical'
            ],
            [
                'name' => 'Secure file permissions',
                'description' => 'Set proper file permissions: 644 for files and 755 for directories.',
                'importance' => 'high'
            ],
            [
                'name' => 'Protect sensitive files',
                'description' => 'Ensure config files and other sensitive files are not accessible from the web.',
                'importance' => 'critical'
            ],
            [
                'name' => 'Enable HTTPS',
                'description' => 'Use SSL/TLS encryption to protect data in transit.',
                'importance' => 'high'
            ],
            [
                'name' => 'Remove test files',
                'description' => 'Delete or restrict access to test files and scripts.',
                'importance' => 'medium'
            ]
        ]
    ],
    'performance' => [
        'title' => 'Performance',
        'items' => [
            [
                'name' => 'Optimize images',
                'description' => 'Compress and resize images to appropriate dimensions.',
                'importance' => 'medium'
            ],
            [
                'name' => 'Enable caching',
                'description' => 'Configure browser caching for static assets.',
                'importance' => 'medium'
            ],
            [
                'name' => 'Minify CSS/JS',
                'description' => 'Minify CSS and JavaScript files to reduce file size.',
                'importance' => 'low'
            ]
        ]
    ],
    'content' => [
        'title' => 'Content',
        'items' => [
            [
                'name' => 'Create essential pages',
                'description' => 'Ensure all essential pages (About, Contact, Privacy Policy, etc.) are created.',
                'importance' => 'high'
            ],
            [
                'name' => 'Check for broken links',
                'description' => 'Verify that all links on the site work correctly.',
                'importance' => 'medium'
            ],
            [
                'name' => 'Proofread content',
                'description' => 'Check all content for spelling and grammatical errors.',
                'importance' => 'medium'
            ]
        ]
    ],
    'seo' => [
        'title' => 'SEO',
        'items' => [
            [
                'name' => 'Generate sitemap',
                'description' => 'Create and submit an XML sitemap to search engines.',
                'importance' => 'medium'
            ],
            [
                'name' => 'Configure robots.txt',
                'description' => 'Set up robots.txt to guide search engine crawlers.',
                'importance' => 'medium'
            ],
            [
                'name' => 'Add meta descriptions',
                'description' => 'Add unique meta descriptions to important pages.',
                'importance' => 'medium'
            ]
        ]
    ],
    'backup' => [
        'title' => 'Backup & Recovery',
        'items' => [
            [
                'name' => 'Create initial backup',
                'description' => 'Make a complete backup of the database and files before going live.',
                'importance' => 'critical'
            ],
            [
                'name' => 'Set up regular backups',
                'description' => 'Configure automated regular backups.',
                'importance' => 'high'
            ],
            [
                'name' => 'Test backup restoration',
                'description' => 'Verify that backups can be successfully restored.',
                'importance' => 'high'
            ]
        ]
    ],
    'legal' => [
        'title' => 'Legal Compliance',
        'items' => [
            [
                'name' => 'Privacy policy',
                'description' => 'Create a privacy policy that complies with relevant laws (GDPR, CCPA, etc.).',
                'importance' => 'high'
            ],
            [
                'name' => 'Terms of service',
                'description' => 'Create terms of service that outline user responsibilities and site policies.',
                'importance' => 'medium'
            ],
            [
                'name' => 'Cookie consent',
                'description' => 'Implement cookie consent mechanism if using cookies.',
                'importance' => 'high'
            ]
        ]
    ]
];

// Start of admin header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Admin' : 'Admin Dashboard'; ?> | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        /* Admin styles */
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        /* Admin Layout */
        .admin-container {
            display: flex;
            min-height: calc(100vh - 56px);
        }
        
        .admin-sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            padding: 1rem 0;
            flex-shrink: 0;
        }
        
        .admin-content {
            flex-grow: 1;
            padding: 1.5rem;
            overflow-x: auto;
        }
        
        /* Sidebar Styles */
        .sidebar-header {
            padding: 0 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }
        
        .admin-sidebar ul li a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .admin-sidebar ul li a:hover,
        .admin-sidebar ul li a.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Checklist Styles */
        .checklist-item {
            margin-bottom: 1rem;
            padding: 1rem;
            border-left: 4px solid #ddd;
            background-color: #fff;
        }
        
        .checklist-item.critical {
            border-left-color: #dc3545;
        }
        
        .checklist-item.high {
            border-left-color: #fd7e14;
        }
        
        .checklist-item.medium {
            border-left-color: #ffc107;
        }
        
        .checklist-item.low {
            border-left-color: #28a745;
        }
        
        .checklist-item .form-check {
            padding-left: 2rem;
        }
        
        .checklist-item .form-check-input {
            margin-top: 0.3rem;
        }
        
                .importance-badge {
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
        
        .importance-badge.critical {
            background-color: #dc3545;
            color: #fff;
        }
        
        .importance-badge.high {
            background-color: #fd7e14;
            color: #fff;
        }
        
        .importance-badge.medium {
            background-color: #ffc107;
            color: #212529;
        }
        
        .importance-badge.low {
            background-color: #28a745;
            color: #fff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo ADMIN_URL; ?>"><?php echo SITE_NAME; ?> Admin</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/posts.php">Posts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/comments.php">Comments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/categories.php">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/users.php">Users</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>" target="_blank">View Site</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php echo $_SESSION['username']; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php">Profile</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h5>Admin Menu</h5>
            </div>
            <ul class="list-unstyled">
                <li>
                    <a href="<?php echo ADMIN_URL; ?>"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/posts.php"><i class="fas fa-file-alt mr-2"></i> Posts</a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/comments.php"><i class="fas fa-comments mr-2"></i> Comments</a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/categories.php"><i class="fas fa-tags mr-2"></i> Categories</a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/users.php"><i class="fas fa-users mr-2"></i> Users</a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/settings.php"><i class="fas fa-cog mr-2"></i> Settings</a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/backup.php"><i class="fas fa-database mr-2"></i> Backup</a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/checklist.php" class="active"><i class="fas fa-tasks mr-2"></i> Checklist</a>
                </li>
            </ul>
        </div>
        
        <div class="admin-content">
            <div class="container-fluid py-4">
                <h1 class="h3 mb-4">Production Readiness Checklist</h1>
                
                <div class="alert alert-info">
                    <p class="mb-0">Use this checklist to ensure your site is ready for production. Check off items as you complete them.</p>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0">Importance Levels:</h2>
                        <button id="reset-checklist" class="btn btn-sm btn-outline-secondary">Reset Checklist</button>
                    </div>
                    <div class="d-flex flex-wrap">
                        <span class="importance-badge critical mr-2 mb-2">Critical</span>
                        <span class="importance-badge high mr-2 mb-2">High</span>
                        <span class="importance-badge medium mr-2 mb-2">Medium</span>
                        <span class="importance-badge low mr-2 mb-2">Low</span>
                    </div>
                </div>
                
                <form id="checklist-form">
                    <?php foreach ($checklist as $section => $sectionData): ?>
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="h5 mb-0"><?php echo $sectionData['title']; ?></h3>
                                <span class="badge badge-secondary" id="<?php echo $section; ?>-progress">0/<?php echo count($sectionData['items']); ?></span>
                            </div>
                            <div class="card-body">
                                <?php foreach ($sectionData['items'] as $index => $item): ?>
                                    <div class="checklist-item <?php echo $item['importance']; ?>">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="<?php echo $section; ?>-<?php echo $index; ?>" data-section="<?php echo $section; ?>">
                                            <label class="form-check-label" for="<?php echo $section; ?>-<?php echo $index; ?>">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <strong><?php echo $item['name']; ?></strong>
                                                    <span class="importance-badge <?php echo $item['importance']; ?>"><?php echo $item['importance']; ?></span>
                                                </div>
                                                <p class="text-muted mb-0 mt-1"><?php echo $item['description']; ?></p>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </form>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="h5 mb-0">Overall Progress</h3>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3">
                            <div class="progress-bar bg-success" id="overall-progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <p class="mb-0">
                            <span id="completed-items">0</span> of <span id="total-items">0</span> items completed
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Load saved checklist state from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            // Count total items
            const totalItems = document.querySelectorAll('.form-check-input').length;
            document.getElementById('total-items').textContent = totalItems;
            
            // Load saved state
            const savedChecklist = JSON.parse(localStorage.getItem('productionChecklist') || '{}');
            
            // Apply saved state to checkboxes
            document.querySelectorAll('.form-check-input').forEach(checkbox => {
                const id = checkbox.id;
                if (savedChecklist[id]) {
                    checkbox.checked = true;
                }
            });
            
            // Update progress
            updateProgress();
            
            // Add event listeners
            document.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    saveChecklistState();
                    updateProgress();
                });
            });
            
            // Reset button
            document.getElementById('reset-checklist').addEventListener('click', function() {
                if (confirm('Are you sure you want to reset the checklist? This will clear all checked items.')) {
                    localStorage.removeItem('productionChecklist');
                    document.querySelectorAll('.form-check-input').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    updateProgress();
                }
            });
        });
        
        // Save checklist state to localStorage
        function saveChecklistState() {
            const checklist = {};
            document.querySelectorAll('.form-check-input').forEach(checkbox => {
                checklist[checkbox.id] = checkbox.checked;
            });
            localStorage.setItem('productionChecklist', JSON.stringify(checklist));
        }
        
        // Update progress indicators
        function updateProgress() {
            // Update section progress
            const sections = {};
            document.querySelectorAll('.form-check-input').forEach(checkbox => {
                const section = checkbox.dataset.section;
                if (!sections[section]) {
                    sections[section] = { total: 0, completed: 0 };
                }
                sections[section].total++;
                if (checkbox.checked) {
                    sections[section].completed++;
                }
            });
            
            // Update section badges
            for (const section in sections) {
                const progressElement = document.getElementById(`${section}-progress`);
                if (progressElement) {
                    progressElement.textContent = `${sections[section].completed}/${sections[section].total}`;
                }
            }
            
            // Update overall progress
            const totalItems = document.querySelectorAll('.form-check-input').length;
            const completedItems = document.querySelectorAll('.form-check-input:checked').length;
            const progressPercentage = totalItems > 0 ? Math.round((completedItems / totalItems) * 100) : 0;
            
            document.getElementById('overall-progress-bar').style.width = `${progressPercentage}%`;
            document.getElementById('overall-progress-bar').textContent = `${progressPercentage}%`;
            document.getElementById('overall-progress-bar').setAttribute('aria-valuenow', progressPercentage);
            
            document.getElementById('completed-items').textContent = completedItems;
        }
    </script>
</body>
</html>

