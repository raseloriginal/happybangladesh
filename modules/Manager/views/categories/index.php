<?php $pageTitle = 'Categories'; ?>
<div class="page-header">
    <div><h1 class="page-title">Categories</h1><div class="breadcrumb">Manager &rsaquo; Categories</div></div>
    <button onclick="openModal('add-modal')" class="btn btn-primary flex items-center gap-2">
        <i class="fas fa-plus"></i> Add Category
    </button>
</div>

<div class="card">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="py-4 px-6 text-left w-16">#</th>
                    <th class="py-4 px-6 text-left">Category Name</th>
                    <th class="py-4 px-6 text-left">Company</th>
                    <th class="py-4 px-6 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grouped = [];
                foreach ($items as $c) {
                    $mcName = $c['main_category_name'] ?: 'No Main Category';
                    $grouped[$mcName][] = $c;
                }
                $counter = 1;
                foreach ($grouped as $mcName => $cats): 
                ?>
                    <tr class="bg-gray-50 border-b">
                        <td colspan="4" class="py-3 px-6 text-sm font-semibold text-gray-700 bg-gray-100">
                            <span class="inline-flex items-center gap-1.5">
                                <i class="fas fa-folder text-indigo-500 text-xs"></i>
                                <?= htmlspecialchars($mcName) ?>
                            </span>
                        </td>
                    </tr>
                    <?php foreach ($cats as $c): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-6 text-sm text-gray-400"><?= $counter++ ?></td>
                            <td class="py-4 px-6 text-sm font-medium text-gray-800 pl-10">
                                <span class="text-gray-400 mr-1">└─</span> <?= htmlspecialchars($c['name']) ?>
                            </td>
                            <td class="py-4 px-6 text-sm">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?= $c['company_name'] ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-700' ?>">
                                    <?= htmlspecialchars($c['company_name'] ?? 'General') ?>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-sm flex justify-end gap-2">
                                <button onclick='editCat(<?= json_encode($c) ?>)' class="text-indigo-600 hover:bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors text-sm font-medium">Edit</button>
                                <button onclick="deleteCat(<?= $c['id'] ?>)" class="text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-colors text-sm font-medium">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="4" class="py-12 text-center text-gray-500">
                            <i class="fas fa-folder-open text-4xl text-gray-300 mb-3 block"></i>
                            No categories found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Add Modal -->
<div id="add-modal" class="modal-overlay hidden">
    <div class="modal-box p-6" style="max-width: 500px;">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-gray-900">Bulk Add Categories</h3>
            <button onclick="closeModal('add-modal')" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="add-form" class="space-y-4">
            <input type="hidden" id="csrf" value="<?= Helpers::csrfToken() ?>">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Company (Optional)</label>
                <select id="add-company" class="form-input text-sm w-full">
                    <option value="">General / All Companies</option>
                    <?php foreach ($companies as $comp): ?>
                        <option value="<?= $comp['id'] ?>"><?= htmlspecialchars($comp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Main Category (Optional)</label>
                <select id="add-main-category" class="form-input text-sm w-full" onchange="toggleMainCategory('add')">
                    <option value="">No Main Category</option>
                    <option value="new">+ Add New Main Category</option>
                    <?php foreach ($main_categories as $mc): ?>
                        <option value="<?= $mc['id'] ?>"><?= htmlspecialchars($mc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="add-new-main-cat-group" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">New Main Category Name <span class="text-red-500">*</span></label>
                <input type="text" id="add-new-main-category-name" class="form-input text-sm w-full" placeholder="e.g. Beverages">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category Names <span class="text-red-500">*</span> <span class="text-xs text-gray-400 font-normal">(One per line)</span></label>
                <textarea id="add-names" class="form-input text-sm w-full h-32 resize-none" placeholder="Soap&#10;Shampoo&#10;Detergent" required></textarea>
            </div>
            <div class="flex gap-3 pt-4 border-t mt-4">
                <button type="button" onclick="closeModal('add-modal')" class="btn btn-secondary flex-1">Cancel</button>
                <button type="submit" class="btn btn-primary flex-1">Save Categories</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="modal-overlay hidden">
    <div class="modal-box p-6" style="max-width: 500px;">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-gray-900">Edit Category</h3>
            <button onclick="closeModal('edit-modal')" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="edit-form" class="space-y-4">
            <input type="hidden" id="edit-id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Company (Optional)</label>
                <select id="edit-company" class="form-input text-sm w-full">
                    <option value="">General / All Companies</option>
                    <?php foreach ($companies as $comp): ?>
                        <option value="<?= $comp['id'] ?>"><?= htmlspecialchars($comp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Main Category (Optional)</label>
                <select id="edit-main-category" class="form-input text-sm w-full" onchange="toggleMainCategory('edit')">
                    <option value="">No Main Category</option>
                    <option value="new">+ Add New Main Category</option>
                    <?php foreach ($main_categories as $mc): ?>
                        <option value="<?= $mc['id'] ?>"><?= htmlspecialchars($mc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="edit-new-main-cat-group" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">New Main Category Name <span class="text-red-500">*</span></label>
                <input type="text" id="edit-new-main-category-name" class="form-input text-sm w-full" placeholder="e.g. Beverages">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                <input type="text" id="edit-name" class="form-input text-sm w-full" required>
            </div>
            <div class="flex gap-3 pt-4 border-t mt-4">
                <button type="button" onclick="closeModal('edit-modal')" class="btn btn-secondary flex-1">Cancel</button>
                <button type="submit" class="btn btn-primary flex-1">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function toggleMainCategory(type) {
    const select = document.getElementById(type + '-main-category');
    const group = document.getElementById(type + '-new-main-cat-group');
    const input = document.getElementById(type + '-new-main-category-name');
    if (select.value === 'new') {
        group.classList.remove('hidden');
        input.required = true;
    } else {
        group.classList.add('hidden');
        input.required = false;
        input.value = '';
    }
}

document.getElementById('add-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const names = document.getElementById('add-names').value.split('\n').map(s => s.trim()).filter(s => s);
    if (!names.length) return;

    try {
        const res = await fetch('<?= BASE_URL ?>/manager/api/categories', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: document.getElementById('csrf').value,
                company_id: document.getElementById('add-company').value,
                main_category_id: document.getElementById('add-main-category').value,
                new_main_category_name: document.getElementById('add-new-main-category-name').value,
                names: names
            })
        });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Error saving categories');
    } catch(err) { alert('Request failed'); }
});

function editCat(c) {
    document.getElementById('edit-id').value = c.id;
    document.getElementById('edit-company').value = c.company_id || '';
    document.getElementById('edit-main-category').value = c.main_category_id || '';
    toggleMainCategory('edit');
    document.getElementById('edit-name').value = c.name;
    openModal('edit-modal');
}

document.getElementById('edit-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    try {
        const res = await fetch('<?= BASE_URL ?>/manager/api/categories/update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: document.getElementById('csrf').value,
                id: document.getElementById('edit-id').value,
                company_id: document.getElementById('edit-company').value,
                main_category_id: document.getElementById('edit-main-category').value,
                new_main_category_name: document.getElementById('edit-new-main-category-name').value,
                name: document.getElementById('edit-name').value
            })
        });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Error updating category');
    } catch(err) { alert('Request failed'); }
});

async function deleteCat(id) {
    if (!confirm('Delete this category?')) return;
    try {
        const res = await fetch('<?= BASE_URL ?>/manager/api/categories/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: document.getElementById('csrf').value,
                id: id
            })
        });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Error deleting category');
    } catch(err) { alert('Request failed'); }
}
</script>
