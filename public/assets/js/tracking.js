const MOCK_MODE = window.location.protocol === 'file:';

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const orderId = params.get('id');

    if (orderId) {
        document.getElementById('order-id-input').value = orderId;
        checkStatus(orderId);
    }
});

async function checkStatus(id = null) {
    const orderId = id || document.getElementById('order-id-input').value;
    if (!orderId) {
        alert("Enter Order ID");
        return;
    }

    if (MOCK_MODE) {
        renderStatus({
            order_number: orderId,
            order_status: 'Preparing',
            created_at: new Date().toISOString(),
            total_amount: 540,
            items: [
                { name: 'Mock Item 1', quantity: 2, price: 120 },
                { name: 'Mock Item 2', quantity: 1, price: 300 }
            ]
        });
        return;
    }

    try {
        const res = await fetch(`api/get_order_status.php?order_id=${orderId}`);
        const json = await res.json();
        if (json.status === 'success') {
            renderStatus(json.data);
        } else {
            document.getElementById('status-result').innerHTML = `<p style="color:#ef4444; text-align:center;">${json.message}</p>`;
        }
    } catch (e) {
        console.error(e);
        // Fallback demo
        if (orderId.includes('DEMO')) {
            renderStatus({
                order_number: orderId,
                order_status: 'Preparing',
                created_at: new Date().toISOString(),
                total_amount: 540,
                items: []
            });
        } else {
            document.getElementById('status-result').innerHTML = `<p style="color:#ef4444; text-align:center;">Network Error. Please try again.</p>`;
        }
    }
}

function renderStatus(order) {
    const steps = ['Received', 'Preparing', 'Ready', 'Served'];
    const icons = {
        'Received': 'fa-clipboard-check',
        'Preparing': 'fa-fire-burner',
        'Ready': 'fa-bell',
        'Served': 'fa-utensils',
        'Cancelled': 'fa-ban'
    };

    let currentStepIndex = steps.indexOf(order.order_status);
    let isCancelled = order.order_status === 'Cancelled';

    // Status Logic
    if (isCancelled) {
        let html = `
            <div class="menu-item" style="max-width:600px; margin: 20px auto; border-color: #ef4444; border-width: 2px; border-style: solid;">
                <div style="text-align:center; padding:20px;">
                    <i class="fas fa-ban" style="font-size:3rem; color:#ef4444; margin-bottom:15px;"></i>
                    <h3 style="color:#ef4444; font-size:1.8rem; margin-bottom:10px;">Order Cancelled</h3>
                    <p style="color:var(--text-muted); font-size:1.1rem; margin-bottom:20px;">Order #${order.order_number}</p>
                    
                    <div style="padding:16px; background:rgba(239,68,68,0.1); border-radius:12px; color:#c53030; font-weight:500;">
                        This order has been cancelled. Please contact the restaurant staff for assistance.
                    </div>
                </div>
            </div>
            <div style="text-align:center; margin-top:20px;">
                <a href="index.php" class="primary-btn" style="text-decoration:none; display:inline-block; width:auto; padding:12px 30px; background-color:var(--text-color); color: white;">Back to Menu</a>
            </div>
        `;
        document.getElementById('status-result').innerHTML = html;
        return;
    }

    if (currentStepIndex === -1) currentStepIndex = 0;

    let html = `
        <div class="menu-item" style="max-width:600px; margin: 20px auto; display:block;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
                <div>
                    <h3 style="color:var(--primary-color); font-size:1.4rem;">Order #${order.order_number}</h3>
                    <p style="color:var(--text-muted);">Table: ${order.table_number || 'Takeaway'}</p>
                </div>
                <div class="item-price" style="font-size:1.5rem;">${order.total_amount}</div>
            </div>
            
            <div style="position:relative; padding-left: 20px;">
    `;

    // Render Timeline
    steps.forEach((step, index) => {
        const isActive = index <= currentStepIndex;
        const isCurrent = index === currentStepIndex;
        const color = isActive ? 'var(--primary-color)' : '#e2e8f0';
        const textColor = isActive ? 'var(--text-color)' : 'var(--text-muted)'; // Dark text for light theme
        const iconColor = isActive ? 'white' : '#718096';

        // Vertical Line
        if (index < steps.length - 1) {
            html += `<div style="position:absolute; left:35px; top:${40 + (index * 70)}px; width:2px; height:40px; background:${isActive && currentStepIndex > index ? 'var(--primary-color)' : '#e2e8f0'}; z-index:0;"></div>`;
        }

        html += `
            <div style="display:flex; align-items:center; margin-bottom:30px; position:relative; z-index:1;">
                <div style="width:32px; height:32px; border-radius:50%; background:${color}; display:flex; align-items:center; justify-content:center; color:${iconColor}; font-size:14px; margin-right:16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition:0.3s;">
                    <i class="fas ${icons[step]}"></i>
                </div>
                <div>
                    <div style="font-weight:${isCurrent ? '700' : '500'}; color:${textColor}; font-size:1.1rem;">
                        ${step}
                    </div>
                </div>
            </div>
        `;
    });

    // Render Items
    if (order.items && order.items.length > 0) {
        html += `<div style="margin-top:20px; padding-top:20px; border-top:1px dashed var(--border-color);">
            <h4 style="margin-bottom:10px; font-size:1rem; color:var(--text-color);">Order Details</h4>
            <div style="font-size:0.9rem; color:var(--text-muted);">`;

        order.items.forEach(item => {
            html += `<div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span>${item.quantity} x ${item.name}</span>
                        <span>₹${item.price * item.quantity}</span>
                     </div>`;
        });

        html += `</div>
        <div style="border-top:1px solid #eee; padding-top:10px; margin-top:10px; display:flex; justify-content:space-between; font-weight:bold; color:var(--text-color);">
            <span>Total (Inc. Tax)</span>
            <span>₹${order.total_amount}</span>
        </div>
        </div>`;
    }

    html += `</div></div> 
             <div style="text-align:center; margin-top:20px;">
                <a href="index.php" class="primary-btn" style="text-decoration:none; display:inline-block; width:auto; padding:12px 30px;">Back to Main Page</a>
             </div>`;

    document.getElementById('status-result').innerHTML = html;
}

