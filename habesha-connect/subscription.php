<?php
require_once 'includes/functions.php';
requireLogin();
$currentUser = getCurrentUser();

$plans = [
    'weekly'    => ['label' => 'Weekly',    'price' => getSetting('weekly_price', '99'),    'period' => '7 days',   'days' => 7,   'save' => ''],
    'monthly'   => ['label' => 'Monthly',   'price' => getSetting('monthly_price', '299'),  'period' => '1 month',  'days' => 30,  'save' => 'Popular'],
    'quarterly' => ['label' => 'Quarterly', 'price' => getSetting('quarterly_price', '699'),'period' => '3 months', 'days' => 90,  'save' => 'Save 22%'],
    'annual'    => ['label' => 'Annual',    'price' => getSetting('annual_price', '1999'),  'period' => '1 year',   'days' => 365, 'save' => 'Best Value'],
];

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $plan    = $_POST['plan'] ?? '';
    $method  = $_POST['payment_method'] ?? '';
    $txnRef  = $_POST['transaction_ref'] ?? '';

    if (!array_key_exists($plan, $plans)) {
        $error = 'Invalid plan selected.';
    } elseif (empty($method)) {
        $error = 'Please select a payment method.';
    } elseif (empty($txnRef)) {
        $error = 'Please enter your transaction reference number.';
    } else {
        $db     = getDB();
        $amount = $plans[$plan]['price'];
        $days   = $plans[$plan]['days'];

        $db->prepare("INSERT INTO subscriptions (user_id, plan, amount, payment_method, transaction_id, status, starts_at, expires_at)
                      VALUES (?,?,?,?,?,'pending',NOW(),DATE_ADD(NOW(), INTERVAL $days DAY))")
           ->execute([$currentUser['id'], $plan, $amount, $method, $txnRef]);

        // Auto-approve for demo (in production, verify payment first)
        $subId = $db->lastInsertId();
        $db->prepare("UPDATE subscriptions SET status='active' WHERE id=?")->execute([$subId]);
        $db->prepare("UPDATE users SET is_premium=1, premium_expires=DATE_ADD(NOW(), INTERVAL $days DAY) WHERE id=?")->execute([$currentUser['id']]);

        flash('success', "🎉 Premium activated! Your {$plans[$plan]['label']} plan is now active.");
        redirect(SITE_URL . '/browse.php');
    }
}

