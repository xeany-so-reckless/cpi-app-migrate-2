<style>
    /* Badge label jawaban: Selalu / Sering / Kadang-Kadang / Tidak Pernah */
    .lbl-badge { display: inline-block; padding: 3px 11px; border-radius: 99px; font-size: .74rem; font-weight: 700; white-space: nowrap; }
    .lbl-badge.sl { background: #dcfce7; color: #166534; }
    .lbl-badge.sr { background: #dbeafe; color: #1e40af; }
    .lbl-badge.kd { background: #fef3c7; color: #92400e; }
    .lbl-badge.tp { background: #fee2e2; color: #991b1b; }
    .lbl-badge.na { background: #eef0f7; color: #6b7280; }

    /* Tombol Hapus (merah) */
    .btn-hapus { color: var(--danger); border-color: #fecaca; }
    .btn-hapus:hover { background: #fee2e2; }

    /* Halaman detail */
    .det-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
    .det-k { font-size: .7rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; }
    .det-v { font-size: .92rem; font-weight: 600; margin-top: 3px; word-break: break-word; }

    .mat { margin-bottom: 16px; }
    .mat-head { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 8px; }
    .mat-head h2 { margin: 0; display: flex; align-items: center; gap: 8px; }
    .mat-head .kode { background: var(--primary); color: #fff; border-radius: 6px; padding: 1px 8px; font-size: .74rem; }
    .mat-head .spacer { flex: 1; }
    .mat-warn { font-size: .78rem; font-weight: 600; color: var(--danger); }

    .qrow { display: grid; grid-template-columns: 26px 1fr auto; gap: 12px; align-items: center; padding: 10px 8px; border-top: 1px solid #eef0f7; font-size: .86rem; line-height: 1.5; }
    .qrow:first-of-type { border-top: none; }
    .qrow.warn { background: #fef2f2; border-radius: 8px; }
    .qno { width: 24px; height: 24px; border-radius: 50%; background: #eef0f7; color: #6b7280; font-size: .72rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .qrow.warn .qno { background: #fecaca; color: #991b1b; }

    @media (max-width: 700px) {
        .qrow { grid-template-columns: 26px 1fr; }
        .qrow .qans { grid-column: 2; }
    }
</style>
