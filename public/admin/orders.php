<?php
$page_title = 'Live Orders';
$active_page = 'orders';
require_once 'includes/header.php';
?>

<div class="flex justify-between mb-4">
    <h2 class="page-title">Real-time Orders</h2>
    <div style="display:flex; align-items:center; gap:15px;">
        <input type="text" id="searchInput" placeholder="Search orders..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;">
        <div style="font-size: 0.875rem; color: #666; display: flex; align-items: center; gap: 5px;">
            <span class="status-badge" style="background: #10b981; width: 10px; height: 10px; padding: 0;"></span> Live Updates Active
        </div>

        <!-- <button onclick="playNotificationSound()" class="btn btn-sm" style="background:#e5e7eb; color:#374151;" title="Test Sound">
             <i class="fas fa-play"></i> TEST
        </button> -->
    </div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Details</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="orders-table-body">
            <tr><td colspan="7" class="text-center">Loading orders...</td></tr>
        </tbody>
    </table>
</div>

<!-- Order Detail Modal -->
<div id="orderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: white; width: 90%; max-width: 500px; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        <div style="padding: 1.5rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.25rem;">Manage Order <span id="modal-order-id"></span></h3>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <div style="padding: 1.5rem;">
            <div id="modal-content">
                <!-- Data will be populated here -->
            </div>
            
            <form id="updateOrderForm" style="margin-top: 1.5rem;">
                <input type="hidden" id="edit-order-id" name="order_id">
                
                <div class="form-group">
                    <label class="form-label">Order Status</label>
                    <select name="order_status" id="edit-order-status" class="form-control">
                        <option value="Received">Received</option>
                        <option value="Preparing">Preparing</option>
                        <option value="Ready">Ready</option>
                        <option value="Served">Served</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" id="edit-payment-status" class="form-control">
                        <option value="Pending">Pending</option>
                        <option value="Paid">Paid</option>
                    </select>
                </div>

                <div class="form-group" id="refund-group" style="display:none;">
                    <label class="form-label">Refund Amount</label>
                    <input type="number" step="0.01" name="refund_amount" id="edit-refund-amount" class="form-control" placeholder="0.00">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Update Order</button>
            </form>
        </div>
    </div>
</div>

<script>
let currentSearch = '';

function fetchOrders() {
    fetch('ajax/get_orders.php?_=' + new Date().getTime())
        .then(response => response.text())
        .then(data => {
            document.getElementById('orders-table-body').innerHTML = data;
            if (currentSearch) filterOrders();
            checkNewOrders(data);
        })
        .catch(err => console.error("Fetch error:", err));
}

function filterOrders() {
    let rows = document.querySelectorAll('#orders-table-body tr');
    const term = currentSearch.toLowerCase();
    rows.forEach(row => {
        if (row.innerText.includes("Loading")) return;
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
}

document.getElementById('searchInput').addEventListener('keyup', function() {
    currentSearch = this.value;
    filterOrders();
});

// Initial fetch and Interval
fetchOrders();
setInterval(fetchOrders, 2000); // 2 seconds polling

function openOrderModal(order) {
    document.getElementById('modal-order-id').innerText = '#' + order.order_number;
    document.getElementById('edit-order-id').value = order.id;
    document.getElementById('edit-order-status').value = order.order_status;
    document.getElementById('edit-payment-status').value = order.payment_status;
    document.getElementById('edit-refund-amount').value = order.refund_amount || 0;
    
    toggleRefundField();
    
    document.getElementById('orderModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('orderModal').style.display = 'none';
}

// Close modal on outside click
window.onclick = function(event) {
    if (event.target == document.getElementById('orderModal')) {
        closeModal();
    }
}

document.getElementById('updateOrderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('ajax/update_order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            fetchOrders(); // Refresh immediately
        } else {
            alert('Error updating order: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => console.error(err));
});

document.getElementById('edit-order-status').addEventListener('change', toggleRefundField);

function toggleRefundField() {
    const status = document.getElementById('edit-order-status').value;
    const refundGroup = document.getElementById('refund-group');
    if (status === 'Cancelled') {
        refundGroup.style.display = 'block';
    } else {
        refundGroup.style.display = 'none';
        document.getElementById('edit-refund-amount').value = 0;
    }
}
</script>
<script>
// Notification System
let maxSeenOrder = 0;
let initialLoad = true;

// Embedded "Ding" Sound (Base64 MP3)
const notificationSound = new Audio("data:audio/mpeg;base64,//uQxAAAAAAAAAAAAAAASW5mbwAAAA8AAAACAAABhAAAAAE4AABVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVX/8AABRAAABBAAAAEAAAGAEBAQGAAAAAAAAAAAAVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVQ==");

// Auto-enable audio on first interaction
document.addEventListener('click', function unlockAudio() {
    notificationSound.play().then(() => {
        notificationSound.pause();
        notificationSound.currentTime = 0;
        document.removeEventListener('click', unlockAudio);
    }).catch(e => console.log("Waiting for interaction to unlock audio..."));
}, { once: true });


// function enableSound() {
//     // Attempt to play sound immediately to unlock audio context
//     notificationSound.play().then(() => {
//         notificationSound.pause();
//         notificationSound.currentTime = 0;
//         
//         const btn = document.getElementById('enableSoundBtn');
//         if(btn) {
//             btn.innerHTML = '<i class="fas fa-volume-up"></i> &nbsp; Sound Active';
//             btn.classList.remove('btn-light');
//             btn.classList.add('btn-success');
//             btn.style.background = '#10b981';
//             btn.style.color = 'white';
//         }
//         showToast("Notifications Enabled!");
//         // Play once for confirmation - DISABLED as requested
//         // playNotificationSound(); 
//     }).catch(e => {
//         console.error("Audio enable failed:", e);
//         alert("Please click 'OK' then click 'Enable Sound' again to allow audio playback.");
//     });
// }

function playNotificationSound() {
    notificationSound.currentTime = 0;
    notificationSound.play().catch(e => console.error("Play failed:", e));
}

function notifyUser(text) {
    showToast(text);
    playNotificationSound();
    
    // Text-to-Speech
    if ('speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 1.0;
        utterance.pitch = 1.0;
        utterance.volume = 1.0;
        window.speechSynthesis.speak(utterance);
    }
}

function showToast(msg) {
    let toast = document.getElementById('notif-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'notif-toast';
        toast.style.cssText = "position:fixed; top:20px; right:20px; background:#333; color:#fff; padding:12px 24px; border-radius:8px; z-index:9999; opacity:0; transition:opacity 0.3s;";
        document.body.appendChild(toast);
    }
    toast.innerText = msg;
    toast.style.opacity = '1';
    setTimeout(() => { toast.style.opacity = '0'; }, 3000);
}

function checkNewOrders(html) {
    const regex = /<td>\s*#(\d+)\s*<\/td>/g;  
    let match;
    let maxInBatch = 0;
    
    while ((match = regex.exec(html)) !== null) {
        const num = parseInt(match[1], 10);
        if (num > maxInBatch) maxInBatch = num;
    }
    
    if (maxInBatch === 0) return;

    if (initialLoad) {
        maxSeenOrder = maxInBatch;
        initialLoad = false;
        console.log("Baseline Order ID:", maxSeenOrder);
        return;
    }
    
    if (maxInBatch > maxSeenOrder) {
        console.log("New Order:", maxInBatch);
        maxSeenOrder = maxInBatch;
        notifyUser("You got a new order!");
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
