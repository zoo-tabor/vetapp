/**
 * Hlavní JavaScript aplikace
 */

// Inicializace po načtení stránky
document.addEventListener('DOMContentLoaded', function() {
    console.log('Parazitologická Evidence - Aplikace načtena');

    // Inicializovat komponenty
    initTables();
    initForms();
    initFilters();
    initMobileOptimizations();
    initModals();
});

/**
 * Inicializace tabulek
 */
function initTables() {
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(table => {
        // Přidat hover efekt na řádky
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('click', function() {
                // Případná interakce při kliknutí na řádek
            });
        });
    });
}

/**
 * Inicializace formulářů
 */
function initForms() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Validace před odesláním
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Vyplňte prosím všechna povinná pole');
            }
        });
    });
}

/**
 * Inicializace filtrů
 */
function initFilters() {
    const filterForm = document.querySelector('.filters-form');
    
    if (filterForm) {
        // Auto-submit při změně selectů
        const selects = filterForm.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                // Můžeme buď auto-submit nebo nechat uživatele kliknout na tlačítko
                // filterForm.submit();
            });
        });
    }
}

/**
 * Pomocné funkce
 */

// Formátování data
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('cs-CZ');
}

// Confirm dialog pro mazání
function confirmDelete(message) {
    return confirm(message || 'Opravdu chcete tento záznam smazat?');
}

// Zobrazení notifikace
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Export do CSV (základní implementace)
function exportToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = Array.from(cols).map(col => {
            return '"' + col.textContent.trim().replace(/"/g, '""') + '"';
        });
        csv.push(rowData.join(','));
    });

    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Mobile-specific optimizations
 */
function initMobileOptimizations() {
    // Detect if mobile device
    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

    if (isMobile) {
        // Add mobile class to body
        document.body.classList.add('mobile-device');

        // Optimize table scrolling on mobile
        optimizeTableScrolling();

        // Add touch-friendly interactions
        addTouchFeedback();
    }

    // Handle orientation changes
    window.addEventListener('orientationchange', function() {
        setTimeout(function() {
            // Recalculate viewport dimensions
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }, 100);
    });

    // Set CSS custom property for real viewport height (handles mobile browser bars)
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
}

/**
 * Optimize table scrolling for mobile
 */
function optimizeTableScrolling() {
    const tableWrappers = document.querySelectorAll('.table-responsive, .examination-table-wrapper');

    tableWrappers.forEach(wrapper => {
        // Add scroll indicator
        if (wrapper.scrollWidth > wrapper.clientWidth) {
            wrapper.classList.add('has-horizontal-scroll');

            // For examination tables, use CSS ::after for scroll hint
            // Hide it after first scroll
            wrapper.addEventListener('scroll', function() {
                wrapper.classList.add('scrolled');
            }, { once: true });

            // For regular tables, add scroll hint below
            if (wrapper.classList.contains('table-responsive')) {
                const scrollHint = document.createElement('div');
                scrollHint.className = 'scroll-hint';
                scrollHint.innerHTML = '← Swipe to scroll →';
                scrollHint.style.cssText = 'text-align: center; padding: 0.5rem; background: #f0f0f0; color: #666; font-size: 0.85rem; border-top: 1px solid #ddd;';

                // Show hint initially, hide after first scroll
                if (wrapper.parentElement) {
                    wrapper.parentElement.appendChild(scrollHint);
                }

                wrapper.addEventListener('scroll', function() {
                    scrollHint.style.display = 'none';
                }, { once: true });
            }
        }

        // Make examination table wrapper relative for positioning
        if (wrapper.classList.contains('examination-table-wrapper')) {
            wrapper.style.position = 'relative';
        }
    });

    // Optimize examination history tables specifically
    const examTables = document.querySelectorAll('.examination-history-table');
    examTables.forEach(table => {
        // Add smooth scrolling
        if (table.parentElement) {
            table.parentElement.style.scrollBehavior = 'smooth';
        }
    });
}

/**
 * Add touch feedback for interactive elements
 */
function addTouchFeedback() {
    const interactiveElements = document.querySelectorAll('.btn, .card, .workplace-card, a:not(.navbar a)');

    interactiveElements.forEach(element => {
        element.addEventListener('touchstart', function() {
            this.style.opacity = '0.7';
        });

        element.addEventListener('touchend', function() {
            this.style.opacity = '1';
        });

        element.addEventListener('touchcancel', function() {
            this.style.opacity = '1';
        });
    });
}

/**
 * Initialize modals
 */
function initModals() {
    // Close modal when clicking outside
    const modals = document.querySelectorAll('.modal');

    modals.forEach(modal => {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeModal(modal.id);
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal(modal.id);
            }
        });
    });
}

/**
 * Open modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
}

/**
 * Close modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = ''; // Restore scrolling
    }
}

/**
 * Detect swipe gestures (for future enhancements)
 */
function detectSwipe(element, callback) {
    let touchStartX = 0;
    let touchStartY = 0;
    let touchEndX = 0;
    let touchEndY = 0;

    element.addEventListener('touchstart', function(event) {
        touchStartX = event.changedTouches[0].screenX;
        touchStartY = event.changedTouches[0].screenY;
    });

    element.addEventListener('touchend', function(event) {
        touchEndX = event.changedTouches[0].screenX;
        touchEndY = event.changedTouches[0].screenY;
        handleSwipe();
    });

    function handleSwipe() {
        const diffX = touchEndX - touchStartX;
        const diffY = touchEndY - touchStartY;

        // Minimum swipe distance
        if (Math.abs(diffX) > 50 || Math.abs(diffY) > 50) {
            if (Math.abs(diffX) > Math.abs(diffY)) {
                // Horizontal swipe
                if (diffX > 0) {
                    callback('right');
                } else {
                    callback('left');
                }
            } else {
                // Vertical swipe
                if (diffY > 0) {
                    callback('down');
                } else {
                    callback('up');
                }
            }
        }
    }
}