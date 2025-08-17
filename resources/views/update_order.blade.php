<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Update Order #{{ $order->id }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .badge {
            font-size: 0.9em;
        }

        .spinner-border {
            width: 2rem;
            height: 2rem;
        }

        .btn-lg {
            padding: 12px 30px;
            font-size: 1.1em;
            font-weight: bold;
        }

        .card-header h4 {
            margin: 0;
            display: inline-block;
        }

        .card-header .badge {
            float: right;
            margin-top: 5px;
        }

        .custom-alert {
            margin-bottom: 1rem;
            transition: opacity 0.5s ease;
        }

        .container {
            margin-top: 30px;
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Update Order #{{ $order->id }}</h4>
                        <span
                            class="badge badge-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'completed' ? 'success' : 'secondary') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>

                    <div class="card-body">
                        <!-- Order Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Order Information</h5>
                                <p><strong>Order ID:</strong> {{ $order->id }}</p>
                                <p><strong>Amount:</strong> ₦{{ number_format($order->amount, 2) }}</p>
                                <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                                <p><strong>Reference:</strong> {{ $order->reference }}</p>
                                @if ($order->customer)
                                    <p><strong>Customer:</strong>
                                        {{ $order->customer->name ?? $order->customer->email }}</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h5>Delivery Information</h5>
                                <p><strong>Address:</strong> {{ $order->address }}</p>
                                @if ($order->state)
                                    <p><strong>State:</strong> {{ $order->state->name }}</p>
                                @endif
                                <p><strong>Dispatch Fee:</strong> ₦{{ number_format($order->dispatch_fee, 2) }}</p>
                                <p><strong>Current Dispatch Number:</strong>
                                    @if ($order->dispatch_number)
                                        <span class="badge badge-info">{{ $order->dispatch_number }}</span>
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Dispatch Number Management -->
                        @if ($order->status === 'pending')
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Dispatch Management</h5>
                                </div>
                                <div class="card-body">
                                    @if (!$order->dispatch_number)
                                        <!-- Set Dispatch Number Form -->
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            This order needs a dispatch number before it can be completed.
                                        </div>
                                        <form id="setDispatchForm">
                                            <div class="form-group">
                                                <label for="dispatch_number">Dispatch Number</label>
                                                <input type="text" class="form-control" id="dispatch_number"
                                                    name="dispatch_number" placeholder="Enter dispatch number" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-truck"></i> Set Dispatch Number
                                            </button>
                                        </form>
                                    @else
                                        <!-- Edit Dispatch Number -->
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            Current dispatch number: <strong>{{ $order->dispatch_number }}</strong>
                                        </div>

                                        <div class="mb-3">
                                            <button type="button" class="btn btn-secondary"
                                                onclick="toggleEditDispatch()">
                                                <i class="fas fa-edit"></i> Edit Dispatch Number
                                            </button>
                                        </div>

                                        <!-- Hidden Edit Form -->
                                        <div id="editDispatchForm" style="display: none;">
                                            <form id="updateDispatchForm">
                                                <div class="form-group">
                                                    <label for="new_dispatch_number">New Dispatch Number</label>
                                                    <input type="text" class="form-control" id="new_dispatch_number"
                                                        name="dispatch_number" value="{{ $order->dispatch_number }}"
                                                        required>
                                                </div>
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="fas fa-save"></i> Update Dispatch Number
                                                </button>
                                                <button type="button" class="btn btn-secondary"
                                                    onclick="toggleEditDispatch()">
                                                    Cancel
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Complete Order Section -->
                            @if ($order->dispatch_number)
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5>Complete Order</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <p class="mb-3">Order is ready to be completed. Click the button below to mark
                                            this order as completed.</p>
                                        <button type="button" class="btn btn-success btn-lg" onclick="completeOrder()">
                                            <i class="fas fa-check-circle"></i> COMPLETE ORDER
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @else
                            <!-- Order Already Completed -->
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                This order has already been {{ $order->status }}.
                            </div>
                        @endif
                    </div>

                    <div class="card-footer">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Processing...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>

    <script>
        // CSRF Token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const orderId = {{ $order->id }};

        // Initialize Bootstrap modal
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));

        // Toggle edit dispatch form
        function toggleEditDispatch() {
            const editForm = document.getElementById('editDispatchForm');
            editForm.style.display = editForm.style.display === 'none' ? 'block' : 'none';
        }

        // Set dispatch number
        document.getElementById('setDispatchForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const dispatchNumber = document.getElementById('dispatch_number').value.trim();

            if (!dispatchNumber) {
                showAlert('Please enter a dispatch number', 'warning');
                return;
            }

            updateOrder({
                dispatch_number: dispatchNumber
            });
        });

        // Update dispatch number
        document.getElementById('updateDispatchForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const dispatchNumber = document.getElementById('new_dispatch_number').value.trim();

            if (!dispatchNumber) {
                showAlert('Please enter a dispatch number', 'warning');
                return;
            }

            updateOrder({
                dispatch_number: dispatchNumber
            });
        });

        // Complete order
        function completeOrder() {
            if (confirm('Are you sure you want to complete this order? This action cannot be undone.')) {
                updateOrder({
                    status: 'completed'
                });
            }
        }

        // Generic function to update order
        function updateOrder(data) {
            loadingModal.show();

            fetch(`/api/orders/${orderId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(async response => {
                    const responseData = await response.json();
                    if (!response.ok) {
                        throw responseData;
                    }
                    return responseData;
                })
                .then(response => {
                    loadingModal.hide();
                    showAlert(response.message || 'Operation successful', 'success');

                    // Reload page after 2 seconds to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                })
                .catch(error => {
                    loadingModal.hide();
                    let errorMessage = 'An error occurred';

                    if (error && error.message) {
                        errorMessage = error.message;
                    } else if (error.status === 404) {
                        errorMessage = 'Order not found';
                    } else if (error.status === 500) {
                        errorMessage = 'Server error occurred';
                    }

                    showAlert(errorMessage, 'danger');
                });
        }

        // Show alert function
        function showAlert(message, type) {
            // Remove existing alerts
            const existingAlerts = document.querySelectorAll('.custom-alert');
            existingAlerts.forEach(alert => alert.remove());

            // Create new alert
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show custom-alert" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;

            // Insert alert at the top of the card body
            const cardBody = document.querySelector('.card-body');
            cardBody.insertAdjacentHTML('afterbegin', alertHtml);

            // Add click handler for close button
            const closeButton = cardBody.querySelector('.alert .close');
            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    const alert = this.closest('.alert');
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                });
            }

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const alert = cardBody.querySelector('.custom-alert');
                if (alert) {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            }, 5000);
        }
    </script>
</body>

</html>