$pageTitle = 'Premium Membership';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
    <div class="text-center mb-5">
        <div class="mb-2" style="font-size:2.5rem">⭐</div>
        <h2 class="fw-800">Upgrade to Premium</h2>
        <p class="text-muted fs-5">Get unlimited access to all features and find your match faster</p>
        <?php if ($currentUser['is_premium']): ?>
        <div class="alert alert-success d-inline-block px-4">
            <i class="bi bi-check-circle-fill me-2"></i>
            You have an active Premium membership
            <?php if ($currentUser['premium_expires']): ?>
            · Expires <?= date('M d, Y', strtotime($currentUser['premium_expires'])) ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger text-center mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Plans -->
    <div class="row g-4 justify-content-center mb-5">
        <?php foreach ($plans as $planKey => $plan): ?>
        <div class="col-6 col-lg-3">
            <div class="pricing-card <?= $plan['save'] === 'Popular' ? 'featured' : '' ?>">
                <?php if ($plan['save']): ?>
                <span class="pricing-badge"><?= $plan['save'] ?></span>
                <?php endif; ?>
                <div class="pricing-name"><?= $plan['label'] ?></div>
                <div class="pricing-price">
                    <?= number_format((int)$plan['price']) ?>
                    <small>ETB</small>
                </div>
                <div class="pricing-period">per <?= $plan['period'] ?></div>
                <ul class="pricing-features">
                    <li><i class="bi bi-check-circle-fill"></i>See Who Liked You</li>
                    <li><i class="bi bi-check-circle-fill"></i>Unlimited Messages</li>
                    <li><i class="bi bi-check-circle-fill"></i>Priority in Search</li>
                    <li><i class="bi bi-check-circle-fill"></i>Premium Badge</li>
                    <li><i class="bi bi-check-circle-fill"></i>Advanced Filters</li>
                    <?php if ($planKey === 'quarterly' || $planKey === 'annual'): ?>
                    <li><i class="bi bi-check-circle-fill"></i>Profile Boost</li>
                    <li><i class="bi bi-check-circle-fill"></i>Read Receipts</li>
                    <?php else: ?>
                    <li class="disabled"><i class="bi bi-x-circle"></i>Profile Boost</li>
                    <li class="disabled"><i class="bi bi-x-circle"></i>Read Receipts</li>
                    <?php endif; ?>
                </ul>
                <button class="btn btn-primary w-100 fw-700"
                        onclick="selectPlan('<?= $planKey ?>')">
                    <?= $currentUser['is_premium'] ? 'Renew' : 'Get ' . $plan['label'] ?>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-800"><i class="bi bi-credit-card me-2 text-success"></i>Complete Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="planSummary" class="alert alert-success py-2 mb-3"></div>
                    <form method="POST" id="paymentForm">
                        <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                        <input type="hidden" name="plan" id="selectedPlan">

                        <div class="mb-3">
                            <label class="form-label fw-600">Payment Method</label>
                            <div class="row g-2">
                                <?php if (getSetting('telebirr_enabled', '1')): ?>
                                <div class="col-6">
                                    <input type="radio" name="payment_method" id="pm_telebirr" value="telebirr" class="d-none payment-radio">
                                    <label for="pm_telebirr" class="payment-method-card border rounded-3 p-3 text-center cursor-pointer d-block">
                                        <i class="bi bi-phone fs-3 d-block text-primary mb-1"></i>
                                        <span class="fw-600 small">TeleBirr</span>
                                    </label>
                                </div>
                                <?php endif; ?>
                                <?php if (getSetting('cbebirr_enabled', '1')): ?>
                                <div class="col-6">
                                    <input type="radio" name="payment_method" id="pm_cbebirr" value="cbebirr" class="d-none payment-radio">
                                    <label for="pm_cbebirr" class="payment-method-card border rounded-3 p-3 text-center cursor-pointer d-block">
                                        <i class="bi bi-bank fs-3 d-block text-success mb-1"></i>
                                        <span class="fw-600 small">CBE Birr</span>
                                    </label>
                                </div>
                                <?php endif; ?>
                                <div class="col-6">
                                    <input type="radio" name="payment_method" id="pm_bank" value="bank_transfer" class="d-none payment-radio">
                                    <label for="pm_bank" class="payment-method-card border rounded-3 p-3 text-center cursor-pointer d-block">
                                        <i class="bi bi-building fs-3 d-block text-warning mb-1"></i>
                                        <span class="fw-600 small">Bank Transfer</span>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" name="payment_method" id="pm_card" value="visa" class="d-none payment-radio">
                                    <label for="pm_card" class="payment-method-card border rounded-3 p-3 text-center cursor-pointer d-block">
                                        <i class="bi bi-credit-card fs-3 d-block text-info mb-1"></i>
                                        <span class="fw-600 small">Card</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-600">Payment Instructions</label>
                            <div class="bg-light rounded-3 p-3 small text-muted">
                                <p class="mb-1"><strong>TeleBirr:</strong> Send to <strong>0911 000 001</strong> (Habesha Connect)</p>
                                <p class="mb-1"><strong>CBE Birr:</strong> Send to <strong>1000012345678</strong></p>
                                <p class="mb-0"><strong>Bank Transfer:</strong> CBE Account: <strong>1000012345678</strong></p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-600">Transaction Reference # *</label>
                            <input type="text" class="form-control" name="transaction_ref" placeholder="Enter your transaction ID or reference" required>
                            <div class="form-text">After sending payment, enter the confirmation/receipt number here.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg fw-700">
                                <i class="bi bi-lock-fill me-2"></i>Confirm Payment & Activate
                            </button>
                        </div>
                        <div class="text-center mt-2 text-muted small">
                            <i class="bi bi-shield-check me-1"></i>Secure & encrypted payment
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Comparison -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-gradient-primary text-white fw-700 py-3">
            <i class="bi bi-table me-2"></i>Free vs Premium Comparison
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Feature</th>
                        <th class="text-center">Free</th>
                        <th class="text-center text-success fw-700">Premium</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ([
                        ['Create Profile', true, true],
                        ['Browse Members', true, true],
                        ['Like Profiles', true, true],
                        ['See Who Liked You', false, true],
                        ['Unlimited Messaging', false, true],
                        ['See Who Viewed You', true, true],
                        ['Advanced Search Filters', 'Basic', 'Full'],
                        ['Profile Boost', false, true],
                        ['Premium Badge', false, true],
                        ['Priority in Search Results', false, true],
                    ] as [$feature, $free, $premium]): ?>
                    <tr>
                        <td class="fw-500"><?= $feature ?></td>
                        <td class="text-center">
                            <?php if ($free === true): ?>
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <?php elseif ($free === false): ?>
                            <i class="bi bi-x-circle-fill text-danger"></i>
                            <?php else: ?>
                            <span class="text-muted small"><?= $free ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($premium === true): ?>
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <?php elseif ($premium === false): ?>
                            <i class="bi bi-x-circle-fill text-danger"></i>
                            <?php else: ?>
                            <span class="text-success fw-600 small"><?= $premium ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script>
const plans = <?= json_encode(array_map(fn($k,$p) => ['key'=>$k,'label'=>$p['label'],'price'=>$p['price'],'period'=>$p['period']], array_keys($plans), $plans)) ?>;

function selectPlan(key) {
    const plan = plans.find(p => p.key === key);
    document.getElementById('selectedPlan').value = key;
    document.getElementById('planSummary').innerHTML =
        `<strong>${plan.label} Plan</strong> – <strong>${parseInt(plan.price).toLocaleString()} ETB</strong> / ${plan.period}`;
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

document.querySelectorAll('.payment-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('border-success', 'bg-success', 'bg-opacity-10'));
        this.nextElementSibling.classList.add('border-success', 'bg-success', 'bg-opacity-10');
    });
});
</script>
