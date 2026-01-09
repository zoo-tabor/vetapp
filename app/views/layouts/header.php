<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Parazitologická Evidence' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php
    $currentApp = $_SESSION['current_app'] ?? 'parasitology';
    ?>
    <nav class="navbar <?= $currentApp === 'animals' ? 'animals' : ($currentApp === 'biochemistry' ? 'biochemistry' : ($currentApp === 'urineanalysis' ? 'urineanalysis' : ($currentApp === 'vaccination' ? 'vaccination' : ($currentApp === 'warehouse' ? 'warehouse' : '')))) ?>">
        <div class="container-wide">
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Toggle menu">
                <span id="menuIcon">☰</span>
            </button>
            <div class="navbar-brand">
                <div class="app-switcher">
                    <?php
                    $currentApp = $_SESSION['current_app'] ?? 'parasitology';
                    // Determine the correct home URL based on current app
                    $homeUrl = '/';
                    if ($currentApp === 'animals') {
                        $homeUrl = '/animals';
                    } elseif ($currentApp === 'biochemistry') {
                        $homeUrl = '/biochemistry';
                    } elseif ($currentApp === 'urineanalysis') {
                        $homeUrl = '/urineanalysis';
                    } elseif ($currentApp === 'vaccination') {
                        $homeUrl = '/vaccination-plan';
                    } elseif ($currentApp === 'warehouse') {
                        $homeUrl = '/warehouse';
                    }
                    ?>
                    <a href="<?= $homeUrl ?>" class="app-name">
                        <?php
                        if ($currentApp === 'animals') {
                            echo 'Seznam zvířat';
                        } elseif ($currentApp === 'parasitology') {
                            echo 'Parazitologie';
                        } elseif ($currentApp === 'biochemistry') {
                            echo 'Biochemie a hematologie';
                        } elseif ($currentApp === 'urineanalysis') {
                            echo 'Analýza moči';
                        } elseif ($currentApp === 'vaccination') {
                            echo 'Vakcinační plán';
                        } elseif ($currentApp === 'warehouse') {
                            echo 'Sklad';
                        }
                        ?>
                    </a>
                    <div class="app-dropdown">
                        <button class="app-dropdown-toggle" onclick="toggleAppDropdown()">▼</button>
                        <div class="app-dropdown-menu" id="appDropdownMenu">
                            <a href="/app/switch/animals" class="app-dropdown-item animals <?= $currentApp === 'animals' ? 'active' : '' ?>">
                                Seznam zvířat
                            </a>
                            <a href="/app/switch/parasitology" class="app-dropdown-item <?= $currentApp === 'parasitology' ? 'active' : '' ?>">
                                Parazitologie
                            </a>
                            <a href="/app/switch/biochemistry" class="app-dropdown-item biochem <?= $currentApp === 'biochemistry' ? 'active' : '' ?>">
                                Biochemie a hematologie
                            </a>
                            <a href="/app/switch/urineanalysis" class="app-dropdown-item urine <?= $currentApp === 'urineanalysis' ? 'active' : '' ?>">
                                Analýza moči
                            </a>
                            <a href="/app/switch/vaccination" class="app-dropdown-item vaccination <?= $currentApp === 'vaccination' ? 'active' : '' ?>">
                                Vakcinační plán
                            </a>
                            <a href="/app/switch/warehouse" class="app-dropdown-item warehouse <?= $currentApp === 'warehouse' ? 'active' : '' ?>">
                                Sklad
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="navbar-menu">
                <span class="navbar-user">
                    <?= htmlspecialchars(Auth::fullName()) ?>
                    <?php if (Auth::isAdmin()): ?>
                        <span class="badge badge-admin">Admin</span>
                    <?php endif; ?>
                </span>
                <?php if (Auth::isAdmin()): ?>
                    <a href="/admin/settings" class="btn btn-sm btn-outline" title="Administrace" style="margin-right: 10px; background: white !important; color: #000 !important; border: 2px solid #000 !important;">
                        ⚙️ Admin
                    </a>
                <?php endif; ?>
                <a href="/user/settings" class="btn btn-sm btn-outline" title="Moje nastavení" style="margin-right: 10px; background: white !important; color: #000 !important; border: 2px solid #000 !important;">
                    👤
                </a>
                <a href="/logout" class="btn btn-sm btn-outline" style="background: white !important; color: #000 !important; border: 2px solid #000 !important;">Odhlásit</a>
            </div>
        </div>
    </nav>

    <style>
    .navbar.animals {
        background: linear-gradient(135deg, #8e44ad 0%, #7d3c98 100%) !important;
    }

    .navbar.biochemistry {
        background: linear-gradient(135deg, #c0392b 0%, #a93226 100%) !important;
    }

    .navbar.urineanalysis {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important;
    }

    .navbar.vaccination {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important;
    }

    .navbar.warehouse {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%) !important;
    }

    .app-switcher {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .app-name {
        color: white;
        text-decoration: none;
        font-size: 18px;
        font-weight: 600;
    }

    .app-name:hover {
        color: #ddd;
    }

    .app-dropdown {
        position: relative;
    }

    .app-dropdown-toggle {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        transition: background 0.2s;
    }

    .app-dropdown-toggle:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .app-dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: 8px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        min-width: 250px;
        z-index: 1000;
        overflow: hidden;
        border: 1px solid #e0e0e0;
    }

    .app-dropdown-menu.show {
        display: block;
    }

    .app-dropdown-item {
        display: block;
        padding: 14px 18px;
        color: #000;
        text-decoration: none;
        transition: all 0.2s;
        border-bottom: 1px solid #e0e0e0;
        font-size: 16px;
        font-weight: 600;
        background: white;
    }

    .app-dropdown-item:last-child {
        border-bottom: none;
    }

    .app-dropdown-item:not(.active) {
        background: white !important;
        color: #000 !important;
    }

    .app-dropdown-item:not(.active):hover {
        background: white !important;
        color: #666 !important;
    }

    .app-dropdown-item.active {
        background: #667eea !important;
        color: white !important;
        font-weight: 700;
    }

    .app-dropdown-item.active:hover {
        background: #5568d3 !important;
        color: white !important;
    }

    .app-dropdown-item.biochem.active {
        background: #c0392b !important;
        color: white !important;
    }

    .app-dropdown-item.biochem.active:hover {
        background: #a93226 !important;
        color: white !important;
    }

    .app-dropdown-item.urine.active {
        background: #f39c12 !important;
        color: white !important;
    }

    .app-dropdown-item.urine.active:hover {
        background: #e67e22 !important;
        color: white !important;
    }

    .app-dropdown-item.warehouse.active {
        background: #27ae60 !important;
        color: white !important;
    }

    .app-dropdown-item.warehouse.active:hover {
        background: #229954 !important;
        color: white !important;
    }

    .app-dropdown-item.animals.active {
        background: #8e44ad !important;
        color: white !important;
    }

    .app-dropdown-item.animals.active:hover {
        background: #7d3c98 !important;
        color: white !important;
    }

    .app-dropdown-item.vaccination.active {
        background: #3498db !important;
        color: white !important;
    }

    .app-dropdown-item.vaccination.active:hover {
        background: #2980b9 !important;
        color: white !important;
    }

    /* Mobile optimization for app dropdown */
    @media (max-width: 768px) {
        .app-switcher {
            position: relative;
            width: 100%;
        }

        .app-dropdown {
            position: static;
        }

        .app-dropdown-menu {
            position: fixed;
            left: 0;
            right: 0;
            top: auto;
            margin-top: 0;
            width: 100%;
            max-width: 100%;
            min-width: 100%;
            border-radius: 0;
            border-left: none;
            border-right: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 2000;
        }

        .app-dropdown-toggle {
            padding: 6px 12px;
            font-size: 14px;
        }

        .app-dropdown-item {
            padding: 16px 20px;
            font-size: 17px;
            text-align: left;
        }

        /* Position dropdown below navbar */
        .navbar {
            position: relative;
        }
    }

    @media (max-width: 480px) {
        .app-name {
            font-size: 16px;
        }

        .app-dropdown-toggle {
            padding: 5px 10px;
            font-size: 13px;
        }

        .app-dropdown-item {
            padding: 14px 16px;
            font-size: 16px;
        }
    }
    </style>

    <script>
    function toggleAppDropdown() {
        const menu = document.getElementById('appDropdownMenu');
        const navbar = document.querySelector('.navbar');
        menu.classList.toggle('show');

        // Position dropdown below navbar on mobile
        if (window.innerWidth <= 768) {
            const navbarHeight = navbar.offsetHeight;
            const navbarTop = navbar.getBoundingClientRect().top;
            menu.style.top = (navbarTop + navbarHeight) + 'px';
        }
    }

    // Toggle mobile menu
    function toggleMobileMenu() {
        const navbarMenu = document.querySelector('.navbar-menu');
        const menuIcon = document.getElementById('menuIcon');

        navbarMenu.classList.toggle('show');

        // Change icon between hamburger and X
        if (navbarMenu.classList.contains('show')) {
            menuIcon.textContent = '✕';
        } else {
            menuIcon.textContent = '☰';
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.querySelector('.app-dropdown');
        const dropdownToggle = document.querySelector('.app-dropdown-toggle');
        const dropdownMenu = document.getElementById('appDropdownMenu');

        // Check if click is outside both the dropdown button and menu
        if (dropdown && !dropdown.contains(event.target)) {
            dropdownMenu.classList.remove('show');
        }
    });

    // Close mobile menu when clicking on a link
    document.addEventListener('DOMContentLoaded', function() {
        const navbarMenu = document.querySelector('.navbar-menu');
        if (navbarMenu) {
            const menuLinks = navbarMenu.querySelectorAll('a');
            menuLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        navbarMenu.classList.remove('show');
                        document.getElementById('menuIcon').textContent = '☰';
                    }
                });
            });
        }

        // Close app dropdown when clicking on an app item
        const appDropdownItems = document.querySelectorAll('.app-dropdown-item');
        appDropdownItems.forEach(function(item) {
            item.addEventListener('click', function() {
                document.getElementById('appDropdownMenu').classList.remove('show');
            });
        });

        // Update dropdown position on scroll/resize for mobile
        if (window.innerWidth <= 768) {
            window.addEventListener('scroll', function() {
                const menu = document.getElementById('appDropdownMenu');
                const navbar = document.querySelector('.navbar');
                if (menu.classList.contains('show')) {
                    const navbarHeight = navbar.offsetHeight;
                    const navbarTop = navbar.getBoundingClientRect().top;
                    menu.style.top = (navbarTop + navbarHeight) + 'px';
                }
            });
        }
    });
    </script>