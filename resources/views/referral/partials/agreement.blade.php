@php($agreementDark = $dark ?? true)
<div class="referral-agreement-control {{ $agreementDark ? 'referral-agreement-control--dark' : 'referral-agreement-control--light' }}">
    <style>
        .referral-agreement-control{border:1px solid rgba(148,163,184,.28);border-radius:.75rem;padding:1rem 1.1rem}.referral-agreement-control--dark{background:rgba(59,130,246,.06);color:#cbd5e1}.referral-agreement-control--light{background:#eef2ff;color:#334155;border-color:#c7d2fe}.referral-agreement__trigger{padding:0;border:0;background:none;color:#60a5fa;font:inherit;font-weight:800;text-decoration:underline;text-underline-offset:3px;cursor:pointer}.referral-agreement-control--light .referral-agreement__trigger{color:#4338ca}.referral-agreement__accepted{display:none;margin-left:.5rem;color:#34d399;font-size:.78rem;font-weight:800}.referral-agreement__backdrop{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:1rem;background:rgba(2,6,23,.78);backdrop-filter:blur(4px)}.referral-agreement__backdrop.is-open{display:flex}.referral-agreement{display:flex;max-height:min(90vh,52rem);width:min(100%,52rem);flex-direction:column;border:1px solid rgba(148,163,184,.28);border-radius:.9rem;overflow:hidden;background:#071024;color:#dbeafe;box-shadow:0 24px 80px rgba(0,0,0,.5)}.referral-agreement__header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1rem 1.2rem;border-bottom:1px solid rgba(148,163,184,.2);background:#0f172a}.referral-agreement__heading{display:flex;flex-wrap:wrap;align-items:center;gap:.65rem}.referral-agreement__title{margin:0;font-size:1.05rem;font-weight:800}.referral-agreement__version{display:inline-flex;padding:.3rem .55rem;border-radius:999px;background:rgba(59,130,246,.16);color:#60a5fa;font-size:.7rem;font-weight:800}.referral-agreement__close{border:0;background:transparent;color:#94a3b8;font-size:1.75rem;line-height:1;cursor:pointer}.referral-agreement__body{min-height:0;overflow:auto;padding:1.2rem;scrollbar-gutter:stable}.referral-agreement__body h3{margin:1.25rem 0 .45rem;font-size:.93rem;font-weight:800;color:#fff}.referral-agreement__body h3:first-child{margin-top:0}.referral-agreement__body p,.referral-agreement__body li{font-size:.84rem;line-height:1.65}.referral-agreement__body p{margin:.45rem 0}.referral-agreement__body ul{margin:.45rem 0 .7rem 1.25rem;list-style:disc}.referral-agreement__example{margin:.75rem 0;padding:.85rem 1rem;border-left:3px solid #60a5fa;border-radius:.4rem;background:rgba(59,130,246,.09)}.referral-agreement__footer{padding:1rem 1.2rem;border-top:1px solid rgba(148,163,184,.2);background:#0f172a}.referral-agreement__hint{margin:0 0 .65rem;color:#94a3b8;font-size:.75rem}.referral-agreement__accept{width:100%;border:0;border-radius:.55rem;padding:.75rem 1rem;background:#4f46e5;color:#fff;font-weight:800;cursor:pointer}.referral-agreement__accept:disabled{cursor:not-allowed;opacity:.42}
    </style>
    <input type="hidden" name="agreement_accepted" value="1" disabled>
    <span>Please </span><button type="button" class="referral-agreement__trigger">read and accept the Client Referral Agreement</button><span> before submitting.</span>
    <span class="referral-agreement__accepted" role="status">&#10003; Agreement accepted</span>

    <div class="referral-agreement__backdrop" role="dialog" aria-modal="true" aria-labelledby="referral-agreement-title">
        <section class="referral-agreement">
            <header class="referral-agreement__header">
                <div>
                    <div class="referral-agreement__heading">
                        <h2 id="referral-agreement-title" class="referral-agreement__title">Client Referral Agreement</h2>
                    </div>
                    <p style="margin:.3rem 0 0;font-size:.8rem;color:#94a3b8">Scroll to the bottom to enable acceptance.</p>
                </div>
                <button type="button" class="referral-agreement__close" aria-label="Close agreement">&times;</button>
            </header>
            <div class="referral-agreement__body" tabindex="0">
        <h3>1. Purpose</h3>
        <p>This Client Referral Agreement ("Agreement") governs the referral of prospective business clients, hiring requirements, recruitment assignments and related business opportunities to Simply Hiree through its digital platform.</p>
        <p>By digitally accepting this Agreement, the Referral Partner confirms that they have read, understood and agreed to the terms mentioned herein.</p>

        <h3>2. Referral Partner</h3>
        <p>Any individual, professional, company, agency, consultant or approved partner having a verified Simply Hiree account may participate in the Client Referral Program, subject to approval by Simply Hiree.</p>

        <h3>3. What Can Be Referred</h3>
        <p>The Referral Partner may refer:</p>
        <ul><li>New corporate/business clients.</li><li>Companies having recruitment or staffing requirements.</li><li>Permanent hiring requirements.</li><li>Contract/temporary staffing requirements.</li><li>Manpower requirements.</li><li>Multiple job openings or hiring projects.</li><li>Other recruitment or staffing-related business opportunities accepted by Simply Hiree.</li></ul>
        <p>The Referral Partner may introduce the client/lead to Simply Hiree and provide the available business contact and requirement details.</p>

        <h3>4. Role of Simply Hiree</h3>
        <p>After receiving a valid referral, Simply Hiree may independently:</p>
        <ul><li>Contact and communicate with the referred client.</li><li>Understand and validate the client's requirements.</li><li>Discuss commercials and service terms.</li><li>Execute recruitment, staffing or other agreed services.</li><li>Source and manage candidates.</li><li>Coordinate interviews and selection.</li><li>Complete joining and documentation processes.</li><li>Raise invoices and collect payments from the client.</li><li>Manage the overall client relationship and service delivery.</li></ul>
        <p>The Referral Partner is not required to independently execute the hiring assignment unless separately agreed in writing.</p>

        <h3>5. Eligible Referral</h3>
        <p>A referral will qualify under this Agreement only if:</p>
        <ul><li>The client is a new client for Simply Hiree;</li><li>The client was not already registered, contracted, engaged or actively under discussion with Simply Hiree before the referral;</li><li>The referral is properly recorded through the Simply Hiree platform/CRM or otherwise acknowledged by Simply Hiree;</li><li>The referral is approved by Simply Hiree.</li></ul>
        <p>Simply Hiree reserves the right to reject a referral where the client is already known, registered, active or under discussion with Simply Hiree.</p>

        <h3>6. Referral Commission / Payout</h3>
        <p>For every eligible referred client that results in business with Simply Hiree, the approved Referral Partner shall be eligible to receive 10% to 20% of Simply Hiree's Net Service Revenue, as determined and approved by Simply Hiree for the applicable referral arrangement.</p>
        <p>The applicable percentage may depend upon the client, business volume, nature of services, commercial arrangement and other factors.</p>
        <p>The applicable referral percentage may be communicated to or displayed for the Referral Partner through the Simply Hiree digital platform, CRM, dashboard, email or other written/digital communication.</p>

        <h3>7. Meaning of Net Service Revenue</h3>
        <p>For the purpose of calculating referral commission:</p>
        <p><strong>Net Service Revenue = Actual service fee/revenue received by Simply Hiree from the referred client, excluding applicable taxes and pass-through costs.</strong></p>
        <p>The following shall not form part of Net Service Revenue:</p>
        <ul><li>GST and other indirect taxes;</li><li>TDS or statutory deductions;</li><li>Candidate/employee payroll or salary costs;</li><li>Reimbursements;</li><li>Refunds, reversals or credit notes;</li><li>Third-party expenses;</li><li>Government/statutory charges;</li><li>Other pass-through expenses;</li><li>Amounts not actually received or finally realized by Simply Hiree.</li></ul>
        <div class="referral-agreement__example"><strong>Example</strong><p>If Simply Hiree receives ₹1,00,000 as eligible Net Service Revenue from a referred client:</p><p>At 10% referral payout = ₹10,000<br>At 15% referral payout = ₹15,000<br>At 20% referral payout = ₹20,000</p><p>The applicable percentage will be the percentage approved for that referral.</p></div>

        <h3>8. Recurring Referral Commission</h3>
        <p>Where recurring commission has been approved for a referral, the Referral Partner may continue to receive the applicable referral commission for eligible revenue generated from the referred client while:</p>
        <ul><li>The client remains active with Simply Hiree;</li><li>Simply Hiree continues to receive payment from the client;</li><li>The referral remains eligible under this Agreement; and</li><li>No exclusion, suspension or termination condition applies.</li></ul>
        <p>Simply Hiree may discontinue recurring commission for future transactions where the referral becomes ineligible under this Agreement.</p>

        <h3>9. Payment Terms</h3>
        <p>Referral payouts shall be processed only after:</p>
        <ul><li>The referred client has made the applicable payment;</li><li>The payment has been cleared and realized by Simply Hiree;</li><li>The relevant revenue has been verified by the finance/accounts team; and</li><li>Any applicable statutory deductions or adjustments have been considered.</li></ul>
        <p>Payouts will generally be processed on a monthly cycle or as per the payment cycle communicated by Simply Hiree.</p>

        <h3>10. Duplicate Referrals</h3>
        <p>If the same client is referred by multiple persons, the first valid and approved referral recorded in the Simply Hiree CRM/platform shall generally be considered the eligible referral.</p>
        <p>Simply Hiree's CRM records and internal verification shall be treated as the basis for determining referral ownership.</p>

        <h3>11. Client Relationship</h3>
        <p>The referred client shall remain a client of Simply Hiree for the purpose of the services provided by Simply Hiree.</p>
        <p>The Referral Partner shall not represent themselves as an employee, agent, authorized representative or legal representative of Simply Hiree unless specifically authorized in writing.</p>

        <h3>12. No Guarantee of Business</h3>
        <p>Referral registration does not guarantee:</p>
        <ul><li>Client onboarding;</li><li>Hiring;</li><li>Placement;</li><li>Staffing assignment;</li><li>Revenue generation; or</li><li>Referral commission.</li></ul>
        <p>Commission becomes payable only when the applicable conditions under this Agreement are fulfilled.</p>

        <h3>13. Confidentiality</h3>
        <p>The Referral Partner shall maintain confidentiality of all confidential information received from or relating to Simply Hiree and its clients, including:</p>
        <ul><li>Client information;</li><li>Candidate information;</li><li>Commercial terms;</li><li>Pricing;</li><li>Business requirements;</li><li>Contracts;</li><li>Recruitment data; and</li><li>Other non-public information.</li></ul>
        <p>Such information shall not be disclosed or misused without authorization.</p>

        <h3>14. Non-Circumvention</h3>
        <p>The Referral Partner shall not intentionally bypass Simply Hiree for the purpose of directly contracting with or diverting a referred client for competing recruitment, staffing or substantially similar services.</p>
        <p>Any such conduct may result in suspension or termination of the Referral Partner account and cancellation of future referral eligibility.</p>

        <h3>15. Compliance and Ethical Conduct</h3>
        <p>The Referral Partner shall not:</p>
        <ul><li>Submit false or misleading client information;</li><li>Create duplicate or fraudulent referrals;</li><li>Misrepresent Simply Hiree's services;</li><li>Promise unauthorized pricing or payouts;</li><li>Misuse client or candidate data; or</li><li>Engage in fraudulent or unlawful activities.</li></ul>

        <h3>16. Taxes and Statutory Deductions</h3>
        <p>Referral payouts shall be subject to applicable taxes, TDS and other statutory deductions, wherever applicable.</p>
        <p>The Referral Partner shall be responsible for complying with their own applicable tax and statutory obligations.</p>

        <h3>17. Suspension and Termination</h3>
        <p>Simply Hiree may suspend or terminate the Referral Partner's participation in the program in cases including:</p>
        <ul><li>Fraud or attempted fraud;</li><li>Misuse of the platform;</li><li>Policy violations;</li><li>Misrepresentation;</li><li>Confidentiality breach;</li><li>Non-circumvention violation;</li><li>Submission of false referrals; or</li><li>Any conduct detrimental to Simply Hiree or its clients.</li></ul>
        <p>Eligible and verified commissions earned before termination shall remain payable, subject to applicable verification, adjustments and statutory deductions.</p>

        <h3>18. Modification of Referral Program</h3>
        <p>Simply Hiree may modify the referral program, payout structure, eligibility criteria or operational process from time to time.</p>
        <p>Any such changes shall apply prospectively to new referrals unless otherwise communicated.</p>

        <h3>19. Digital Acceptance</h3>
        <p>The Referral Partner's acceptance through any electronic method, including:</p>
        <ul><li>"I Agree" / "Accept" button;</li><li>Digital checkbox;</li><li>OTP verification;</li><li>Electronic signature;</li><li>Registered email confirmation;</li><li>Platform acceptance; or</li><li>Other authenticated digital confirmation</li></ul>
        <p>shall constitute acceptance of this Agreement.</p>
        <p>The Referral Partner acknowledges that such digital acceptance is intended to have the same commercial effect as acceptance of a physical agreement, subject to applicable Indian law.</p>

        <h3>20. Governing Law and Jurisdiction</h3>
        <p>This Agreement shall be governed by the laws of India.</p>
        <p>Any dispute arising in connection with this Agreement shall be subject to the applicable courts having jurisdiction over Simply Hiree's registered/operational office, subject to applicable law.</p>

        <h3>21. Entire Agreement</h3>
        <p>This Agreement, together with any referral-specific commercial terms communicated and approved by Simply Hiree, constitutes the understanding between Simply Hiree and the Referral Partner regarding the Client Referral Program.</p>
            </div>
            <footer class="referral-agreement__footer">
                <p class="referral-agreement__hint">Please read the complete agreement. The button unlocks when you reach the bottom.</p>
                <button type="button" class="referral-agreement__accept" disabled>I accept</button>
            </footer>
        </section>
    </div>
</div>

<script>
    (() => {
        const control = document.currentScript.previousElementSibling;
        const trigger = control.querySelector('.referral-agreement__trigger');
        const backdrop = control.querySelector('.referral-agreement__backdrop');
        const scrollArea = control.querySelector('.referral-agreement__body');
        const closeButton = control.querySelector('.referral-agreement__close');
        const acceptButton = control.querySelector('.referral-agreement__accept');
        const acceptedInput = control.querySelector('input[name="agreement_accepted"]');
        const acceptedStatus = control.querySelector('.referral-agreement__accepted');
        const form = control.closest('form');

        const updateAcceptState = () => {
            const reachedBottom = scrollArea.scrollTop + scrollArea.clientHeight >= scrollArea.scrollHeight - 4;
            acceptButton.disabled = !reachedBottom;
        };
        const closeModal = () => {
            backdrop.classList.remove('is-open');
            document.body.style.overflow = '';
            trigger.focus();
        };
        trigger.addEventListener('click', () => {
            backdrop.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => {
                scrollArea.focus();
                updateAcceptState();
            });
        });
        scrollArea.addEventListener('scroll', updateAcceptState, { passive: true });
        closeButton.addEventListener('click', closeModal);
        backdrop.addEventListener('click', event => {
            if (event.target === backdrop) closeModal();
        });
        form?.addEventListener('submit', event => {
            if (acceptedInput.disabled) {
                event.preventDefault();
                trigger.click();
            }
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && backdrop.classList.contains('is-open')) closeModal();
        });
        acceptButton.addEventListener('click', () => {
            acceptedInput.disabled = false;
            acceptedStatus.style.display = 'inline';
            closeModal();
            if (form) form.requestSubmit();
        });
    })();
</script>
