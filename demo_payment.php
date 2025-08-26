<?php
// Demo payment page - simulates external Shake-Out payment gateway
// This runs independently without Moodle to simulate real external gateway

$invoice_id = $_GET['invoice_id'] ?? '';
$amount = $_GET['amount'] ?? '0.00';
$currency = $_GET['currency'] ?? 'USD';
$description = $_GET['description'] ?? 'Payment';

// Basic input sanitization
$invoice_id = htmlspecialchars(strip_tags($invoice_id));
$amount = number_format(floatval($amount), 2);
$currency = htmlspecialchars(strip_tags($currency));
$description = htmlspecialchars(strip_tags($description));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shake-Out Payment Gateway</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .payment-header { background: linear-gradient(135deg, #007cba 0%, #0056b3 100%); }
        .demo-badge { background: #ff6b35; color: white; font-size: 0.8em; padding: 2px 8px; border-radius: 10px; }
    </style>
</head>
<body class="bg-light"><?php echo "\n"; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header payment-header text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">🚀 Shake-Out Payment Gateway</h4>
                        <span class="demo-badge">DEMO MODE</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Demo Environment:</strong> This simulates the external Shake-Out payment gateway. In production, this would be hosted at <code>payment.shake-out.com</code>
                    </div>
                    
                    <h5>Payment Details</h5>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Invoice ID:</strong></td>
                            <td><?php echo htmlspecialchars($invoice_id); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Description:</strong></td>
                            <td><?php echo htmlspecialchars($description); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Amount:</strong></td>
                            <td><?php echo htmlspecialchars($amount . ' ' . $currency); ?></td>
                        </tr>
                    </table>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-6">
                            <button type="button" onclick="completePayment('success')" 
                                    class="btn btn-success btn-lg w-100">
                                ✅ Complete Payment
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" onclick="completePayment('failed')" 
                                    class="btn btn-danger btn-lg w-100">
                                ❌ Simulate Failure
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="button" class="btn btn-secondary w-100" onclick="window.close();">
                            Cancel & Close
                        </button>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small>This is a demo environment for testing the Shake-Out payment gateway integration.</small>
                </div>
            </div>
        </div>
    </div>
</div>

    <script>
        function completePayment(status) {
            const data = new FormData();
            data.append('invoice_id', '<?php echo $invoice_id; ?>');
            data.append('status', status === 'success' ? 'completed' : 'failed');
            data.append('amount', '<?php echo $amount; ?>');
            data.append('currency', '<?php echo $currency; ?>');
            data.append('demo_mode', '1');
            data.append('payment_action', status);
            
            fetch('callback.php', {
                method: 'POST',
                body: data
            }).then(response => {
                if (response.ok) {
                    if (status === 'success') {
                        alert('✅ Payment completed successfully!\n\nIn a real environment, you would be redirected back to the course.');
                    } else {
                        alert('❌ Payment failed as requested.\n\nThis simulates a failed payment scenario.');
                    }
                    window.close();
                } else {
                    alert('Error processing payment simulation.');
                }
            }).catch(error => {
                console.error('Error:', error);
                alert('Error processing payment simulation.');
            });
        }
    </script>
</body>
</html>