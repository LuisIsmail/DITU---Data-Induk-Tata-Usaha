    </main><!-- end page-content -->
  </div><!-- end main-content -->
</div><!-- end app-layout -->

<!-- Session Timeout Modal -->
<div id="session-timeout-modal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.55);backdrop-filter:blur(8px);justify-content:center;align-items:center;transition:opacity .3s ease">
  <div style="background:var(--bg-card);border-radius:24px;padding:48px 40px;max-width:420px;width:90%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,.25);border:1px solid var(--border);transform:scale(.9);transition:transform .3s cubic-bezier(.23,1,.32,1);position:relative;overflow:hidden">
    <div style="position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--warning),var(--danger));border-radius:24px 24px 0 0"></div>
    <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#fef3c7,#fde68a);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(217,119,6,.2)"><i data-lucide="alert-triangle" style="width:36px;height:36px;color:#d97706"></i></div>
    <h3 style="font-size:1.2rem;font-weight:700;color:var(--text);margin:0 0 10px">Sesi Akan Berakhir</h3>
    <p style="font-size:.88rem;color:var(--text-muted);margin:0 0 16px;line-height:1.6">Sesi Anda akan berakhir dalam <strong id="session-countdown" style="color:var(--warning);font-size:1rem">60</strong> detik karena tidak ada aktivitas.</p>
    <p style="font-size:.82rem;color:var(--text-muted);margin:0 0 24px">Klik tombol di bawah untuk tetap login.</p>
    <button id="session-stay-btn" style="width:100%;padding:14px;border:none;border-radius:12px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;font-size:.92rem;font-weight:600;cursor:pointer;transition:all .2s;box-shadow:0 4px 12px rgba(37,99,235,.3)" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(37,99,235,.4)'" onmouseout="this.style.transform='';this.style.boxShadow='0 4px 12px rgba(37,99,235,.3)'"><i data-lucide="shield-check"></i> Tetap Login</button>
  </div>
</div>

<!-- Session Expired Modal -->
<div id="session-expired-modal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.65);backdrop-filter:blur(10px);justify-content:center;align-items:center;transition:opacity .3s ease">
  <div style="background:var(--bg-card);border-radius:24px;padding:48px 40px;max-width:420px;width:90%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,.3);border:1px solid var(--border);transform:scale(.9);transition:transform .3s cubic-bezier(.23,1,.32,1);position:relative;overflow:hidden">
    <div style="position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--danger),#991b1b);border-radius:24px 24px 0 0"></div>
    <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#fee2e2,#fecaca);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(220,38,38,.2)"><i data-lucide="lock" style="width:36px;height:36px;color:#dc2626"></i></div>
    <h3 style="font-size:1.2rem;font-weight:700;color:var(--text);margin:0 0 10px">Sesi Telah Berakhir</h3>
    <p style="font-size:.88rem;color:var(--text-muted);margin:0 0 8px;line-height:1.6">Sesi Anda telah berakhir karena tidak ada aktivitas.</p>
    <p style="font-size:.88rem;color:var(--text-muted);margin:0 0 24px">Silahkan login kembali untuk mengakses sistem.</p>
    <div style="width:48px;height:48px;border:3px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:sessionSpin 1s linear infinite;margin:0 auto 16px"></div>
    <p style="font-size:.8rem;color:var(--text-muted);margin:0">Mengalihkan ke halaman login...</p>
  </div>
</div>

<style>
@keyframes sessionSpin { to { transform: rotate(360deg); } }
#session-timeout-modal.show, #session-expired-modal.show {
  display: flex !important;
  opacity: 1 !important;
}
#session-timeout-modal.show > div, #session-expired-modal.show > div {
  transform: scale(1);
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script src="assets/js/app.js?v=<?= filemtime(__DIR__ . '/../assets/js/app.js') ?>"></script>
<?php if (isset($extraJs)) echo $extraJs; ?>
<?php if (isLoggedIn()): ?>
<script>
(function(){
  const TIMEOUT_MIN = <?= (int)($settings['session_timeout'] ?? 15) ?>;
  const TIMEOUT_MS  = TIMEOUT_MIN * 60 * 1000;
  const WARN_MS     = TIMEOUT_MS - 60000;
  let idleTimer, warnTimer, countdownTimer;
  let countdownSec = 60;

  function showSessionWarning(){
    countdownSec = 60;
    const modal = document.getElementById('session-timeout-modal');
    const countdown = document.getElementById('session-countdown');
    modal.classList.add('show');
    countdown.textContent = countdownSec;

    countdownTimer = setInterval(() => {
      countdownSec--;
      countdown.textContent = countdownSec;
      if (countdownSec <= 10) {
        countdown.style.color = 'var(--danger)';
        countdown.style.fontSize = '1.2rem';
      }
      if (countdownSec <= 0) {
        clearInterval(countdownTimer);
        modal.classList.remove('show');
        showSessionExpired();
      }
    }, 1000);

    warnTimer = setTimeout(() => {
      clearInterval(countdownTimer);
      modal.classList.remove('show');
      showSessionExpired();
    }, 61000);
  }

  function showSessionExpired(){
    const modal = document.getElementById('session-expired-modal');
    modal.classList.add('show');
    setTimeout(() => { window.location.href = 'logout.php'; }, 4000);
  }

  function stayLoggedIn(){
    clearInterval(countdownTimer);
    clearTimeout(warnTimer);
    var modal = document.getElementById('session-timeout-modal');
    modal.classList.remove('show');
    modal.style.opacity = '0';
    setTimeout(function(){ modal.style.display = 'none'; }, 300);
    resetIdle();
  }

  document.getElementById('session-stay-btn').addEventListener('click', stayLoggedIn);

  function resetIdle(){
    clearTimeout(idleTimer);
    clearTimeout(warnTimer);
    clearInterval(countdownTimer);
    idleTimer = setTimeout(showSessionWarning, WARN_MS);
  }

  ['mousemove','keydown','click','scroll','touchstart'].forEach(function(e){
    document.addEventListener(e, resetIdle, {passive:true});
  });
  resetIdle();
})();
</script>
<?php endif; ?>
<script>lucide.createIcons();</script>
</body>
</html>

