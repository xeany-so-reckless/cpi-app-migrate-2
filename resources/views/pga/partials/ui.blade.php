<style>
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 16px; border-radius: 10px; border: 1px solid transparent; font-size: .84rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: background .2s ease, border-color .2s ease; }
    .btn .material-symbols-outlined { font-size: 18px; }
    .btn-primary { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: var(--primary-dark); }
    .btn-light { background: #fff; color: var(--text); border-color: var(--line); }
    .btn-light:hover { background: var(--primary-soft); border-color: #c7d2fe; }
    .btn-danger { background: #fff; color: var(--danger); border-color: #fecaca; }
    .btn-danger:hover { background: var(--danger); color: #fff; border-color: var(--danger); }
    .btn.disabled { opacity: .45; pointer-events: none; }
    .btn-sm { padding: 6px 11px; font-size: .78rem; }

    .badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: .72rem; font-weight: 700; white-space: nowrap; }
    .badge.ok { background: #dcfce7; color: #166534; }
    .badge.bad { background: #fee2e2; color: #991b1b; }
    .badge.na { background: #e2e8f0; color: #334155; }
    .badge.edited { background: #fef3c7; color: #92400e; }

    .flash { background: #dcfce7; border: 1px solid #86efac; color: #166534; border-radius: 12px; padding: 12px 16px; font-size: .86rem; margin-bottom: 18px; }
    .flash-err { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }

    .toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 16px; }
    .toolbar .spacer { flex: 1; }

    .filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; align-items: end; }
    .filters .fgroup label { display: block; font-size: .7rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 5px; }
    .in { width: 100%; height: 40px; border: 1px solid var(--line); border-radius: 10px; padding: 0 12px; font-size: .86rem; background: #fff; color: var(--text); }
    textarea.in { height: auto; min-height: 90px; padding: 10px 12px; resize: vertical; }
    .in:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
    .fgroup .err, .err { color: var(--danger); font-size: .76rem; margin-top: 4px; }

    .table-wrap { overflow-x: auto; }
    table.tbl { width: 100%; border-collapse: collapse; font-size: .85rem; min-width: 760px; }
    table.tbl th { text-align: left; font-size: .7rem; text-transform: uppercase; letter-spacing: .4px; color: var(--muted); padding: 10px 12px; border-bottom: 1px solid var(--line); white-space: nowrap; }
    table.tbl td { padding: 11px 12px; border-bottom: 1px solid #eef0f7; vertical-align: middle; }
    table.tbl tr:hover td { background: #fafbff; }
    table.tbl a.lnk { color: var(--primary); font-weight: 700; text-decoration: none; }
    table.tbl a.lnk:hover { text-decoration: underline; }
    .muted { color: var(--muted); }
    .empty { text-align: center; color: var(--muted); padding: 36px 12px; font-size: .88rem; }

    .pager { display: flex; align-items: center; justify-content: center; gap: 14px; margin-top: 16px; font-size: .82rem; color: var(--muted); }
</style>
