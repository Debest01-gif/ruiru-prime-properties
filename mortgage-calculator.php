<?php
/**
 * Mortgage Calculator Page
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Mortgage Calculator';
$prefilledPrice = (float)($_GET['price'] ?? 5000000);

require_once 'includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="section-tag" style="display:inline-flex;margin-bottom:12px;"><i class="fas fa-calculator"></i> Financial Tools</div>
        <h1>Mortgage <span class="text-gold">Calculator</span></h1>
        <p style="color:var(--text-secondary);max-width:560px;margin:12px auto 0;">
            Calculate your monthly mortgage repayments for any property in Ruiru. Powered for the Kenyan market.
        </p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Mortgage Calculator</span>
        </div>
    </div>
</section>

<section class="section-padding">
<div class="container">
    <div class="calculator-grid">

        <!-- Input Panel -->
        <div class="calculator-card" data-aos="fade-right">
            <h3 style="margin-bottom:28px;"><i class="fas fa-sliders-h text-gold"></i> Your Loan Details</h3>

            <form id="mortgageForm">

                <!-- Property Value -->
                <div class="form-group">
                    <label style="display:flex;justify-content:space-between;">
                        <span>Property Value (KES)</span>
                        <strong style="color:var(--primary);" id="loanDisplay">KES 5,000,000</strong>
                    </label>
                    <input type="range" class="range-slider" id="loanAmount"
                           min="500000" max="100000000" step="100000" value="<?= $prefilledPrice ?>">
                    <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:var(--text-muted);margin-top:4px;">
                        <span>KES 500K</span>
                        <span>KES 100M</span>
                    </div>
                </div>

                <!-- Down Payment -->
                <div class="form-group">
                    <label style="display:flex;justify-content:space-between;">
                        <span>Down Payment</span>
                        <strong style="color:var(--primary);" id="downDisplay">20%</strong>
                    </label>
                    <input type="range" class="range-slider" id="downPayment"
                           min="10" max="60" step="5" value="20">
                    <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:var(--text-muted);margin-top:4px;">
                        <span>10%</span>
                        <span>60%</span>
                    </div>
                </div>

                <!-- Interest Rate -->
                <div class="form-group">
                    <label style="display:flex;justify-content:space-between;">
                        <span>Annual Interest Rate</span>
                        <strong style="color:var(--primary);" id="rateDisplay">13%</strong>
                    </label>
                    <input type="range" class="range-slider" id="interestRate"
                           min="6" max="25" step="0.5" value="13">
                    <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:var(--text-muted);margin-top:4px;">
                        <span>6%</span>
                        <span>25%</span>
                    </div>
                    <p style="color:var(--text-muted);font-size:0.78rem;margin-top:4px;">
                        *Kenya average mortgage rate ranges from 12-16% p.a.
                    </p>
                </div>

                <!-- Loan Term -->
                <div class="form-group">
                    <label style="display:flex;justify-content:space-between;">
                        <span>Loan Term</span>
                        <strong style="color:var(--primary);" id="termDisplay">20 years</strong>
                    </label>
                    <input type="range" class="range-slider" id="loanTerm"
                           min="5" max="30" step="1" value="20">
                    <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:var(--text-muted);margin-top:4px;">
                        <span>5 years</span>
                        <span>30 years</span>
                    </div>
                </div>

            </form>

            <!-- Tips -->
            <div style="background:rgba(212,168,67,0.08);border:1px solid rgba(212,168,67,0.2);border-radius:12px;padding:16px;margin-top:20px;">
                <h4 style="font-size:0.9rem;margin-bottom:10px;"><i class="fas fa-lightbulb text-gold"></i> Quick Tips</h4>
                <ul style="list-style:none;color:var(--text-secondary);font-size:0.83rem;line-height:1.9;">
                    <li><i class="fas fa-check text-gold"></i> A higher down payment reduces monthly repayments</li>
                    <li><i class="fas fa-check text-gold"></i> Most Kenyan banks offer 15-25 year mortgages</li>
                    <li><i class="fas fa-check text-gold"></i> KCB, NCBA, Co-op Bank offer home loans in Kenya</li>
                    <li><i class="fas fa-check text-gold"></i> Budget for stamp duty (2-4%) & legal fees (~4%)</li>
                </ul>
            </div>
        </div>

        <!-- Results Panel -->
        <div data-aos="fade-left">
            <div class="calculator-card" style="margin-bottom:24px;">
                <h3 style="margin-bottom:28px;"><i class="fas fa-chart-pie text-gold"></i> Your Estimates</h3>

                <!-- Monthly Payment Hero -->
                <div class="calc-result" style="margin-bottom:24px;">
                    <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:8px;text-transform:uppercase;letter-spacing:1px;">Monthly Repayment</p>
                    <div class="calc-monthly" id="monthlyPayment">KES 0</div>
                    <p style="color:var(--text-secondary);font-size:0.82rem;margin-top:8px;">*Approximate figure. Contact your bank for exact rates.</p>
                </div>

                <!-- Breakdown -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <?php
                    $results = [
                        ['Loan Principal', 'loanPrincipal', 'fa-home', '#3b82f6'],
                        ['Total Interest', 'totalInterest', 'fa-percentage', '#ef4444'],
                        ['Total Payment', 'totalPayment', 'fa-money-bill-wave', '#22c55e'],
                    ];
                    ?>
                    <div style="background:rgba(255,255,255,0.04);border:1px solid var(--glass-border);border-radius:12px;padding:16px;">
                        <p style="color:var(--text-muted);font-size:0.75rem;margin-bottom:6px;text-transform:uppercase;letter-spacing:1px;">Loan Principal</p>
                        <strong style="color:#3b82f6;font-size:1.05rem;" id="loanPrincipal">KES 0</strong>
                    </div>
                    <div style="background:rgba(255,255,255,0.04);border:1px solid var(--glass-border);border-radius:12px;padding:16px;">
                        <p style="color:var(--text-muted);font-size:0.75rem;margin-bottom:6px;text-transform:uppercase;letter-spacing:1px;">Total Interest</p>
                        <strong style="color:#ef4444;font-size:1.05rem;" id="totalInterest">KES 0</strong>
                    </div>
                    <div style="background:rgba(255,255,255,0.04);border:1px solid var(--glass-border);border-radius:12px;padding:16px;grid-column:span 2;">
                        <p style="color:var(--text-muted);font-size:0.75rem;margin-bottom:6px;text-transform:uppercase;letter-spacing:1px;">Total Amount Repayable</p>
                        <strong style="color:#22c55e;font-size:1.2rem;" id="totalPayment">KES 0</strong>
                    </div>
                </div>

                <div style="margin-top:24px;">
                    <a href="contact.php" class="btn btn-primary" style="width:100%;justify-content:center;border-radius:12px;">
                        <i class="fas fa-user-tie"></i> Talk to Our Mortgage Expert
                    </a>
                </div>
            </div>

            <!-- Banks -->
            <div class="calculator-card">
                <h4 style="margin-bottom:16px;"><i class="fas fa-university text-gold"></i> Popular Kenyan Mortgage Lenders</h4>
                <?php
                $banks = [
                    ['KCB Bank Kenya', '13%', '25 years', 'From KES 500K'],
                    ['NCBA Bank', '12.5%', '20 years', 'From KES 1M'],
                    ['Co-operative Bank', '13.5%', '20 years', 'From KES 1M'],
                    ['Equity Bank', '14%', '20 years', 'From KES 500K'],
                    ['Absa Kenya', '13%', '25 years', 'From KES 1M'],
                ];
                foreach ($banks as $bank):
                ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--glass-border);">
                    <div>
                        <strong style="font-size:0.9rem;"><?= $bank[0] ?></strong>
                        <span style="color:var(--text-secondary);font-size:0.78rem;display:block;"><?= $bank[3] ?></span>
                    </div>
                    <div style="text-align:right;">
                        <span style="color:var(--primary);font-weight:700;font-size:0.9rem;"><?= $bank[1] ?></span>
                        <span style="color:var(--text-muted);font-size:0.75rem;display:block;">Max <?= $bank[2] ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <p style="color:var(--text-muted);font-size:0.75rem;margin-top:12px;">*Rates are approximate. Contact banks for actual current rates.</p>
            </div>
        </div>
    </div>

    <!-- Browse Properties CTA -->
    <div style="margin-top:60px;text-align:center;padding:60px;background:var(--glass-bg);backdrop-filter:blur(10px);border:1px solid var(--glass-border);border-radius:var(--radius-lg);" data-aos="fade-up">
        <h2 style="margin-bottom:12px;">Ready to Find Your <span class="text-gold">Dream Property?</span></h2>
        <p style="color:var(--text-secondary);max-width:500px;margin:0 auto 28px;">
            Now that you know your budget, let us help you find the perfect property in Ruiru.
        </p>
        <a href="properties.php" class="btn btn-primary btn-lg">
            <i class="fas fa-search"></i> Browse Properties
        </a>
    </div>

</div>
</section>

<?php require_once 'includes/footer.php'; ?>
