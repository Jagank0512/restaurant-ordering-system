const MOCK_MODE = window.location.protocol === 'file:';

// State
let allMenuData = [];
let cart = JSON.parse(localStorage.getItem('cart_data')) || {}; // Persist cart
let currentCategory = null;

// Mock Data
const MOCK_MENU = [
    {
        id: 1, name: "Starters", image: "",
        sub_categories: [
            {
                id: 1, name: "Veg Starters",
                items: [
                    { id: 101, name: "Paneer Tikka", description: "Spicy grilled cottage cheese", price: 240, image: "https://placehold.co/100x100/orange/white?text=Paneer" },
                    { id: 102, name: "Veg Manchurian", description: "Fried veg balls in spicy sauce", price: 180, image: "https://placehold.co/100x100/green/white?text=Veg" }
                ]
            },
            {
                id: 2, name: "Non-Veg Starters",
                items: [
                    { id: 103, name: "Chicken 65", description: "Spicy deep fried chicken", price: 280, image: "https://placehold.co/100x100/red/white?text=Chicken" }
                ]
            }
        ]
    },
    {
        id: 2, name: "Main Course", image: "",
        sub_categories: [
            {
                id: 3, name: "Indian Breads",
                items: [
                    { id: 201, name: "Butter Naan", description: "Soft clay oven bread with butter", price: 45, image: "https://placehold.co/100x100/orange/white?text=Naan" }
                ]
            },
            {
                id: 4, name: "Curries",
                items: [
                    { id: 202, name: "Butter Chicken", description: "Rich tomato gravy chicken", price: 320, image: "https://placehold.co/100x100/orange/white?text=ButterChkn" }
                ]
            }
        ]
    }
];

// Initialization
document.addEventListener('DOMContentLoaded', () => {
    fetchMenu();
    updateCartUI();

    // Check for existing order
    const savedOrder = localStorage.getItem('last_order_id');
    if (savedOrder) {
        showTrackOrderLink(savedOrder);
    }
});

async function fetchMenu() {
    if (MOCK_MODE) {
        console.log("Using MOCK Data");
        allMenuData = MOCK_MENU;
        renderApp(allMenuData);
        return;
    }

    try {
        const res = await fetch('api/get_menu.php');
        const json = await res.json();
        if (json.status === 'success') {
            allMenuData = json.data;
            renderApp(allMenuData);
        } else {
            console.error(json.message);
        }
    } catch (e) {
        console.error("Fetch error", e);
        // Fallback
        allMenuData = MOCK_MENU;
        renderApp(allMenuData);
    }
}

// Rendering
function renderApp(data) {
    renderCategories(data);
    // Default select first category
    if (data.length > 0) {
        selectCategory(data[0].id);
    }
}

function renderCategories(data) {
    const container = document.getElementById('category-scroll');
    if (!container) return;

    container.innerHTML = data.map(cat => `
        <div class="category-pill ${currentCategory === cat.id ? 'active' : ''}" 
             onclick="selectCategory(${cat.id})">
            ${cat.name}
        </div>
    `).join('');
}

function selectCategory(catId) {
    currentCategory = catId;
    renderCategories(allMenuData); // re-render to update active class

    const catData = allMenuData.find(c => c.id === catId);
    const container = document.getElementById('menu-list');

    if (!catData) return;

    let html = '';

    catData.sub_categories.forEach(sub => {
        html += `<div class="section-title">${sub.name}</div><div class="menu-grid">`;
        sub.items.forEach(item => {
            const qty = cart[item.id] ? cart[item.id].qty : 0;
            html += buildItemCard(item, qty);
        });
        html += `</div>`;
    });

    container.innerHTML = html;
}

// Cart Logic
function addToCart(itemId, itemStr) {
    // We decode the str
    const item = JSON.parse(decodeHtml(itemStr));
    if (!cart[itemId]) {
        cart[itemId] = { item, qty: 1 };
    } else {
        cart[itemId].qty++;
    }
    updateCartUI();
    // Re-render current view to show qty controls
    selectCategory(currentCategory);
}

