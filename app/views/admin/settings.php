<?php $layout = 'main'; ?>

<div class="page-header">
    <div class="breadcrumb">
        <a href="/">Pracoviště</a> / Administrace
    </div>
    <h1>⚙️ Administrace systému</h1>
</div>

<!-- Tabs for Users and Animals -->
<div class="tabs">
    <button class="tab active" onclick="switchTab('users')">👤 Uživatelé</button>
    <button class="tab" onclick="switchTab('animals')">🦁 Přiřazení zvířat</button>
</div>

<!-- Users Tab Content -->
<div id="users-content" class="tab-content active">
    <div class="card">
    <div class="card-header">
        <h2>Správa uživatelů</h2>
        <button type="button" class="btn btn-sm btn-admin-primary" onclick="openUserModal()">
            ➕ Přidat uživatele
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Uživatelské jméno</th>
                        <th>Celé jméno</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Stav</th>
                        <th>Vytvořeno</th>
                        <th>Akce</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['full_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($user['email'] ?? '-') ?></td>
                        <td>
                            <?php
                            $roles = [
                                'admin' => ['Admin', 'admin'],
                                'user'  => ['Uživatel', 'info'],
                            ];
                            $role = $roles[$user['role']] ?? ['Neznámá', 'secondary'];
                            ?>
                            <span class="badge badge-<?= $role[1] ?>"><?= $role[0] ?></span>
                        </td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="badge badge-success">Aktivní</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Neaktivní</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline" onclick="editUser(<?= $user['id'] ?>)">
                                ✏️ Upravit
                            </button>
                            <button type="button" class="btn btn-sm btn-outline" onclick="managePermissions(<?= $user['id'] ?>)">
                                🔐 Oprávnění
                            </button>
                            <?php if (!empty($user['email'])): ?>
                                <button type="button" class="btn btn-sm btn-outline" onclick="resendPasswordSetup(<?= $user['id'] ?>)" title="Znovu odeslat odkaz pro nastavení hesla">
                                    📧 Odeslat heslo
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-outline" style="color: #e74c3c;" onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>')" title="Smazat uživatele">
                                🗑️ Smazat
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>
</div>

<!-- Animals Assignment Tab Content -->
<div id="animals-content" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h2>Přiřazení zvířat k ošetřovatelům</h2>
        </div>
        <div class="card-body">
            <div class="filter-controls">
                <div class="filter-group">
                    <label for="workplaceFilter">Filtr pracoviště:</label>
                    <select id="workplaceFilter" class="form-control">
                        <option value="">Všechna pracoviště</option>
                        <?php foreach ($workplaces as $workplace): ?>
                            <option value="<?= $workplace['id'] ?>"><?= htmlspecialchars($workplace['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="searchAnimalInput">Vyhledat zvíře:</label>
                    <input type="text" id="searchAnimalInput" class="form-control" placeholder="Jméno, ID, druh...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pracoviště</th>
                            <th>Jméno zvířete</th>
                            <th>ID zvířete</th>
                            <th>Druh</th>
                            <th>Přiřazený ošetřovatel</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody id="animalsTableBody">
                        <?php foreach ($animals as $animal): ?>
                        <tr data-workplace-id="<?= $animal['workplace_id'] ?>"
                            data-search="<?= strtolower(htmlspecialchars($animal['name'] . ' ' . $animal['identifier'] . ' ' . $animal['species'])) ?>">
                            <td>
                                <span class="badge badge-info"><?= htmlspecialchars($animal['workplace_name']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($animal['name'] ?: '-') ?></td>
                            <td class="animal-id"><?= htmlspecialchars($animal['identifier'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($animal['species']) ?></td>
                            <td>
                                <?php if ($animal['assigned_user']): ?>
                                    <span class="badge badge-success">
                                        <?= htmlspecialchars($animal['assigned_user']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Nepřiřazeno</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline" onclick="assignKeeper(<?= $animal['id'] ?>, '<?= htmlspecialchars($animal['name'] ?: $animal['species'], ENT_QUOTES) ?>')">
                                    ✏️ Přiřadit
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- User Modal -->
<div id="userModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="userModalTitle">Přidat uživatele</h2>
            <span class="modal-close" onclick="closeUserModal()">&times;</span>
        </div>
        <form id="userForm">
            <input type="hidden" id="user_id" name="user_id">

            <div class="form-group">
                <label for="username">Uživatelské jméno: *</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="full_name">Celé jméno:</label>
                <input type="text" id="full_name" name="full_name" class="form-control">
            </div>

            <div class="form-group">
                <label for="email">Email: *</label>
                <input type="email" id="email" name="email" class="form-control" required>
                <small class="form-text">Na tento e-mail bude zaslán odkaz pro nastavení hesla</small>
            </div>

            <div class="form-group">
                <label for="role">Role: *</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="user">Uživatel</option>
                    <option value="admin">Admin (plná správa)</option>
                </select>
            </div>

            <div class="form-group" id="passwordGroup" style="display: none;">
                <label for="password">Nové heslo:</label>
                <input type="password" id="password" name="password" class="form-control">
                <small class="form-text">Ponechte prázdné pro zachování stávajícího hesla</small>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="is_active" name="is_active" checked>
                    Aktivní účet
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-admin-primary">Uložit</button>
                <button type="button" class="btn btn-outline" onclick="closeUserModal()">Zrušit</button>
            </div>
        </form>
    </div>
</div>

<!-- Permissions Modal -->
<div id="permissionsModal" class="modal" style="display: none;">
    <div class="modal-content permissions-modal-content">
        <div class="modal-header">
            <h2>Oprávnění uživatele: <span id="permissionsUserName"></span></h2>
            <span class="modal-close" onclick="closePermissionsModal()">&times;</span>
        </div>
        <div style="padding: 20px;">
            <div class="table-responsive">
                <table class="table permissions-grid" id="permissionsTable">
                    <thead></thead>
                    <tbody id="permissionsTableBody"></tbody>
                </table>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-admin-primary" onclick="savePermissions()">Uložit oprávnění</button>
                <button type="button" class="btn btn-outline" onclick="closePermissionsModal()">Zrušit</button>
            </div>
        </div>
    </div>
</div>

<!-- Keeper Assignment Modal -->
<div id="keeperModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Přiřadit ošetřovatele: <span id="keeperAnimalName"></span></h2>
            <span class="modal-close" onclick="closeKeeperModal()">&times;</span>
        </div>
        <form id="keeperForm">
            <input type="hidden" id="keeper_animal_id" name="animal_id">

            <div class="form-group">
                <label for="assigned_user">Ošetřovatel (uživatelské jméno): *</label>
                <select id="assigned_user" name="assigned_user" class="form-control" required>
                    <option value="">-- Nepřiřazeno --</option>
                    <?php foreach ($users as $user): ?>
                        <?php if ($user['is_active']): ?>
                            <option value="<?= htmlspecialchars($user['username']) ?>">
                                <?= htmlspecialchars($user['full_name'] ? $user['full_name'] . ' (' . $user['username'] . ')' : $user['username']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <small class="form-text">Vyberte uživatele, který bude zodpovídat za péči o toto zvíře</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-admin-primary">Uložit</button>
                <button type="button" class="btn btn-outline" onclick="closeKeeperModal()">Zrušit</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Tabs */
.tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    border-bottom: 2px solid #ecf0f1;
}

.tab {
    background: none;
    border: none;
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
    color: #7f8c8d;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
    margin-bottom: -2px;
}

.tab:hover {
    color: #3498db;
}

.tab.active {
    color: #3498db;
    border-bottom-color: #3498db;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Filter Controls */
.filter-controls {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #2c3e50;
}

.animal-id {
    font-family: 'Courier New', monospace;
    color: #7f8c8d;
}

<style>
/* Admin page: all table headers and primary buttons = #3498db */
.card .table thead th {
    background-color: #3498db;
    color: white;
}

/* Permissions grid */
.permissions-modal-content {
    max-width: min(95vw, 1100px);
}

.permissions-grid thead th {
    background-color: #3498db;
    color: white;
}

.permissions-grid tbody td:first-child {
    font-weight: 500;
    white-space: nowrap;
}

.permissions-grid input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

.btn-admin-primary {
    background-color: #3498db;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: background-color 0.2s;
}

.btn-admin-primary:hover {
    background-color: #2980b9;
}

.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
    padding: 20px;
    background-color: #3498db;
    color: white;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.modal-close {
    color: white;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.modal-close:hover {
    color: #ecf0f1;
}

.modal-content form {
    padding: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #2c3e50;
}

.form-text {
    display: block;
    margin-top: 5px;
    font-size: 0.875rem;
    color: #666;
}

.form-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
</style>

<script>
let currentEditingUserId = null;
let currentPermissionsUserId = null;

// User Modal Functions
function openUserModal() {
    currentEditingUserId = null;
    document.getElementById('userModalTitle').textContent = 'Přidat uživatele';
    document.getElementById('userForm').reset();
    document.getElementById('user_id').value = '';
    document.getElementById('passwordGroup').style.display = 'none';
    document.getElementById('password').required = false;
    document.getElementById('email').required = true;
    document.getElementById('userModal').style.display = 'block';
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
    document.getElementById('userForm').reset();
}

function editUser(userId) {
    currentEditingUserId = userId;

    // Fetch user data
    fetch(`/admin/users/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                document.getElementById('userModalTitle').textContent = 'Upravit uživatele';
                document.getElementById('user_id').value = user.id;
                document.getElementById('username').value = user.username;
                document.getElementById('full_name').value = user.full_name || '';
                document.getElementById('email').value = user.email || '';
                document.getElementById('role').value = user.role;
                document.getElementById('is_active').checked = user.is_active == 1;

                // Password field is shown for manual password reset in edit mode
                document.getElementById('passwordGroup').style.display = 'block';
                document.getElementById('password').required = false;
                document.getElementById('password').value = '';

                document.getElementById('userModal').style.display = 'block';
            } else {
                alert('Chyba při načítání uživatele: ' + (data.error || 'Neznámá chyba'));
            }
        })
        .catch(error => {
            alert('Chyba při komunikaci se serverem: ' + error.message);
            console.error('Error:', error);
        });
}

// Handle user form submission
document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const userId = formData.get('user_id');
    const url = userId ? `/admin/users/${userId}` : '/admin/users/create';

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeUserModal();
            location.reload();
        } else {
            alert('Chyba: ' + (data.error || 'Neznámá chyba'));
        }
    })
    .catch(error => {
        alert('Chyba při komunikaci se serverem: ' + error.message);
        console.error('Error:', error);
    });
});

// Permissions Modal Functions
const SECTIONS = [
    { key: 'animals',      label: 'Seznam zvířat' },
    { key: 'parasitology', label: 'Parazitologie' },
    { key: 'biochemistry', label: 'Biochemie a hematologie' },
    { key: 'urine',        label: 'Analýza moči' },
    { key: 'vaccination',  label: 'Vakcinační plán' },
    { key: 'warehouse',    label: 'Sklad' },
    { key: 'lexikon',      label: 'Lexikon' },
];

function managePermissions(userId) {
    currentPermissionsUserId = userId;

    fetch(`/admin/users/${userId}/permissions`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Chyba při načítání oprávnění: ' + (data.error || 'Neznámá chyba'));
                return;
            }

            document.getElementById('permissionsUserName').textContent = data.user.full_name || data.user.username;

            const workplaces = data.workplaces;

            // Build lookup: { workplace_id: { section: { can_view, can_edit } } }
            const permLookup = {};
            data.permissions.forEach(p => {
                if (!permLookup[p.workplace_id]) permLookup[p.workplace_id] = {};
                permLookup[p.workplace_id][p.section] = p;
            });

            // Build thead: row1 = section col + workplace names (colspan 2), row2 = Zobrazit/Editovat per workplace
            const thead = document.querySelector('#permissionsTable thead');
            thead.innerHTML = '';

            const row1 = document.createElement('tr');
            const thSection = document.createElement('th');
            thSection.rowSpan = 2;
            thSection.style.verticalAlign = 'middle';
            thSection.textContent = 'Sekce';
            row1.appendChild(thSection);
            workplaces.forEach(wp => {
                const th = document.createElement('th');
                th.colSpan = 2;
                th.style.textAlign = 'center';
                th.textContent = wp.name;
                row1.appendChild(th);
            });
            thead.appendChild(row1);

            const row2 = document.createElement('tr');
            workplaces.forEach(() => {
                ['Zobrazit', 'Editovat'].forEach(label => {
                    const th = document.createElement('th');
                    th.style.textAlign = 'center';
                    th.style.fontWeight = 'normal';
                    th.style.fontSize = '0.78rem';
                    th.textContent = label;
                    row2.appendChild(th);
                });
            });
            thead.appendChild(row2);

            // Build tbody: one row per section
            const tbody = document.getElementById('permissionsTableBody');
            tbody.innerHTML = '';

            SECTIONS.forEach(section => {
                const row = document.createElement('tr');
                const td = document.createElement('td');
                td.textContent = section.label;
                row.appendChild(td);

                workplaces.forEach(wp => {
                    const perm = (permLookup[wp.id] || {})[section.key] || {};
                    ['view', 'edit'].forEach(type => {
                        const cell = document.createElement('td');
                        cell.style.textAlign = 'center';
                        const cb = document.createElement('input');
                        cb.type = 'checkbox';
                        cb.setAttribute('data-workplace-id', wp.id);
                        cb.setAttribute('data-section', section.key);
                        cb.setAttribute('data-permission', type);
                        cb.checked = type === 'view' ? !!parseInt(perm.can_view) : !!parseInt(perm.can_edit);
                        cb.addEventListener('change', function () {
                            const wid = this.getAttribute('data-workplace-id');
                            const sec = this.getAttribute('data-section');
                            const ptype = this.getAttribute('data-permission');
                            if (ptype === 'edit' && this.checked) {
                                const v = document.querySelector(`input[data-workplace-id="${wid}"][data-section="${sec}"][data-permission="view"]`);
                                if (v) v.checked = true;
                            }
                            if (ptype === 'view' && !this.checked) {
                                const e = document.querySelector(`input[data-workplace-id="${wid}"][data-section="${sec}"][data-permission="edit"]`);
                                if (e) e.checked = false;
                            }
                        });
                        cell.appendChild(cb);
                        row.appendChild(cell);
                    });
                });

                tbody.appendChild(row);
            });

            document.getElementById('permissionsModal').style.display = 'block';
        })
        .catch(error => {
            alert('Chyba při komunikaci se serverem: ' + error.message);
            console.error('Error:', error);
        });
}

function closePermissionsModal() {
    document.getElementById('permissionsModal').style.display = 'none';
}

function savePermissions() {
    const grid = {};
    document.querySelectorAll('#permissionsTableBody input[type="checkbox"]').forEach(cb => {
        const wid  = cb.getAttribute('data-workplace-id');
        const sec  = cb.getAttribute('data-section');
        const type = cb.getAttribute('data-permission');
        const key  = `${wid}__${sec}`;
        if (!grid[key]) grid[key] = { workplace_id: wid, section: sec, can_view: false, can_edit: false };
        if (type === 'view') grid[key].can_view = cb.checked;
        if (type === 'edit') grid[key].can_edit = cb.checked;
    });

    const permissions = Object.values(grid);

    fetch(`/admin/users/${currentPermissionsUserId}/permissions`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ permissions })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closePermissionsModal();
            alert('Oprávnění byla úspěšně uložena');
        } else {
            alert('Chyba při ukládání oprávnění: ' + (data.error || 'Neznámá chyba'));
        }
    })
    .catch(error => {
        alert('Chyba při komunikaci se serverem: ' + error.message);
        console.error('Error:', error);
    });
}

function htmlEscape(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Resend password setup email
function resendPasswordSetup(userId) {
    if (!confirm('Opravdu chcete odeslat e-mail s odkazem pro nastavení hesla?')) {
        return;
    }

    fetch(`/admin/users/${userId}/resend-password`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'E-mail byl úspěšně odeslán');
        } else {
            alert('Chyba: ' + (data.error || 'Neznámá chyba'));
        }
    })
    .catch(error => {
        alert('Chyba při komunikaci se serverem: ' + error.message);
        console.error('Error:', error);
    });
}

// Delete user
function deleteUser(userId, username) {
    if (!confirm(`Opravdu chcete smazat uživatele "${username}"?\n\nTato akce je nevratná a vymaže:\n- Uživatelský účet\n- Všechna oprávnění\n- Všechny související záznamy`)) {
        return;
    }

    // Double confirmation for safety
    if (!confirm(`POZOR: Poslední potvrzení!\n\nOpravdu smazat uživatele "${username}"?`)) {
        return;
    }

    fetch(`/admin/users/${userId}/delete`, {
        method: 'POST'
    })
    .then(response => {
        if (!response.ok && response.status !== 400 && response.status !== 403 && response.status !== 404) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message || 'Uživatel byl úspěšně smazán');
            location.reload();
        } else {
            alert('Chyba: ' + (data.error || 'Neznámá chyba'));
        }
    })
    .catch(error => {
        alert('Chyba při komunikaci se serverem: ' + error.message);
        console.error('Error:', error);
    });
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    const userModal = document.getElementById('userModal');
    const permissionsModal = document.getElementById('permissionsModal');
    const keeperModal = document.getElementById('keeperModal');

    if (event.target === userModal) {
        closeUserModal();
    }
    if (event.target === permissionsModal) {
        closePermissionsModal();
    }
    if (event.target === keeperModal) {
        closeKeeperModal();
    }
});

// Tab switching function
function switchTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });

    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.classList.remove('active');
    });

    // Show selected tab content
    document.getElementById(tabName + '-content').classList.add('active');

    // Mark selected tab as active
    event.target.classList.add('active');
}

// Keeper Assignment Functions
let currentAnimalId = null;

function assignKeeper(animalId, animalName) {
    currentAnimalId = animalId;
    document.getElementById('keeperAnimalName').textContent = animalName;
    document.getElementById('keeper_animal_id').value = animalId;

    // Fetch current assignment
    fetch(`/admin/animals/${animalId}/keeper`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('assigned_user').value = data.assigned_user || '';
                document.getElementById('keeperModal').style.display = 'block';
            } else {
                alert('Chyba při načítání přiřazení: ' + (data.error || 'Neznámá chyba'));
            }
        })
        .catch(error => {
            alert('Chyba při komunikaci se serverem: ' + error.message);
            console.error('Error:', error);
        });
}

function closeKeeperModal() {
    document.getElementById('keeperModal').style.display = 'none';
    document.getElementById('keeperForm').reset();
}

// Handle keeper assignment form submission
document.getElementById('keeperForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const animalId = formData.get('animal_id');

    fetch(`/admin/animals/${animalId}/keeper`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeKeeperModal();
            location.reload();
        } else {
            alert('Chyba: ' + (data.error || 'Neznámá chyba'));
        }
    })
    .catch(error => {
        alert('Chyba při komunikaci se serverem: ' + error.message);
        console.error('Error:', error);
    });
});

// Animals table filtering
document.addEventListener('DOMContentLoaded', function() {
    const workplaceFilter = document.getElementById('workplaceFilter');
    const searchAnimalInput = document.getElementById('searchAnimalInput');
    const animalsTableBody = document.getElementById('animalsTableBody');

    if (workplaceFilter && searchAnimalInput && animalsTableBody) {
        function filterAnimalsTable() {
            const workplaceId = workplaceFilter.value;
            const searchTerm = searchAnimalInput.value.toLowerCase();

            const rows = animalsTableBody.querySelectorAll('tr');
            rows.forEach(row => {
                const rowWorkplaceId = row.getAttribute('data-workplace-id');
                const searchData = row.getAttribute('data-search');

                let show = true;

                // Filter by workplace
                if (workplaceId && rowWorkplaceId !== workplaceId) {
                    show = false;
                }

                // Filter by search term
                if (searchTerm && !searchData.includes(searchTerm)) {
                    show = false;
                }

                row.style.display = show ? '' : 'none';
            });
        }

        workplaceFilter.addEventListener('change', filterAnimalsTable);
        searchAnimalInput.addEventListener('input', filterAnimalsTable);
    }
});
</script>
