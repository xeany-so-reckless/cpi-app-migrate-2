{{-- resources/views/components/logout-warning.blade.php --}}
{{-- Mandiri: tanpa Tailwind / Font Awesome. Pemakaian: <x-logout-warning /> --}}

<style>
  .lw-card {
    display: flex; align-items: flex-start; gap: 12px;
    margin-top: 20px; padding: 14px;
    background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 12px;
    box-shadow: 0 0 0 1px rgba(245,158,11,.25), 0 4px 16px rgba(245,158,11,.18);
    text-align: left; font-family: 'Inter', system-ui, sans-serif;
  }
  .lw-icon {
    flex-shrink: 0; width: 36px; height: 36px; border-radius: 50%;
    background: #fef3c7; color: #d97706;
    display: flex; align-items: center; justify-content: center;
    animation: lw-pulse 2.4s ease-in-out infinite;
  }
  .lw-icon svg { width: 20px; height: 20px; }
  .lw-text { min-width: 0; margin: 0; font-size: 0.8rem; line-height: 1.5; color: #78350f; }
  .lw-text strong { font-weight: 700; }
  @keyframes lw-pulse {
    0%, 100% { transform: scale(1);    filter: drop-shadow(0 0 0 rgba(245,158,11,0)); }
    50%      { transform: scale(1.12); filter: drop-shadow(0 0 6px rgba(245,158,11,.8)); }
  }
  @media (min-width: 640px) { .lw-text { font-size: 0.875rem; } }
  @media (prefers-reduced-motion: reduce) { .lw-icon { animation: none; } }
</style>

<div class="lw-card" role="note">
  <div class="lw-icon" aria-hidden="true">
    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4Zm-1 14-3.5-3.5 1.41-1.41L11 12.17l4.59-4.58L17 9l-6 6Z"/></svg>
  </div>
  <p class="lw-text"><strong>Demi keamanan akun,</strong> jangan lupa logout setelah selesai!</p>
</div>