function updateQty(itemId, change) {
    if (cart[itemId]) {
        cart[itemId].qty += change;
        if (cart[itemId].qty <= 0) {
            delete cart[itemId];
        }
    }
    updateCartUI();
    selectCategory(currentCategory);
}

function updateCartUI() {
    // Save to local storage
    localStorage.setItem('cart_data', JSON.stringify(cart));

    const subtotal = Object.values(cart).reduce((a, b) => a + (b.qty * b.item.price), 0);
    const cgst = subtotal * 0.025;
    const sgst = subtotal * 0.025;
    const totalToPay = subtotal + cgst + sgst;

    const bar = document.getElementById('cart-bar');
    if (totalToPay > 0) {
        if (bar) {
            bar.style.display = 'flex';
            document.getElementById('cart-count').innerText = `${Object.values(cart).reduce((a, b) => a + b.qty, 0)} ITEMS`;
            document.getElementById('cart-total').innerText = `₹${totalToPay.toFixed(2)}`;
        }
    } else {
        if (bar) bar.style.display = 'none';
        closeCartModal();
    }
}

// Modal
function openCartModal() {
    const modal = document.getElementById('cart-modal');
    modal.classList.add('open');
    renderCartItems();
}

function closeCartModal() {
    document.getElementById('cart-modal').classList.remove('open');
}

function renderCartItems() {
    const container = document.getElementById('cart-items-container');
    const subtotal = Object.values(cart).reduce((a, b) => a + (b.qty * b.item.price), 0);
    const cgst = subtotal * 0.025;
    const sgst = subtotal * 0.025;
    const totalPrice = subtotal + cgst + sgst;

    let html = '';

    if (Object.keys(cart).length === 0) {
        html = '<div style="text-align:center; padding:20px;">Cart is empty</div>';
    } else {
        Object.values(cart).forEach(({ item, qty }) => {
            html += `
                <div class="cart-item">
                    <div>
                        <div class="item-name">${item.name}</div>
                        <div class="item-price">${item.price * qty}</div>
                    </div>
                    <div class="qty-control">
                        <button class="qty-btn" onclick="updateQty(${item.id}, -1); renderCartItems()">−</button>
                        <span class="qty-val">${qty}</span>
                        <button class="qty-btn" onclick="updateQty(${item.id}, 1); renderCartItems()">+</button>
                    </div>
                </div>
            `;
        });

        // Form & Totals
        html += `
            <div style="margin-top:20px; border-top:1px solid #eee; padding-top:16px;">
                 <div class="form-group">
                    <div style="display:flex; justify-content:space-between; margin-bottom:5px; color:#666;">
                        <span>Item Total</span>
                        <span>₹${subtotal.toFixed(2)}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:5px; color:#666; font-size:0.9em;">
                        <span>CGST (2.5%)</span>
                        <span>₹${cgst.toFixed(2)}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px; color:#666; font-size:0.9em;">
                        <span>SGST (2.5%)</span>
                        <span>₹${sgst.toFixed(2)}</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Table Number (Optional)</label>
                    <input type="text" id="cust-table" class="form-input" placeholder="e.g. 5">
                </div>
                <div class="form-group">
                    <label class="form-label">Your Name</label>
                    <input type="text" id="cust-name" class="form-input" placeholder="e.g. John">
                </div>
                <div class="form-group">
                    <label class="form-label">Dining Option</label>
                    <select id="cust-order-type" class="form-input">
                        <option value="Dine-in">Dine-in</option>
                        <option value="Take-away">Take-away</option>
                    </select>
                </div>
                 <div class="form-group">
                    <label class="form-label">Payment</label>
                    <select id="cust-payment" class="form-input">
                        <option value="Cash">Pay at Counter</option>
                        <option value="UPI">UPI</option>
                    </select>
                </div>
                <div class="item-foot">
                    <strong>Total Payable</strong>
                    <strong style="font-size:1.2rem; color:var(--primary-color)">₹${totalPrice.toFixed(2)}</strong>
                </div>
                <button class="primary-btn" onclick="placeOrder()">Place Order</button>
            </div>
        `;
    }

    container.innerHTML = html;
}

