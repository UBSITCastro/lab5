<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module 4: Supply Requests</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .autocomplete-items {
            border: 1px solid #d4d4d4;
            border-bottom: none;
            border-top: none;
            z-index: 99;
            position: absolute;
            width: 100%;
            background: white;
            max-height: 150px;
            overflow-y: auto;
        }
        .autocomplete-items div {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #d4d4d4;
        }
        .autocomplete-items div:hover {
            background-color: #e9e9e9;
        }
    </style>
</head>
<body class="bg-gray-100 p-6 font-sans">

    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <div class="bg-white p-6 rounded-lg shadow-md md:col-span-1 h-fit">
            <h2 class="text-xl font-bold mb-4 text-blue-600">New Supply Request</h2>
            
            <form id="requestForm" autocomplete="off">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Employee Name</label>
                    <input type="text" id="requester_name" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" placeholder="John Doe" required>
                </div>

                <div class="mb-4 relative">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Search Item</label>
                    <input type="text" id="item_search" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" placeholder="Type to search..." required>
                    <input type="hidden" id="selected_item_id">
                    <div id="autocomplete-list" class="autocomplete-items hidden rounded-b-lg shadow-lg"></div>
                </div>

                <div id="stock_info" class="mb-4 p-3 bg-gray-50 border-l-4 border-gray-300 hidden">
                    <p class="text-sm text-gray-600">Current Stock:</p>
                    <p class="text-2xl font-bold" id="current_stock_display">0</p>
                    <p class="text-xs text-gray-500" id="unit_display"></p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Quantity Needed</label>
                    <input type="number" id="request_qty" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" min="1" disabled>
                    <p id="qty_error" class="text-red-500 text-xs mt-1 hidden">Quantity exceeds available stock!</p>
                </div>

                <button type="submit" id="submitBtn" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition disabled:bg-gray-400">
                    Submit Request
                </button>
            </form>
            <div id="msg" class="mt-4 text-center text-sm font-bold"></div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md md:col-span-2">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Request Log</h2>
                
                <div class="inline-flex rounded-md shadow-sm" role="group">
                    <button onclick="loadRequests('All')" class="px-4 py-2 text-sm font-medium bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100">All</button>
                    <button onclick="loadRequests('Pending')" class="px-4 py-2 text-sm font-medium bg-white border-t border-b border-gray-200 hover:bg-gray-100 text-yellow-600">Pending</button>
                    <button onclick="loadRequests('Approved')" class="px-4 py-2 text-sm font-medium bg-white border-t border-b border-gray-200 hover:bg-gray-100 text-green-600">Approved</button>
                    <button onclick="loadRequests('Rejected')" class="px-4 py-2 text-sm font-medium bg-white border border-gray-200 rounded-r-lg hover:bg-gray-100 text-red-600">Rejected</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Requester</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Item</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Qty</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody id="requestTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let currentStock = 0;
        const apiUrl = 'request_handler.php';

        const searchInput = document.getElementById('item_search');
        const listContainer = document.getElementById('autocomplete-list');

        searchInput.addEventListener('keyup', function() {
            const term = this.value;
            if (term.length < 1) {
                listContainer.classList.add('hidden');
                return;
            }

            fetch(`${apiUrl}?action=search&term=${term}`)
                .then(res => res.json())
                .then(data => {
                    listContainer.innerHTML = '';
                    if (data.length > 0) {
                        listContainer.classList.remove('hidden');
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.innerHTML = `<strong>${item.item_name}</strong> <span class='text-gray-500 text-sm'>(${item.stock_qty} ${item.unit})</span>`;
                            div.addEventListener('click', () => selectItem(item));
                            listContainer.appendChild(div);
                        });
                    } else {
                        listContainer.classList.add('hidden');
                    }
                });
        });

        function selectItem(item) {
            searchInput.value = item.item_name;
            document.getElementById('selected_item_id').value = item.id;
            listContainer.classList.add('hidden');
            
            const stockBox = document.getElementById('stock_info');
            stockBox.classList.remove('hidden');
            stockBox.classList.remove('border-red-500', 'border-gray-300');
            
            currentStock = parseInt(item.stock_qty);
            document.getElementById('current_stock_display').innerText = currentStock;
            document.getElementById('unit_display').innerText = item.unit;

            const qtyInput = document.getElementById('request_qty');
            qtyInput.value = '';
            
            if(currentStock <= 0) {
                 stockBox.classList.add('border-red-500');
                 qtyInput.disabled = true;
                 qtyInput.placeholder = "Out of Stock";
            } else {
                 stockBox.classList.add('border-green-500');
                 qtyInput.disabled = false;
                 qtyInput.placeholder = "Enter amount";
            }
        }

        document.getElementById('request_qty').addEventListener('keyup', function() {
            const val = parseInt(this.value);
            const btn = document.getElementById('submitBtn');
            const err = document.getElementById('qty_error');

            if (val > currentStock) {
                err.classList.remove('hidden');
                btn.disabled = true;
                btn.classList.add('bg-gray-400');
            } else {
                err.classList.add('hidden');
                btn.disabled = false;
                btn.classList.remove('bg-gray-400');
            }
        });

        document.getElementById('requestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const data = {
                name: document.getElementById('requester_name').value,
                item_id: document.getElementById('selected_item_id').value,
                qty: document.getElementById('request_qty').value
            };

            fetch(`${apiUrl}?action=submit_request`, {
                method: 'POST',
                body: JSON.stringify(data),
                headers: {'Content-Type': 'application/json'}
            })
            .then(res => res.json())
            .then(response => {
                const msgDiv = document.getElementById('msg');
                msgDiv.innerText = response.message;
                if(response.status === 'success') {
                    msgDiv.className = "mt-4 text-center text-sm font-bold text-green-600";
                    document.getElementById('requestForm').reset();
                    document.getElementById('stock_info').classList.add('hidden');
                    loadRequests('All');
                } else {
                    msgDiv.className = "mt-4 text-center text-sm font-bold text-red-600";
                }
            });
        });

        function loadRequests(filter) {
            fetch(`${apiUrl}?action=fetch_requests&filter=${filter}`)
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('requestTableBody');
                    tbody.innerHTML = '';
                    
                    data.forEach(req => {
                        let statusColor = 'bg-yellow-200 text-yellow-900';
                        if(req.status === 'Approved') statusColor = 'bg-green-200 text-green-900';
                        if(req.status === 'Rejected') statusColor = 'bg-red-200 text-red-900';

                        let actions = `<span class="text-gray-400 italic">Processed</span>`;
                        if(req.status === 'Pending') {
                            actions = `
                                <button onclick="processRequest(${req.id}, 'approve')" class="text-green-600 hover:text-green-900 mr-2">✔</button>
                                <button onclick="processRequest(${req.id}, 'reject')" class="text-red-600 hover:text-red-900">✖</button>
                            `;
                        }

                        const row = `
                            <tr>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">${new Date(req.request_date).toLocaleDateString()}</td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm font-bold">${req.requester_name}</td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">${req.item_name}</td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">${req.quantity} ${req.unit}</td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <span class="relative inline-block px-3 py-1 font-semibold leading-tight rounded-full ${statusColor}">
                                        <span class="relative">${req.status}</span>
                                    </span>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">${actions}</td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                });
        }

        function processRequest(id, action) {
            if(!confirm(`Are you sure you want to ${action} this request?`)) return;

            fetch(`${apiUrl}?action=process_request`, {
                method: 'POST',
                body: JSON.stringify({ id: id, process_action: action }),
                headers: {'Content-Type': 'application/json'}
            })
            .then(res => res.json())
            .then(response => {
                alert(response.message);
                loadRequests('All');
            });
        }

        loadRequests('All');
    </script>
</body>
</html>