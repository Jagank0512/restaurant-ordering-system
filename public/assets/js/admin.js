const MOCK_MODE = window.location.protocol === 'file:';

function checkAuth() {
    const token = localStorage.getItem('admin_token');
    if (!token && !window.location.href.includes('login.html')) {
        window.location.href = 'login.html';
    }
}

async function login() {
    const u = document.getElementById('username').value;
    const p = document.getElementById('password').value;

    if (MOCK_MODE) {
        if (u === 'admin' && p === 'admin') {
            localStorage.setItem('admin_token', 'demo');
            window.location.href = 'index.html';
        } else {
            alert('Invalid (Try admin/admin)');
        }
        return;
    }

    try {
        const res = await fetch('../api/login.php', {
            method: 'POST',
            body: JSON.stringify({ username: u, password: p })
        });
        const json = await res.json();
        if (json.status === 'success') {
            localStorage.setItem('admin_token', json.token);
            window.location.href = 'index.php'; // In prod use php
        } else {
            alert(json.message);
        }
    } catch (e) {
        alert('Error logging in');
    }
}

async function loadOrders() {
    if (MOCK_MODE) {
        renderOrders([
            { id: 1, order_number: 'ORD-DEMO-1', customer_name: 'John', total_amount: 500, order_status: 'Received', items_summary: 'Burger (x2), Coke (x1)' },
            { id: 2, order_number: 'ORD-DEMO-2', customer_name: 'Jane', total_amount: 850, order_status: 'Preparing', items_summary: 'Pizza (x1), Pasta (x1)' }
        ]);
        return;
    }

    const res = await fetch('../api/admin_orders.php');
    const json = await res.json();
    if (json.status === 'success') {
        renderOrders(json.data);
    }
}

function renderOrders(orders) {
    const container = document.getElementById('orders-list');
    container.innerHTML = orders.map(o => `
        <div class="menu-item" style="display:block;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <strong>${o.order_number} (${o.customer_name})</strong>
                <span class="admin-badge badge-${o.order_status.toLowerCase()}">${o.order_status}</span>
            </div>
            <div style="font-size:0.9rem; color:var(--text-muted); margin-bottom:12px;">
                ${o.items_summary}
            </div>
            <div class="status-grid">
                ${['Received', 'Preparing', 'Ready', 'Served'].map(s => `
                    <button class="status-btn ${o.order_status === s ? 'active' : ''}" 
                        onclick="updateStatus(${o.id || o.order_number}, '${s}')">${s}</button>
                `).join('')}
            </div>
        </div>
    `).join('');
}

async function updateStatus(id, status) {
    if (MOCK_MODE) {
        alert(`Status updated to ${status} (Mock)`);
        loadOrders();
        return;
    }

    await fetch('../api/admin_orders.php', {
        method: 'POST',
        body: JSON.stringify({ order_id: id, status: status })
    });
    loadOrders();
}