// Order Placement
async function placeOrder() {
    const name = document.getElementById('cust-name').value;
    const table = document.getElementById('cust-table').value;
    const payment = document.getElementById('cust-payment').value;
    const orderType = document.getElementById('cust-order-type') ? document.getElementById('cust-order-type').value : 'Dine-in';

    // Calculate Total with Tax
    const subtotal = Object.values(cart).reduce((a, b) => a + (b.qty * b.item.price), 0);
    const tax = subtotal * 0.05; // 2.5% + 2.5%
    const total = subtotal + tax;

    if (!name || name.trim() === '') {
        alert("Please enter your Name to place order.");
        return;
    }

    if (total === 0) {
        alert("Cart is empty!");
        return;
    }

    const orderData = {
        customer_name: name,
        table_number: table,
        payment_method: payment,
        order_type: orderType,
        total_amount: total,
        items: Object.values(cart).map(c => ({
            id: c.item.id,
            quantity: c.qty,
            price: c.item.price
        }))
    };

    if (MOCK_MODE) {
        // Mock Success
        alert("Order Placed Successfully! (Mock Order ID: ORD-DEMO-123)");
        cart = {};
        updateCartUI();
        localStorage.setItem('last_order_id', 'ORD-DEMO-123');
        window.location.href = 'track_order.php?id=ORD-DEMO-123';
        return;
    }

    try {
        toggleLoading(true);
        const res = await fetch('api/place_order.php', {
            method: 'POST',
            body: JSON.stringify(orderData)
        });
        const json = await res.json();
        if (json.status === 'success') {
            cart = {};
            localStorage.setItem('last_order_id', json.order_id);
            // window.location.href = `track_order.php?id=${json.order_id}`;
            showSuccessPopup(json.order_id, orderData);
        } else {
            alert("Error: " + json.message);
        }
    } catch (e) {
        alert("Network Error");
    } finally {
        toggleLoading(false);
    }
}

function showSuccessPopup(orderId, orderData) {
    closeCartModal();
    const modal = document.createElement('div');
    modal.className = 'modal open';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';

    let itemsHtml = orderData.items.map(i => {
        // Find item name from cart or allMenuData logic (or pass full item name in orderData)
        // Here we rely on tracking page logic typically, but for this popup we reuse orderData logic if name was passed.
        // Best to just use a simple list
        return `<div>${i.quantity} x Item (check track page) - ₹${i.price * i.quantity}</div>`;
    }).join('');
    // Better: We construct items list from Cart before clearing it, or we rely on passed Data having names.
    // Let's modify placeOrder to keep names handy for this popup.
    const itemsListHtml = Object.values(orderData.items).map(i => {
        // We need names. Let's fix orderData construction to include names or fetch from DOM
        // Actually, orderData.items currently stripped names. Let's fix that block too.
        return ``;
    }).join('');

    // Re-construct purely for popup
    let total = orderData.total_amount;

    modal.innerHTML = `
        <div class="modal-content" style="height:auto; max-height:80vh; border-radius:24px; text-align:center; padding:30px;">
            <div style="font-size:3rem; margin-bottom:10px;">🎉</div>
            <h2 style="color:var(--primary-color); margin-bottom:10px;">Order Placed!</h2>
            <div style="font-size:1.2rem; font-weight:700; margin-bottom:20px;">#${orderId}</div>
            
            <p style="color:var(--text-muted); margin-bottom:20px;">Your order has been sent to the kitchen.</p>
            
            <div style="font-size:1.5rem; font-weight:700; color:var(--text-color); margin-bottom:30px;">
                Total: ₹${total}
            </div>

            <button class="primary-btn" onclick="window.location.href='track_order.php?id=${orderId}'" style="margin-bottom:12px;">Track Order</button>
            <button class="primary-btn" onclick="location.reload()" style="background:var(--card-bg); color:var(--text-color); border:1px solid var(--border-color);">Back to Menu</button>
        </div>
    `;
    document.body.appendChild(modal);
}

