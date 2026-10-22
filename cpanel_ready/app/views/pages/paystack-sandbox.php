<?php
/**
 * Paystack Developer Sandbox Checkout View
 * Rendered when placeholder keys are active to provide full local demo functionality.
 *
 * @var array $booking
 * @var array $payment
 * @var array $package
 */
use App\Core\Security;

$amount = (float)($payment['amount'] ?? 0);
$formattedAmount = '₦' . number_format($amount, 2);
?>

<div style="min-height: 80vh; background: #F8FAFC; display: flex; align-items: center; justify-content: center; padding: 40px 16px;">
  <div style="width: 100%; max-width: 480px; background: #FFFFFF; border-radius: 16px; box-shadow: 0 20px 40px -15px rgba(0,0,0,0.1), 0 0 0 1px rgba(0,0,0,0.05); overflow: hidden;">
    
    <!-- Paystack Header -->
    <div style="background: #081E3E; padding: 24px; color: #FFFFFF; position: relative;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 8px;">
          <!-- Paystack Icon -->
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
            <rect width="24" height="24" rx="6" fill="#0BA4DB"/>
            <path d="M6 8h12M6 12h12M6 16h8" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/>
          </svg>
          <span style="font-weight: 700; font-size: 16px; letter-spacing: -0.3px;">paystack</span>
        </div>
        <span style="font-size: 11px; font-weight: 600; background: rgba(11, 164, 219, 0.25); color: #38BDF8; padding: 3px 8px; border-radius: 12px; border: 1px solid rgba(56, 189, 248, 0.4);">
          TEST SANDBOX
        </span>
      </div>

      <div style="margin-top: 8px;">
        <div style="font-size: 12px; color: #94A3B8; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Pay To</div>
        <div style="font-size: 17px; font-weight: 700; color: #FFFFFF; margin-top: 2px;">LAD Global Travels and Tours</div>
        <div style="font-size: 12px; color: #CBD5E1; margin-top: 4px;"><?= Security::e($package['title'] ?? 'Tour Package') ?></div>
      </div>

      <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: baseline;">
        <span style="font-size: 13px; color: #94A3B8;">Total Amount:</span>
        <span style="font-size: 24px; font-weight: 800; color: #38BDF8;"><?= $formattedAmount ?></span>
      </div>
    </div>

    <!-- Body -->
    <div style="padding: 24px;">
      
      <!-- Simulated Card Interface -->
      <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <span style="font-size: 12px; font-weight: 700; color: #334155; text-transform: uppercase;">Simulated Test Card</span>
          <div style="display: flex; gap: 6px;">
            <span style="font-size: 10px; font-weight: 700; background: #E0E7FF; color: #3730A3; padding: 2px 6px; border-radius: 4px;">VISA</span>
            <span style="font-size: 10px; font-weight: 700; background: #FEE2E2; color: #991B1B; padding: 2px 6px; border-radius: 4px;">MC</span>
            <span style="font-size: 10px; font-weight: 700; background: #DCFCE7; color: #166534; padding: 2px 6px; border-radius: 4px;">VERVE</span>
          </div>
        </div>

        <div style="margin-bottom: 10px;">
          <label style="display: block; font-size: 11px; color: #64748B; font-weight: 600; margin-bottom: 4px;">Card Number</label>
          <input type="text" readonly value="4084 0840 8408 4081" 
                 style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 8px; font-family: monospace; font-size: 14px; font-weight: 600; color: #0F172A;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
          <div>
            <label style="display: block; font-size: 11px; color: #64748B; font-weight: 600; margin-bottom: 4px;">Valid Thru</label>
            <input type="text" readonly value="12/30" 
                   style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 8px; font-family: monospace; font-size: 14px; color: #0F172A;">
          </div>
          <div>
            <label style="display: block; font-size: 11px; color: #64748B; font-weight: 600; margin-bottom: 4px;">CVV</label>
            <input type="text" readonly value="408" 
                   style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 8px; font-family: monospace; font-size: 14px; color: #0F172A;">
          </div>
        </div>
      </div>

      <!-- Customer Reference Summary -->
      <div style="font-size: 12px; color: #64748B; margin-bottom: 20px; line-height: 1.5; background: #F1F5F9; padding: 12px; border-radius: 8px;">
        <div><strong>Booking Reference:</strong> <?= Security::e($booking['reference']) ?></div>
        <div><strong>Customer:</strong> <?= Security::e($booking['customer_name']) ?> (<?= Security::e($booking['customer_email']) ?>)</div>
        <div><strong>Payment Ref:</strong> <?= Security::e($payment['payment_reference']) ?></div>
      </div>

      <!-- Action Buttons -->
      <a href="/paystack/callback?reference=<?= urlencode($payment['payment_reference']) ?>" 
         id="btnAuthorizePayment"
         style="display: block; width: 100%; box-sizing: border-box; background: #0BA4DB; color: #FFFFFF; text-align: center; text-decoration: none; padding: 14px; border-radius: 8px; font-weight: 700; font-size: 15px; margin-bottom: 12px; transition: background 0.2s;"
         onmouseover="this.style.background='#0891B2'" onmouseout="this.style.background='#0BA4DB'">
        ✓ Authorize Payment (<?= $formattedAmount ?>)
      </a>

      <a href="/booking/<?= urlencode($booking['reference']) ?>" 
         style="display: block; width: 100%; box-sizing: border-box; background: transparent; color: #64748B; text-align: center; text-decoration: none; padding: 10px; font-size: 13px; font-weight: 600;"
         onmouseover="this.style.color='#0F172A'" onmouseout="this.style.color='#64748B'">
        Cancel and Return to Booking
      </a>

      <!-- Developer Info -->
      <div style="margin-top: 20px; padding-top: 16px; border-top: 1px dashed #E2E8F0; text-align: center; font-size: 11px; color: #94A3B8; line-height: 1.4;">
        <span style="font-weight: 600; color: #64748B;">Simulator Notice:</span> This simulated gateway enables full end-to-end testing while placeholder API keys are active. Configure your live/test Paystack API keys in <a href="/admin/settings" style="color: #0BA4DB; text-decoration: none; font-weight: 600;">Admin Settings</a> to connect directly to Paystack servers.
      </div>

    </div>

  </div>
</div>
