
let currentToken = localStorage.getItem('token');
let currentUser = null;

// Set axios default header
if (currentToken) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${currentToken}`;
}

// Login function
window.login = async function () {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorDiv = document.getElementById('login-error');

    try {
        const response = await axios.post('/api/login', {
            email,
            password
        });
        const data = response.data;

        if (data.status === 'success') {
            currentToken = data.token;
            currentUser = data.user;
            localStorage.setItem('token', currentToken);
            axios.defaults.headers.common['Authorization'] = `Bearer ${currentToken}`;
            showMainApp();
        }
    } catch (error) {
        errorDiv.classList.remove('hidden');
        errorDiv.innerText = error.response?.data?.message || 'Login gagal';
    }
};

// Logout function
window.logout = async function () {
    try {
        await axios.post('/api/logout');
    } catch (e) { }
    localStorage.removeItem('token');
    delete axios.defaults.headers.common['Authorization'];
    currentToken = null;
    currentUser = null;
    showLoginForm();
};

// Show main app after login
function showMainApp() {
    document.getElementById('login-container').classList.add('hidden');
    document.getElementById('main-container').classList.remove('hidden');
    document.getElementById('user-name').innerText = currentUser.name;

    // Build sidebar based on role
    buildSidebar();
    // Default page
    loadPage('dashboard');
}

// Show login form
function showLoginForm() {
    document.getElementById('login-container').classList.remove('hidden');
    document.getElementById('main-container').classList.add('hidden');
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
    document.getElementById('login-error').classList.add('hidden');
}

// Build sidebar menu based on user role
function buildSidebar() {
    const sidebar = document.getElementById('sidebar-menu');
    const role = currentUser.role;
    let menuHtml = '<ul class="space-y-2">';

    // Dashboard (semua role)
    menuHtml +=
        `<li><button onclick="loadPage('dashboard')" class="w-full text-left px-4 py-2 rounded hover:bg-gray-100">🏠 Dashboard</button></li>`;

    if (role === 'kasir') {
        menuHtml += `
                    <li><button onclick="loadPage('kasir/transaksi')" class="w-full text-left px-4 py-2 rounded hover:bg-gray-100">💰 Transaksi</button></li>
                    <li><button onclick="loadPage('kasir/riwayat')" class="w-full text-left px-4 py-2 rounded hover:bg-gray-100">📋 Riwayat Transaksi</button></li>
                `;
    } else if (role === 'manajer') {
        menuHtml += `
                    <li><button onclick="loadPage('manajer/menu')" class="w-full text-left px-4 py-2 rounded hover:bg-gray-100">🍽️ Kelola Menu</button></li>
                    <li><button onclick="loadPage('manajer/laporan')" class="w-full text-left px-4 py-2 rounded hover:bg-gray-100">📊 Laporan</button></li>
                    <li><button onclick="loadPage('manajer/logs')" class="w-full text-left px-4 py-2 rounded hover:bg-gray-100">📜 Log Aktivitas</button></li>
                `;
    } else if (role === 'admin') {
        menuHtml += `
                    <li><button onclick="loadPage('admin/users')" class="w-full text-left px-4 py-2 rounded hover:bg-gray-100">👥 Kelola User</button></li>
                    <li><button onclick="loadPage('admin/logs')" class="w-full text-left px-4 py-2 rounded hover:bg-gray-100">📜 Log Aktivitas</button></li>
                `;
    }

    menuHtml += '</ul>';
    sidebar.innerHTML = menuHtml;
}

// Load page via API
window.loadPage = async function (page) {
    const contentDiv = document.getElementById('page-content');
    const loading = document.getElementById('loading');
    loading.classList.remove('hidden');

    try {
        let html = '<div class="text-center py-8">Konten sedang dimuat...</div>';

        if (page === 'dashboard') {
            html = `<h2 class="text-2xl font-bold mb-4">Dashboard</h2>
                            <p>Selamat datang, ${currentUser.name}!</p>
                            <p class="mt-4">Role: ${currentUser.role}</p>`;
        } else if (page === 'kasir/transaksi') {
            html = await loadKasirTransaksiPage();
        } else if (page === 'kasir/riwayat') {
            html = await loadKasirRiwayatPage();
        } else if (page === 'manajer/menu') {
            html = await loadManajerMenuPage();
        } else if (page === 'manajer/laporan') {
            html = await loadManajerLaporanPage();
        } else if (page === 'manajer/logs') {
            html = await loadLogsPage('/api/manajer/activity-logs');
        } else if (page === 'admin/users') {
            html = await loadAdminUsersPage();
        } else if (page === 'admin/logs') {
            html = await loadLogsPage('/api/admin/activity-logs');
        }

        contentDiv.innerHTML = html;
    } catch (error) {
        contentDiv.innerHTML =
            `<div class="bg-red-100 text-red-700 p-4 rounded">Error: ${error.message}</div>`;
    } finally {
        loading.classList.add('hidden');
    }
};

// ============ KASIR PAGES ============
async function loadKasirTransaksiPage() {
    const [menuRes, tablesRes] = await Promise.all([
        axios.get('/api/kasir/menu'),
        axios.get('/api/kasir/tables')
    ]);
    const menus = menuRes.data.data;
    const tables = tablesRes.data.data;

    let html = `<h2 class="text-2xl font-bold mb-4">Transaksi Kasir</h2>
                <div class="grid grid-cols-3 gap-6">
                    <div class="col-span-2">
                        <h3 class="font-bold mb-2">Menu</h3>
                        <div class="grid grid-cols-3 gap-2" id="menu-list">`;

    menus.forEach(menu => {
        html += `<button onclick="addToCart(${menu.id}, '${menu.name}', ${menu.price})" 
                            class="bg-gray-100 hover:bg-gray-200 p-3 rounded text-left">
                            <div class="font-semibold">${menu.name}</div>
                            <div class="text-sm text-gray-600">Rp ${menu.price.toLocaleString()}</div>
                        </button>`;
    });

    html += `</div></div>
                    <div class="border-l pl-6">
                        <h3 class="font-bold mb-2">Keranjang</h3>
                        <div id="cart-items" class="mb-4"></div>
                        <div class="font-bold">Total: Rp <span id="cart-total">0</span></div>
                        <div class="mt-4">
                            <label class="block mb-1">Pilih Meja</label>
                            <select id="table-id" class="w-full border rounded p-2 mb-4">`;

    tables.forEach(table => {
        html += `<option value="${table.id}">${table.table_number}</option>`;
    });

    html += `</select>
                            <label class="block mb-1">Uang Bayar</label>
                            <input type="number" id="cash-amount" class="w-full border rounded p-2 mb-4" placeholder="Rp 0">
                            <button onclick="processTransaction()" class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600">Bayar</button>
                        </div>
                    </div>
                </div>
                <div id="transaction-result" class="mt-4"></div>`;

    return html;
}

async function loadKasirRiwayatPage() {
    const response = await axios.get('/api/kasir/transactions');
    const transactions = response.data.data;

    let html = `<h2 class="text-2xl font-bold mb-4">Riwayat Transaksi</div>
                <table class="w-full border-collapse">
                    <thead>
                        <tr><th class="border p-2">Invoice</th><th class="border p-2">Meja</th><th class="border p-2">Total</th><th class="border p-2">Waktu</th></tr>
                    </thead>
                    <tbody>`;

    transactions.forEach(t => {
        html += `<tr>
                            <td class="border p-2">${t.invoice_number}</td>
                            <td class="border p-2">${t.table?.table_number || '-'}</td>
                            <td class="border p-2">Rp ${t.total_amount.toLocaleString()}</td>
                            <td class="border p-2">${new Date(t.payment_time).toLocaleString()}</td>
                         </tr>`;
    });

    html += `</tbody></table>`;
    return html;
}

// ============ MANAJER PAGES ============
async function loadManajerMenuPage() {
    const response = await axios.get('/api/manajer/menu');
    const menus = response.data.data;

    let html = `<h2 class="text-2xl font-bold mb-4">Kelola Menu</div>
                <button onclick="showAddMenuForm()" class="bg-green-500 text-white px-4 py-2 rounded mb-4">+ Tambah Menu</button>
                <div id="menu-form-container"></div>
                <table class="w-full border-collapse">
                    <thead>
                        <tr><th class="border p-2">ID</th><th class="border p-2">Nama</th><th class="border p-2">Harga</th><th class="border p-2">Kategori</th><th class="border p-2">Stok</th><th class="border p-2">Aksi</th></tr>
                    </thead>
                    <tbody>`;

    menus.forEach(menu => {
        html += `<tr>
                            <td class="border p-2">${menu.id}</td>
                            <td class="border p-2">${menu.name}</td>
                            <td class="border p-2">Rp ${menu.price.toLocaleString()}</td>
                            <td class="border p-2">${menu.category || '-'}</td>
                            <td class="border p-2">${menu.stock}</td>
                            <td class="border p-2">
                                <button onclick="editMenu(${menu.id})" class="bg-blue-500 text-white px-2 py-1 rounded">Edit</button>
                                <button onclick="deleteMenu(${menu.id})" class="bg-red-500 text-white px-2 py-1 rounded">Hapus</button>
                            </td>
                         </tr>`;
    });

    html += `</tbody></table>`;
    return html;
}

async function loadManajerLaporanPage() {
    const kasirRes = await axios.get('/api/manajer/kasir-list');
    const kasirs = kasirRes.data.data;

    let html = `<h2 class="text-2xl font-bold mb-4">Laporan</div>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="border rounded p-4">
                        <h3 class="font-bold mb-2">Laporan Harian</h3>
                        <input type="date" id="report-date" class="border rounded p-2 w-full mb-2">
                        <button onclick="loadDailyReport()" class="bg-blue-500 text-white px-4 py-2 rounded">Tampilkan</button>
                        <div id="daily-report-result" class="mt-4"></div>
                    </div>
                    <div class="border rounded p-4">
                        <h3 class="font-bold mb-2">Laporan Bulanan</h3>
                        <input type="month" id="report-month" class="border rounded p-2 w-full mb-2">
                        <button onclick="loadMonthlyReport()" class="bg-blue-500 text-white px-4 py-2 rounded">Tampilkan</button>
                        <div id="monthly-report-result" class="mt-4"></div>
                    </div>
                </div>
                <div class="border rounded p-4">
                    <h3 class="font-bold mb-2">Filter Transaksi</h3>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <select id="filter-kasir" class="border rounded p-2">
                            <option value="">Semua Kasir</option>
                            ${kasirs.map(k => `<option value="${k.id}">${k.name}</option>`).join('')}
                        </select>
                        <input type="date" id="filter-date" class="border rounded p-2">
                    </div>
                    <button onclick="filterTransactions()" class="bg-blue-500 text-white px-4 py-2 rounded">Filter</button>
                    <div id="transactions-result" class="mt-4"></div>
                </div>`;

    return html;
}

// ============ ADMIN PAGES ============
async function loadAdminUsersPage() {
    const response = await axios.get('/api/admin/users');
    const users = response.data.data;

    let html = `<h2 class="text-2xl font-bold mb-4">Kelola User</div>
                <button onclick="showAddUserForm()" class="bg-green-500 text-white px-4 py-2 rounded mb-4">+ Tambah User</button>
                <div id="user-form-container"></div>
                <table class="w-full border-collapse">
                    <thead>
                        <tr><th class="border p-2">ID</th><th class="border p-2">Nama</th><th class="border p-2">Email</th><th class="border p-2">Role</th><th class="border p-2">Aksi</th></tr>
                    </thead>
                    <tbody>`;

    users.forEach(user => {
        html += `<tr>
                            <td class="border p-2">${user.id}</td>
                            <td class="border p-2">${user.name}</td>
                            <td class="border p-2">${user.email}</td>
                            <td class="border p-2">${user.role}</td>
                            <td class="border p-2">
                                <button onclick="editUserRole(${user.id}, '${user.role}')" class="bg-blue-500 text-white px-2 py-1 rounded">Edit Role</button>
                                <button onclick="deleteUser(${user.id})" class="bg-red-500 text-white px-2 py-1 rounded">Hapus</button>
                            </td>
                         </tr>`;
    });

    html += `</tbody></table>`;
    return html;
}

async function loadLogsPage(apiUrl) {
    const response = await axios.get(apiUrl);
    const logs = response.data.data;

    let html = `<h2 class="text-2xl font-bold mb-4">Log Aktivitas</div>
                <table class="w-full border-collapse">
                    <thead>
                        <tr><th class="border p-2">User</th><th class="border p-2">Aksi</th><th class="border p-2">Deskripsi</th><th class="border p-2">Waktu</th></tr>
                    </thead>
                    <tbody>`;

    logs.forEach(log => {
        html += `<tr>
                            <td class="border p-2">${log.user?.name || '-'}</td>
                            <td class="border p-2">${log.action}</td>
                            <td class="border p-2">${log.description || '-'}</td>
                            <td class="border p-2">${new Date(log.created_at).toLocaleString()}</td>
                         </tr>`;
    });

    html += `</tbody></table>`;
    return html;
}

// Check if already logged in
(async function checkAuth() {
    if (currentToken) {
        try {
            const response = await axios.get('/api/me');
            currentUser = response.data.user;
            showMainApp();
        } catch (e) {
            showLoginForm();
        }
    }
})();

// Cart array
let cart = [];
let isProcessing = false;

window.addToCart = function (menuId, menuName, price) {
    const existing = cart.find(item => item.menu_id === menuId);
    if (existing) {
        existing.quantity++;
        existing.subtotal = existing.quantity * existing.price;
    } else {
        cart.push({
            menu_id: menuId,
            name: menuName,
            price: price,
            quantity: 1,
            subtotal: price
        });
    }
    renderCart();
};

function renderCart() {
    const cartDiv = document.getElementById('cart-items');
    const totalSpan = document.getElementById('cart-total');

    if (!cartDiv) return;

    if (cart.length === 0) {
        cartDiv.innerHTML = '<p class="text-gray-500">Keranjang kosong</p>';
        if (totalSpan) totalSpan.innerText = '0';
        return;
    }

    let html = '<div class="space-y-2">';
    let total = 0;

    cart.forEach((item, index) => {
        total += item.subtotal;
        html += `
            <div class="flex justify-between items-center border-b pb-2">
                <div class="flex-1">
                    <div class="font-semibold">${item.name}</div>
                    <div class="text-sm text-gray-600">${item.quantity} x Rp ${item.price.toLocaleString()}</div>
                </div>
                <div class="text-right">
                    <div>Rp ${item.subtotal.toLocaleString()}</div>
                    <button onclick="removeFromCart(${index})" class="text-red-500 text-sm">Hapus</button>
                </div>
            </div>
        `;
    });

    html += '</div>';
    cartDiv.innerHTML = html;
    if (totalSpan) totalSpan.innerText = total.toLocaleString();
}

window.removeFromCart = function (index) {
    cart.splice(index, 1);
    renderCart();
};

window.processTransaction = async function () {
    if (isProcessing) return;
    if (cart.length === 0) {
        alert('Keranjang kosong!');
        return;
    }

    const tableId = document.getElementById('table-id')?.value;
    const cashAmount = parseInt(document.getElementById('cash-amount')?.value);

    if (!tableId) {
        alert('Pilih meja!');
        return;
    }
    if (!cashAmount || cashAmount <= 0) {
        alert('Masukkan uang bayar!');
        return;
    }

    const items = cart.map(item => ({
        menu_id: item.menu_id,
        quantity: item.quantity
    }));

    isProcessing = true;
    const loading = document.getElementById('loading');
    loading.classList.remove('hidden');

    try {
        const response = await axios.post('/api/kasir/transaction', {
            table_id: parseInt(tableId),
            cash_amount: cashAmount,
            items: items
        });

        const result = response.data;

        if (result.status === 'success') {
            const change = result.data.change_amount;
            const resultDiv = document.getElementById('transaction-result');
            resultDiv.innerHTML = `
                <div class="bg-green-100 border border-green-400 text-green-700 p-4 rounded mt-4">
                    <h3 class="font-bold">✅ Transaksi Berhasil!</h3>
                    <p>Kembalian: Rp ${change.toLocaleString()}</p>
                    <button onclick="window.print()" class="mt-2 bg-gray-500 text-white px-3 py-1 rounded">Cetak Struk</button>
                </div>
            `;
            // Reset cart
            cart = [];
            renderCart();
            document.getElementById('cash-amount').value = '';

            // Reload riwayat if on riwayat page
            const contentDiv = document.getElementById('page-content');
            if (contentDiv.innerHTML.includes('Riwayat Transaksi')) {
                loadPage('kasir/riwayat');
            }
        } else {
            alert(result.message);
        }
    } catch (error) {
        const msg = error.response?.data?.message || 'Transaksi gagal';
        alert(msg);
    } finally {
        isProcessing = false;
        loading.classList.add('hidden');
    }
};

window.showAddMenuForm = function () {
    const container = document.getElementById('menu-form-container');
    container.innerHTML = `
        <div class="border rounded p-4 mb-4 bg-gray-50">
            <h3 class="font-bold mb-2">Tambah Menu Baru</h3>
            <input type="text" id="new-menu-name" placeholder="Nama Menu" class="border rounded p-2 w-full mb-2">
            <input type="number" id="new-menu-price" placeholder="Harga" class="border rounded p-2 w-full mb-2">
            <input type="text" id="new-menu-category" placeholder="Kategori" class="border rounded p-2 w-full mb-2">
            <input type="number" id="new-menu-stock" placeholder="Stok" class="border rounded p-2 w-full mb-2">
            <button onclick="submitAddMenu()" class="bg-green-500 text-white px-4 py-2 rounded">Simpan</button>
            <button onclick="document.getElementById('menu-form-container').innerHTML=''" class="bg-gray-500 text-white px-4 py-2 rounded">Batal</button>
        </div>
    `;
};

window.submitAddMenu = async function () {
    const name = document.getElementById('new-menu-name')?.value;
    const price = parseFloat(document.getElementById('new-menu-price')?.value);
    const category = document.getElementById('new-menu-category')?.value;
    const stock = parseInt(document.getElementById('new-menu-stock')?.value) || 0;

    if (!name || !price) {
        alert('Nama dan harga wajib diisi!');
        return;
    }

    try {
        await axios.post('/api/manajer/menu', {
            name,
            price,
            category,
            stock
        });
        alert('Menu berhasil ditambahkan');
        document.getElementById('menu-form-container').innerHTML = '';
        loadPage('manajer/menu');
    } catch (error) {
        alert('Gagal menambah menu');
    }
};

window.editMenu = async function (id) {
    const response = await axios.get('/api/manajer/menu');
    const menu = response.data.data.find(m => m.id === id);
    if (!menu) return;

    const newName = prompt('Nama baru:', menu.name);
    if (newName) {
        const newPrice = prompt('Harga baru:', menu.price);
        try {
            await axios.put(`/api/manajer/menu/${id}`, {
                name: newName,
                price: parseFloat(newPrice) || menu.price
            });
            alert('Menu berhasil diupdate');
            loadPage('manajer/menu');
        } catch (error) {
            alert('Gagal update menu');
        }
    }
};

window.deleteMenu = async function (id) {
    if (confirm('Yakin hapus menu ini?')) {
        try {
            await axios.delete(`/api/manajer/menu/${id}`);
            alert('Menu berhasil dihapus');
            loadPage('manajer/menu');
        } catch (error) {
            alert('Gagal hapus menu');
        }
    }
};

window.loadDailyReport = async function () {
    const date = document.getElementById('report-date')?.value;
    if (!date) {
        alert('Pilih tanggal!');
        return;
    }
    try {
        const response = await axios.get(`/api/manajer/report/daily?tanggal=${date}`);
        const data = response.data.data;
        const resultDiv = document.getElementById('daily-report-result');
        resultDiv.innerHTML = `
            <div class="bg-blue-50 p-3 rounded">
                <p><strong>Tanggal:</strong> ${data.tanggal}</p>
                <p><strong>Total Transaksi:</strong> ${data.total_transaksi}</p>
                <p><strong>Total Pendapatan:</strong> ${data.pendapatan_format}</p>
            </div>
        `;
    } catch (error) {
        alert('Gagal load laporan harian');
    }
};

window.loadMonthlyReport = async function () {
    const month = document.getElementById('report-month')?.value;
    if (!month) {
        alert('Pilih bulan!');
        return;
    }
    try {
        const response = await axios.get(`/api/manajer/report/monthly?bulan=${month}`);
        const data = response.data.data;
        const resultDiv = document.getElementById('monthly-report-result');
        resultDiv.innerHTML = `
            <div class="bg-blue-50 p-3 rounded">
                <p><strong>Bulan:</strong> ${data.bulan}</p>
                <p><strong>Total Transaksi:</strong> ${data.total_transaksi}</p>
                <p><strong>Total Pendapatan:</strong> ${data.pendapatan_format}</p>
            </div>
        `;
    } catch (error) {
        alert('Gagal load laporan bulanan');
    }
};

window.filterTransactions = async function () {
    const kasirId = document.getElementById('filter-kasir')?.value;
    const date = document.getElementById('filter-date')?.value;
    let url = '/api/manajer/transactions?';
    if (kasirId) url += `kasir_id=${kasirId}&`;
    if (date) url += `tanggal=${date}&`;

    try {
        const response = await axios.get(url);
        const transactions = response.data.data;
        const resultDiv = document.getElementById('transactions-result');

        if (transactions.length === 0) {
            resultDiv.innerHTML = '<p class="text-gray-500">Tidak ada transaksi</p>';
            return;
        }

        let html = '<table class="w-full border-collapse mt-4">';
        html +=
            '<thead><tr><th class="border p-2">Invoice</th><th class="border p-2">Kasir</th><th class="border p-2">Total</th><th class="border p-2">Waktu</th></tr></thead><tbody>';

        transactions.forEach(t => {
            html += `<tr>
                        <td class="border p-2">${t.invoice_number}</td>
                        <td class="border p-2">${t.user?.name || '-'}</td>
                        <td class="border p-2">Rp ${t.total_amount.toLocaleString()}</td>
                        <td class="border p-2">${new Date(t.payment_time).toLocaleString()}</td>
                     </tr>`;
        });

        html += '</tbody></table>';
        resultDiv.innerHTML = html;
    } catch (error) {
        alert('Gagal filter transaksi');
    }
};

window.showAddUserForm = function () {
    const container = document.getElementById('user-form-container');
    container.innerHTML = `
        <div class="border rounded p-4 mb-4 bg-gray-50">
            <h3 class="font-bold mb-2">Tambah User Baru</h3>
            <input type="text" id="new-user-name" placeholder="Nama" class="border rounded p-2 w-full mb-2">
            <input type="email" id="new-user-email" placeholder="Email" class="border rounded p-2 w-full mb-2">
            <input type="password" id="new-user-password" placeholder="Password" class="border rounded p-2 w-full mb-2">
            <select id="new-user-role" class="border rounded p-2 w-full mb-2">
                <option value="kasir">Kasir</option>
                <option value="manajer">Manajer</option>
                <option value="admin">Admin</option>
            </select>
            <button onclick="submitAddUser()" class="bg-green-500 text-white px-4 py-2 rounded">Simpan</button>
            <button onclick="document.getElementById('user-form-container').innerHTML=''" class="bg-gray-500 text-white px-4 py-2 rounded">Batal</button>
        </div>
    `;
};

window.submitAddUser = async function () {
    const name = document.getElementById('new-user-name')?.value;
    const email = document.getElementById('new-user-email')?.value;
    const password = document.getElementById('new-user-password')?.value;
    const role = document.getElementById('new-user-role')?.value;

    if (!name || !email || !password) {
        alert('Semua field wajib diisi!');
        return;
    }

    try {
        await axios.post('/api/admin/users', {
            name,
            email,
            password,
            role
        });
        alert('User berhasil ditambahkan');
        document.getElementById('user-form-container').innerHTML = '';
        loadPage('admin/users');
    } catch (error) {
        alert(error.response?.data?.message || 'Gagal menambah user');
    }
};

window.editUserRole = async function (id, currentRole) {
    const newRole = prompt('Role baru (admin/manajer/kasir):', currentRole);
    if (newRole && ['admin', 'manajer', 'kasir'].includes(newRole)) {
        try {
            await axios.put(`/api/admin/users/${id}/role`, {
                role: newRole
            });
            alert('Role berhasil diupdate');
            loadPage('admin/users');
        } catch (error) {
            alert('Gagal update role');
        }
    } else if (newRole) {
        alert('Role tidak valid!');
    }
};

window.deleteUser = async function (id) {
    if (confirm('Yakin hapus user ini?')) {
        try {
            await axios.delete(`/api/admin/users/${id}`);
            alert('User berhasil dihapus');
            loadPage('admin/users');
        } catch (error) {
            alert(error.response?.data?.message || 'Gagal hapus user');
        }
    }
};