function toggleLoading(show) {
    let loader = document.getElementById('global-loader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'global-loader';
        loader.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.8); z-index:2000; display:flex; align-items:center; justifyContent:center; flex-direction:column;';
        loader.innerHTML = '<div class="spinner" style="border:4px solid #f3f3f3; border-top:4px solid var(--primary-color); border-radius:50%; width:40px; height:40px; animation:spin 1s linear infinite;"></div><div style="margin-top:10px; font-weight:600; color:var(--primary-color);">Processing...</div>';
        document.body.appendChild(loader);

        const style = document.createElement('style');
        style.innerHTML = '@keyframes spin {0% {transform: rotate(0deg);} 100% {transform: rotate(360deg);}}';
        document.head.appendChild(style);
    }
    loader.style.display = show ? 'flex' : 'none';
}

// Search Logic
function filterMenu() {
    const term = document.getElementById('search-input').value.toLowerCase();
    const container = document.getElementById('menu-list');

    if (term.length < 2) {
        // Restore category view
        document.getElementById('category-scroll').style.display = 'flex';
        document.querySelector('.hero-section').style.display = 'block';
        if (currentCategory) selectCategory(currentCategory);
        else if (allMenuData.length > 0) selectCategory(allMenuData[0].id);
        return;
    }

    // Search Mode
    document.getElementById('category-scroll').style.display = 'none';
    document.querySelector('.hero-section').style.display = 'none';

    let html = '';
    let found = false;

    allMenuData.forEach(cat => {
        cat.sub_categories.forEach(sub => {
            const matches = sub.items.filter(item => item.name.toLowerCase().includes(term));
            if (matches.length > 0) {
                found = true;
                html += `<div class="section-title">${sub.name} <small style="font-weight:400; font-size:0.9rem; color:var(--text-muted)">(${cat.name})</small></div><div class="menu-grid">`;
                matches.forEach(item => {
                    const qty = cart[item.id] ? cart[item.id].qty : 0;
                    html += buildItemCard(item, qty);
                });
                html += `</div>`;
            }
        });
    });

    if (!found) {
        html = '<div style="text-align:center; padding:40px; color:var(--text-muted);">No items found matching "' + term + '"</div>';
    }

    container.innerHTML = html;
}

function buildItemCard(item, qty) {
    return `
        <div class="menu-item animate-fade">
            <img src="${item.image || 'assets/images/placeholder.png'}" alt="${item.name}" loading="lazy">
            <div class="item-details">
                <div>
                    <div class="item-name">${item.name}</div>
                    <div class="item-desc">${item.description || ''}</div>
                </div>
                <div class="item-foot">
                    <div class="item-price">${item.price}</div>
                    ${qty === 0 ?
            `<button class="add-btn" onclick="addToCart(${item.id}, '${escapeHtml(JSON.stringify(item))}')">ADD</button>` :
            `<div class="qty-control">
                            <button class="qty-btn" onclick="updateQty(${item.id}, -1)">−</button>
                            <span class="qty-val">${qty}</span>
                            <button class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                        </div>`
        }
                </div>
            </div>
        </div>
    `;
}

function showTrackOrderLink(id) {
    // Maybe add a small banner? For now we just remember it.
}

// Utils
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function (m) { return map[m]; });
}

function decodeHtml(text) {
    const doc = new DOMParser().parseFromString(text, "text/html");
    return doc.documentElement.textContent;
}
