<?php
/**
 * Shake-Out Payment Gateway Plugin Demo
 * 
 * This demonstrates the Shake-Out Moodle payment gateway plugin functionality.
 * In a real Moodle installation, this plugin would be located at:
 * /payment/gateway/shakeout/
 */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shake-Out Payment Gateway Plugin - Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .plugin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
        }
        .feature-icon {
            width: 48px;
            height: 48px;
            background: #f8f9fa;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .code-snippet {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 1rem;
            margin: 1rem 0;
        }
        .api-endpoint {
            background: #e9ecef;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            margin: 0.5rem 0;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-configured { background: #cce7ff; color: #004085; }
        .file-tree {
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="plugin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-4 fw-bold">Shake-Out Payment Gateway</h1>
                    <p class="lead mb-4">Complete Moodle payment gateway plugin for processing payments through Shake-Out payment service</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="status-badge status-active">✓ Fully Functional</span>
                        <span class="status-badge status-configured">✓ API Integrated</span>
                        <span class="status-badge status-active">✓ Webhook Ready</span>
                        <span class="status-badge status-configured">✓ GDPR Compliant</span>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <svg width="120" height="120" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2" stroke="white" stroke-width="2" fill="rgba(255,255,255,0.1)"/>
                        <line x1="1" y1="10" x2="23" y2="10" stroke="white" stroke-width="2"/>
                        <circle cx="18" cy="14" r="2" fill="white"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <!-- Plugin Overview -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="mb-4">Plugin Overview</h2>
                <p class="lead">This is a complete Moodle payment gateway plugin that integrates with the Shake-Out payment service. It provides secure payment processing for course enrollments, activity fees, and other paid content within Moodle.</p>
            </div>
        </div>

        <!-- Key Features -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="mb-4">Key Features</h2>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22S6 16 6 10A6 6 0 0 1 12 4A6 6 0 0 1 18 10C18 16 12 22 12 22Z" stroke="#007bff" stroke-width="2" fill="none"/>
                        <circle cx="12" cy="10" r="3" stroke="#007bff" stroke-width="2" fill="none"/>
                    </svg>
                </div>
                <h5>Multi-Currency Support</h5>
                <p class="text-muted">Support for USD, EUR, GBP, EGP, SAR, AED, KWD, QAR, BHD, OMR, JOD</p>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="#007bff" stroke-width="2" fill="none"/>
                        <circle cx="8.5" cy="8.5" r="1.5" fill="#007bff"/>
                        <path d="M21 15L16 10L5 21" stroke="#007bff" stroke-width="2" fill="none"/>
                    </svg>
                </div>
                <h5>Sandbox Mode</h5>
                <p class="text-muted">Test payments safely with sandbox environment before going live</p>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 12L11 14L15 10" stroke="#007bff" stroke-width="2" fill="none"/>
                        <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#007bff" stroke-width="2" fill="none"/>
                    </svg>
                </div>
                <h5>Webhook Integration</h5>
                <p class="text-muted">Real-time payment status updates via secure webhooks</p>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 15L17 10H7L12 15Z" fill="#007bff"/>
                        <path d="M17 6H22V20C22 21.1046 21.1046 22 20 22H4C2.89543 22 2 21.1046 2 20V6H7" stroke="#007bff" stroke-width="2" fill="none"/>
                        <path d="M12 2V6" stroke="#007bff" stroke-width="2"/>
                    </svg>
                </div>
                <h5>Privacy Compliant</h5>
                <p class="text-muted">GDPR compliant with full privacy API implementation</p>
            </div>
        </div>

        <!-- Plugin Structure -->
        <div class="row mb-5">
            <div class="col-md-6">
                <h3>Plugin Architecture</h3>
                <div class="code-snippet">
                    <div class="file-tree">
<strong>payment/gateway/shakeout/</strong>
├── classes/
│   ├── gateway.php                    <em># Main gateway class</em>
│   ├── shakeout_helper.php           <em># API integration helper</em>
│   ├── admin_setting_manage_shakeout.php
│   └── privacy/
│       └── provider.php              <em># GDPR compliance</em>
├── db/
│   ├── install.xml                   <em># Database schema</em>
│   └── upgrade.php                   <em># Database migrations</em>
├── amd/src/
│   ├── gateways_modal.js            <em># Payment modal JS</em>
│   └── repository.js                <em># AJAX repository</em>
├── templates/
│   └── shakeout_button.mustache     <em># Payment button</em>
├── lang/en/
│   └── paygw_shakeout.php          <em># Language strings</em>
├── pay.php                          <em># Payment processing</em>
├── callback.php                     <em># Webhook handler</em>
└── version.php                      <em># Plugin metadata</em>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h3>API Endpoints</h3>
                <div class="api-endpoint">
                    <strong>POST</strong> /payment/gateway/shakeout/pay.php
                    <div class="text-muted mt-1">Process payment and redirect to Shake-Out</div>
                </div>
                <div class="api-endpoint">
                    <strong>POST</strong> /payment/gateway/shakeout/callback.php
                    <div class="text-muted mt-1">Handle webhook notifications</div>
                </div>
                
                <h4 class="mt-4">Supported Currencies</h4>
                <div class="row">
                    <div class="col-6">
                        <ul class="list-unstyled">
                            <li>• USD (US Dollar)</li>
                            <li>• EUR (Euro)</li>
                            <li>• GBP (British Pound)</li>
                            <li>• EGP (Egyptian Pound)</li>
                            <li>• SAR (Saudi Riyal)</li>
                            <li>• AED (UAE Dirham)</li>
                        </ul>
                    </div>
                    <div class="col-6">
                        <ul class="list-unstyled">
                            <li>• KWD (Kuwaiti Dinar)</li>
                            <li>• QAR (Qatari Riyal)</li>
                            <li>• BHD (Bahraini Dinar)</li>
                            <li>• OMR (Omani Rial)</li>
                            <li>• JOD (Jordanian Dinar)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Technical Features -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="mb-4">Technical Implementation</h2>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Gateway Integration</h5>
                        <p class="card-text">Implements Moodle's payment gateway interface with full support for payment processing, configuration management, and currency validation.</p>
                        <ul class="list-unstyled">
                            <li>✓ Gateway configuration form</li>
                            <li>✓ Payment validation</li>
                            <li>✓ Currency support checks</li>
                            <li>✓ Error handling</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Frontend JavaScript</h5>
                        <p class="card-text">Modern JavaScript modules using AMD pattern with modal-based payment interface and AJAX repository for backend communication.</p>
                        <ul class="list-unstyled">
                            <li>✓ Modal-based UI</li>
                            <li>✓ AJAX repository pattern</li>
                            <li>✓ Event-driven architecture</li>
                            <li>✓ Template rendering</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Security & Privacy</h5>
                        <p class="card-text">Comprehensive security implementation with webhook signature verification and complete GDPR privacy compliance.</p>
                        <ul class="list-unstyled">
                            <li>✓ Webhook signature verification</li>
                            <li>✓ GDPR data export/deletion</li>
                            <li>✓ Secure API communication</li>
                            <li>✓ Payment logging</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Installation Instructions -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="mb-4">Installation in Moodle</h2>
                <div class="alert alert-info">
                    <h5>📦 Plugin Installation</h5>
                    <p class="mb-2">To install this plugin in your Moodle site:</p>
                    <ol class="mb-0">
                        <li>Copy all files to <code>/payment/gateway/shakeout/</code> in your Moodle directory</li>
                        <li>Visit <strong>Site administration → Notifications</strong> to complete installation</li>
                        <li>Go to <strong>Site administration → Payments → Payment accounts</strong> to configure</li>
                        <li>Add your Shake-Out API keys and configure gateway settings</li>
                        <li>Enable the gateway for payment areas you want to use</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Configuration -->
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Gateway Configuration</h2>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Setting</th>
                                <th>Description</th>
                                <th>Required</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>API Key</strong></td>
                                <td>Shake-Out API key for authentication</td>
                                <td><span class="badge bg-danger">Required</span></td>
                            </tr>
                            <tr>
                                <td><strong>Secret Key</strong></td>
                                <td>Secret key for webhook signature verification</td>
                                <td><span class="badge bg-danger">Required</span></td>
                            </tr>
                            <tr>
                                <td><strong>Sandbox Mode</strong></td>
                                <td>Enable sandbox environment for testing</td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                            </tr>
                            <tr>
                                <td><strong>Success URL</strong></td>
                                <td>Custom success page (default: site home)</td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                            </tr>
                            <tr>
                                <td><strong>Failure URL</strong></td>
                                <td>Custom failure page (default: site home)</td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                            </tr>
                            <tr>
                                <td><strong>Pending URL</strong></td>
                                <td>Custom pending payment page (default: site home)</td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h5>Shake-Out Payment Gateway Plugin</h5>
                    <p class="mb-1">Complete Moodle payment gateway integration</p>
                    <p class="text-muted small">Compatible with Moodle 4.1+ | Version 1.0.0</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <p class="mb-1"><strong>Plugin Type:</strong> Payment Gateway</p>
                    <p class="mb-1"><strong>Component:</strong> paygw_shakeout</p>
                    <p class="text-muted small">GPL v3 Licensed</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>