function toggleForgotForm(e) {
    if (e) e.preventDefault();
    const form = document.getElementById('forgot-form-container');
    const isHidden = form.style.display === 'none' || form.style.display === '';
    form.style.display = isHidden ? 'block' : 'none';

    // Scroll to form if showing
    if (isHidden) {
        // small delay to let display block apply
        setTimeout(() => form.scrollIntoView({ behavior: 'smooth', block: 'center' }), 50);
    }
}

// Find Order Logic
async function findOrders() {
    const name = document.getElementById('find-name').value;
    const table = document.getElementById('find-table').value;
    const resultDiv = document.getElementById('find-results');

    if (!name && !table) {
        alert("Please enter Name or Table Number");
        return;
    }

    resultDiv.innerHTML = '<div style="text-align:center; color:var(--text-muted)">Searching...</div>';

    try {
        const res = await fetch(`api/find_order.php?name=${encodeURIComponent(name)}&table=${encodeURIComponent(table)}`);
        const json = await res.json();

        if (json.status === 'success') {
            let html = '<div style="display:flex; flex-direction:column; gap:10px;">';
            json.data.forEach(o => {
                html += `
                    <div onclick="window.location.href='track_order.php?id=${o.order_number}'" 
                         style="background:white; padding:12px; border-radius:12px; cursor:pointer; display:flex; justify-content:space-between; align-items:center; border:1px solid var(--border-color); box-shadow:var(--shadow-sm);">
                        <div>
                            <div style="font-weight:700; color:var(--text-color);">#${o.order_number}</div>
                            <div style="font-size:0.8rem; color:var(--text-muted);">${o.order_status} • ₹${o.total_amount}</div>
                        </div>
                        <div style="color:var(--primary-color); font-size:0.9rem;">Track &rarr;</div>
                    </div>
                `;
            });
            html += '</div>';
            resultDiv.innerHTML = html;
        } else {
            resultDiv.innerHTML = `<div style="text-align:center; color:#ef4444;">${json.message}</div>`;
        }
    } catch (e) {
        resultDiv.innerHTML = '<div style="text-align:center; color:#ef4444;">Network Error: ' + e.message + '</div>';
    }
}
