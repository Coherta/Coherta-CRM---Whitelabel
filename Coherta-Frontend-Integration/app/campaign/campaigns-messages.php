<?php
$PageTitle = 'Messages';
include 'campaigns-shared.php';
?>
<style>
    .campaign-messages-app .threads-layout{
        display:grid;
        grid-template-columns:380px minmax(0,1fr);
        gap:20px;
        min-height:760px;
    }
    .campaign-messages-app .mailbox-switcher{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:12px;
        margin:0 0 16px;
    }
    .campaign-messages-app .mailbox-tab{
        appearance:none;
        border:1px solid var(--line);
        border-radius:18px;
        background:#fff;
        color:#344054;
        min-height:74px;
        padding:13px 16px;
        display:grid;
        grid-template-columns:minmax(0,1fr) auto;
        align-items:center;
        gap:8px;
        text-align:left;
        cursor:pointer;
        box-shadow:0 12px 28px rgba(15,23,42,.05);
        transition:background .16s ease,border-color .16s ease,box-shadow .16s ease;
    }
    .campaign-messages-app .mailbox-tab span{
        display:block;
        font-size:13px;
        font-weight:950;
        letter-spacing:.02em;
        text-transform:uppercase;
        color:#475467;
    }
    .campaign-messages-app .mailbox-tab strong{
        grid-row:1 / span 2;
        grid-column:2;
        min-width:48px;
        height:48px;
        border-radius:50%;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        background:#f2f4f7;
        color:#101828;
        font-size:20px;
        font-weight:950;
    }
    .campaign-messages-app .mailbox-tab small{
        display:block;
        color:#667085;
        font-size:12px;
        line-height:1.35;
    }
    .campaign-messages-app .mailbox-tab.active{
        border-color:rgba(10,102,194,.30);
        background:linear-gradient(180deg,#eef6ff,#fff);
        box-shadow:0 16px 34px rgba(10,102,194,.12);
    }
    .campaign-messages-app .mailbox-tab.active span{
        color:#0a66c2;
    }
    .campaign-messages-app .mailbox-tab.active strong{
        color:#fff;
    }
    .campaign-messages-app .mailbox-tab[data-mailbox="outbox"] strong{
        background:#fff6e8;
        color:#b54708;
    }
    .campaign-messages-app .mailbox-tab[data-mailbox="outbox"].active{
        border-color:rgba(247,144,9,.34);
        background:linear-gradient(180deg,#fff7e8,#fff);
        box-shadow:0 16px 34px rgba(247,144,9,.13);
    }
    .campaign-messages-app .mailbox-tab[data-mailbox="outbox"].active span{
        color:#b54708;
    }
    .campaign-messages-app .mailbox-tab[data-mailbox="outbox"].active strong{
        background:#f79009;
        color:#fff;
    }
    .campaign-messages-app .thread-list-panel,
    .campaign-messages-app .thread-view{
        border:1px solid var(--line);
        border-radius:24px;
        background:#fff;
        overflow:hidden;
        min-height:760px;
        box-shadow:0 18px 45px rgba(15,23,42,.06);
    }
    .campaign-messages-app .thread-list-panel{
        display:flex;
        flex-direction:column;
        min-height:0;
    }
    .campaign-messages-app .thread-list-toolbar{
        padding:18px 18px 14px;
        border-bottom:1px solid var(--line);
        background:
                radial-gradient(circle at top right,rgba(10,102,194,.10),transparent 34%),
                linear-gradient(180deg,#fff,#f8fbff);
    }
    .campaign-messages-app .thread-list-toolbar strong{
        display:flex;
        align-items:center;
        gap:9px;
        font-size:18px;
        color:#101828;
        letter-spacing:-.03em;
    }
    .campaign-messages-app .linkedin-mini-mark{
        width:26px;
        height:26px;
        border-radius:5px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        background:#0a66c2;
        color:#fff;
        font-size:14px;
        font-weight:950;
        letter-spacing:-.02em;
    }
    .campaign-messages-app .thread-list-toolbar span{
        display:block;
        margin-top:5px;
        color:var(--muted);
        font-size:13px;
        line-height:1.45;
    }
    .campaign-messages-app .thread-filter-row,
    .campaign-messages-app .thread-list-summary{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-top:13px;
    }
    .campaign-messages-app .thread-filter-btn,
    .campaign-messages-app .thread-summary-pill{
        display:inline-flex;
        align-items:center;
        gap:8px;
        min-height:32px;
        padding:0 12px;
        border-radius:999px;
        border:1px solid var(--line);
        background:#fff;
        color:#475467;
        font-size:12px;
        font-weight:800;
    }
    .campaign-messages-app .thread-filter-btn{
        cursor:pointer;
        transition:background .15s ease,border-color .15s ease,color .15s ease;
    }
    .campaign-messages-app .thread-filter-btn.active{
        color:#0a66c2;
        background:rgba(10,102,194,.09);
        border-color:rgba(10,102,194,.20);
    }
    .campaign-messages-app .thread-summary-pill::before{
        content:'';
        width:8px;
        height:8px;
        border-radius:50%;
        background:#cbd5e1;
    }
    .campaign-messages-app .thread-summary-pill.unread::before,
    .campaign-messages-app .thread-status-dot.unread{
        background:#0a66c2;
    }
    .campaign-messages-app .thread-summary-pill.needs-reply::before,
    .campaign-messages-app .thread-status-dot.needs-reply{
        background:#f79009;
    }
    .campaign-messages-app .thread-summary-pill.not-sent::before,
    .campaign-messages-app .thread-status-dot.not-sent{
        background:#7c3aed;
    }
    .campaign-messages-app .thread-summary-pill.pending-approval::before,
    .campaign-messages-app .thread-status-dot.pending-approval{
        background:#f79009;
    }
    .campaign-messages-app .thread-summary-pill.read::before,
    .campaign-messages-app .thread-status-dot.read,
    .campaign-messages-app .thread-status-dot.unanswered{
        background:#98a2b3;
    }
    .campaign-messages-app .thread-list{
        flex:1 1 auto;
        min-height:0;
        overflow:auto;
        background:#fff;
    }
    .campaign-messages-app .thread-item{
        position:relative;
        display:flex;
        align-items:flex-start;
        gap:13px;
        width:100%;
        padding:14px 16px;
        border:0;
        border-bottom:1px solid rgba(231,235,243,.92);
        background:#fff;
        text-align:left;
        cursor:pointer;
        transition:background .16s ease,box-shadow .16s ease;
    }
    .campaign-messages-app .thread-item:hover,
    .campaign-messages-app .thread-item:focus-visible{
        background:#f3f7fb;
        outline:0;
    }
    .campaign-messages-app .thread-item::before{
        content:'';
        position:absolute;
        left:0;
        top:12px;
        bottom:12px;
        width:4px;
        border-radius:999px;
        background:transparent;
    }
    .campaign-messages-app .thread-item.active{
        background:#eef6ff;
    }
    .campaign-messages-app .thread-item.active::before{
        background:#0a66c2;
    }
    .campaign-messages-app .thread-item.unread .thread-name,
    .campaign-messages-app .thread-item.unread .thread-time,
    .campaign-messages-app .thread-item.needs-reply .thread-name{
        font-weight:900;
        color:#101828;
    }
    .campaign-messages-app .thread-item.needs-reply:not(.active){
        background:linear-gradient(90deg,rgba(247,144,9,.10),#fff 42%);
    }
    .campaign-messages-app .thread-item.needs-reply.active{
        background:linear-gradient(90deg,rgba(10,102,194,.13),rgba(247,144,9,.10));
    }
    .campaign-messages-app .thread-avatar-wrap{
        position:relative;
        flex:0 0 auto;
    }
    .campaign-messages-app .thread-avatar,
    .campaign-messages-app .thread-view-avatar{
        position:relative;
        overflow:hidden;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:50%;
        background:linear-gradient(135deg,#293e52,#5f7893);
        color:#fff;
        font-weight:900;
        letter-spacing:.02em;
        box-shadow:0 10px 22px rgba(15,23,42,.12);
    }
    .campaign-messages-app .thread-avatar{
        width:50px;
        height:50px;
        font-size:16px;
    }
    .campaign-messages-app .thread-view-avatar{
        width:58px;
        height:58px;
        font-size:19px;
    }
    .campaign-messages-app .thread-avatar img,
    .campaign-messages-app .thread-view-avatar img{
        position:absolute;
        inset:0;
        width:100%;
        height:100%;
        object-fit:cover;
    }
    .campaign-messages-app .thread-avatar span,
    .campaign-messages-app .thread-view-avatar span{
        position:relative;
        z-index:0;
    }
    .campaign-messages-app .thread-avatar.avatar-fallback img,
    .campaign-messages-app .thread-view-avatar.avatar-fallback img{
        display:none;
    }
    .campaign-messages-app .thread-status-dot{
        position:absolute;
        right:1px;
        bottom:1px;
        width:12px;
        height:12px;
        border-radius:50%;
        border:2px solid #fff;
        box-shadow:0 4px 10px rgba(15,23,42,.12);
    }
    .campaign-messages-app .thread-body{
        min-width:0;
        flex:1 1 auto;
    }
    .campaign-messages-app .thread-body-top{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:10px;
    }
    .campaign-messages-app .thread-name{
        display:block;
        font-size:14px;
        line-height:1.35;
        font-weight:750;
        color:#344054;
    }
    .campaign-messages-app .thread-company{
        display:block;
        margin-top:2px;
        color:#667085;
        font-size:12px;
        line-height:1.35;
    }
    .campaign-messages-app .thread-time{
        flex:0 0 auto;
        color:#667085;
        font-size:12px;
        font-weight:750;
        white-space:nowrap;
    }
    .campaign-messages-app .thread-time-wrap{
        flex:0 0 auto;
        display:flex;
        align-items:center;
        gap:7px;
        margin-left:auto;
    }
    .campaign-messages-app .thread-favorite-btn{
        flex:0 0 auto;
        width:32px;
        height:32px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:0;
        border:1px solid var(--line);
        border-radius:50%;
        background:#fff;
        color:#98a2b3;
        cursor:pointer;
        transition:background .15s ease,border-color .15s ease,color .15s ease,transform .15s ease;
    }
    .campaign-messages-app .thread-favorite-btn:hover,
    .campaign-messages-app .thread-favorite-btn:focus-visible{
        color:#e11d48;
        border-color:#fecdd3;
        background:#fff1f2;
        outline:0;
        transform:translateY(-1px);
    }
    .campaign-messages-app .thread-favorite-btn.active{
        color:#e11d48;
        border-color:#fecdd3;
        background:#fff1f2;
    }
    .campaign-messages-app .thread-favorite-btn span{
        font-size:18px;
        line-height:1;
    }
    .campaign-messages-app .thread-view-actions{
        flex:0 0 auto;
        display:flex;
        align-items:center;
        gap:8px;
    }
    .campaign-messages-app .thread-snippet{
        display:-webkit-box;
        -webkit-box-orient:vertical;
        -webkit-line-clamp:2;
        overflow:hidden;
        margin-top:8px;
        color:#475467;
        font-size:13px;
        line-height:1.45;
    }
    .campaign-messages-app .thread-item-meta{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        margin-top:10px;
    }
    .campaign-messages-app .thread-state{
        display:inline-flex;
        align-items:center;
        gap:6px;
        min-height:24px;
        padding:0 9px;
        border-radius:999px;
        background:#f2f4f7;
        color:#667085;
        font-size:11px;
        font-weight:900;
        white-space:nowrap;
    }
    .campaign-messages-app .thread-state::before{
        content:'';
        width:7px;
        height:7px;
        border-radius:50%;
        background:#98a2b3;
    }
    .campaign-messages-app .thread-state.unread{
        background:rgba(10,102,194,.10);
        color:#0a66c2;
    }
    .campaign-messages-app .thread-state.unread::before{
        background:#0a66c2;
    }
    .campaign-messages-app .thread-state.needs-reply{
        background:rgba(247,144,9,.14);
        color:#b54708;
    }
    .campaign-messages-app .thread-state.needs-reply::before{
        background:#f79009;
    }
    .campaign-messages-app .thread-state.not-sent{
        background:rgba(124,58,237,.12);
        color:#6d28d9;
    }
    .campaign-messages-app .thread-state.not-sent::before{
        background:#7c3aed;
    }
    .campaign-messages-app .thread-state.pending-approval{
        background:rgba(247,144,9,.15);
        color:#b54708;
    }
    .campaign-messages-app .thread-state.pending-approval::before{
        background:#f79009;
    }
    .campaign-messages-app .thread-state.unanswered{
        background:#f2f4f7;
        color:#667085;
    }
    .campaign-messages-app .thread-state.unanswered::before{
        background:#98a2b3;
    }
    .campaign-messages-app .thread-item-meta small{
        color:#98a2b3;
        font-size:11px;
        font-weight:750;
    }
    .campaign-messages-app .thread-empty{
        padding:22px;
    }
    .campaign-messages-app .thread-empty .card{
        border-style:dashed;
    }
    .campaign-messages-app .thread-view{
        display:grid;
        grid-template-rows:auto minmax(0,1fr) auto;
        background:#f7faff;
    }
    .campaign-messages-app .thread-view-head{
        display:flex;
        justify-content:space-between;
        gap:16px;
        padding:18px 20px;
        border-bottom:1px solid var(--line);
        background:#fff;
    }
    .campaign-messages-app .thread-view-contact{
        display:flex;
        align-items:center;
        gap:14px;
        min-width:0;
    }
    .campaign-messages-app .thread-view-copy{
        min-width:0;
    }
    .campaign-messages-app .thread-view-copy strong{
        display:block;
        font-size:18px;
        color:#101828;
        letter-spacing:-.03em;
    }
    .campaign-messages-app .thread-view-copy span{
        display:block;
        margin-top:4px;
        color:#667085;
        font-size:13px;
        line-height:1.45;
    }
    .campaign-messages-app .thread-id-chip-row{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-top:10px;
    }
    .campaign-messages-app .thread-id-chip{
        display:inline-flex;
        align-items:center;
        min-height:28px;
        padding:0 10px;
        border-radius:999px;
        border:1px solid var(--line);
        background:#fff;
        color:#475467;
        font-size:12px;
        font-weight:800;
    }
    .campaign-messages-app .thread-id-chip.link{
        color:#0a66c2;
        background:rgba(10,102,194,.08);
        border-color:rgba(10,102,194,.15);
    }
    .campaign-messages-app .thread-id-chip.button{
        cursor:pointer;
    }
    .campaign-messages-app .thread-messages{
        min-height:0;
        height:100%;
        overflow-y:auto;
        overflow-x:hidden;
        padding:22px 22px 0;
        background:
                radial-gradient(circle at top right,rgba(10,102,194,.05),transparent 30%),
                linear-gradient(180deg,#fbfdff,#f4f8fc);
        overscroll-behavior:contain;
    }
    .campaign-messages-app .thread-messages-inner {
        min-height:100%;
        min-width:100%;
        display:flex;
        flex-direction:column;
        justify-content:flex-start;
        box-sizing:border-box;
        padding-bottom:25px;
    }
    .campaign-messages-app .thread-messages-inner .message-row:first-child{
        margin-top:auto;
    }
    .campaign-messages-app .thread-messages-inner .card:first-child{
        margin-top:auto;
        margin-bottom:auto;
    }
    .campaign-messages-app .message-row{
        display:flex;
        width:100% !important;
        min-width:0;
        margin:0 0 12px;
        clear:both;
    }
    .campaign-messages-app .thread-messages-inner .message-row:last-child{
        margin-bottom:0;
    }
    .campaign-messages-app .message-row.left,
    .campaign-messages-app .message-row.is-incoming{
        justify-content:flex-start;
    }
    .campaign-messages-app .message-row.right,
    .campaign-messages-app .message-row.is-outgoing{
        justify-content:flex-end;
    }
    .campaign-messages-app .message-row.left .bubble,
    .campaign-messages-app .message-row.is-incoming .bubble{
        margin-right:auto;
    }
    .campaign-messages-app .message-row.left .bubble,
    .campaign-messages-app .message-row.is-incoming .bubble{
        padding:4px 0;
        border:0;
        border-radius:0;
        background:transparent;
        color:#000;
        box-shadow:none;
    }    .campaign-messages-app .message-row.right .bubble,
    .campaign-messages-app .message-row.is-outgoing .bubble{
        margin-left:auto;
    }
    .campaign-messages-app .bubble{
        max-width:min(690px,78%);
        border-radius:18px;
        padding:11px 13px 9px;
        box-shadow:0 8px 20px rgba(15,23,42,.06);
        word-break:break-word;
    }
    .campaign-messages-app .message-row.left .bubble,
    .campaign-messages-app .message-row.is-incoming .bubble{
        background:#fff;
        border:1px solid #e7ebf3;
        color:#101828;
        border-top-left-radius:6px;
    }
    .campaign-messages-app .message-row.left .bubble,
    .campaign-messages-app .message-row.is-incoming .bubble{
        padding:4px 0;
        border:0;
        border-radius:0;
        background:transparent;
        color:#000;
        box-shadow:none;
    }    .campaign-messages-app .message-row.right .bubble,
    .campaign-messages-app .message-row.is-outgoing .bubble{
        background:#0a66c2;
        color:#fff;
        border-top-right-radius:6px;
    }
    .campaign-messages-app .message-row.is-pending-approval .bubble{
        background:#fff8ed;
        color:#101828;
        border:1px solid rgba(247,144,9,.34);
        box-shadow:0 12px 28px rgba(247,144,9,.12);
    }
    .campaign-messages-app .message-row.is-pending-approval .bubble-meta{
        color:#b54708;
    }
    .campaign-messages-app .bubble-text{
        font-size:14px;
        line-height:1.5;
    }
    .campaign-messages-app .bubble-text p{
        margin:0;
    }
    .campaign-messages-app .bubble-text p + p{
        margin-top:8px;
    }
    .campaign-messages-app .bubble-text a{
        color:inherit;
        text-decoration:underline;
        overflow-wrap:anywhere;
    }
    .campaign-messages-app .bubble-text img{
        display:block;
        max-width:260px;
        max-height:260px;
        width:auto;
        height:auto;
        border-radius:12px;
        margin-top:5px;
    }
    .campaign-messages-app .bubble-meta{
        display:block;
        margin-top:7px;
        color:rgba(255,255,255,.78);
        font-size:11px;
        font-weight:750;
    }
    .campaign-messages-app .message-row.left .bubble-meta,
    .campaign-messages-app .message-row.is-incoming .bubble-meta{
        color:#98a2b3;
    }
    .campaign-messages-app .message-date-separator{
        position:relative;
        display:flex;
        align-items:center;
        justify-content:center;
        width:100%;
        margin:14px 0 18px;
        color:#667085;
        font-size:12px;
        font-weight:900;
        letter-spacing:.01em;
    }
    .campaign-messages-app .message-date-separator::before,
    .campaign-messages-app .message-date-separator::after{
        content:'';
        flex:1 1 auto;
        height:1px;
        background:#d9e2ec;
    }
    .campaign-messages-app .message-date-separator span{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:28px;
        margin:0 12px;
        padding:0 12px;
        border:1px solid #d9e2ec;
        border-radius:999px;
        background:#fff;
        color:#667085;
        box-shadow:0 6px 16px rgba(15,23,42,.05);
        white-space:nowrap;
    }
    .campaign-messages-app .thread-messages-inner .message-date-separator:first-child{
        margin-top:auto;
    }
    .campaign-messages-app .bubble-pending,
    .campaign-messages-app .bubble-status{
        display:block;
        margin-top:8px;
        font-size:12px;
        font-weight:800;
    }
    .campaign-messages-app .bubble-pending{
        color:#b54708;
    }
    .campaign-messages-app .message-row.left .bubble-pending,
    .campaign-messages-app .message-row.is-incoming .bubble-pending{
        color:#b54708;
    }
    .campaign-messages-app .bubble-status{
        color:#dcfae6;
    }
    .campaign-messages-app .message-row.left .bubble-status,
    .campaign-messages-app .message-row.is-incoming .bubble-status{
        color:#027a48;
    }
    .campaign-messages-app .bubble-actions{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-top:9px;
    }
    .campaign-messages-app .bubble-prepare,
    .campaign-messages-app .bubble-edit{
        border:0;
        border-radius:999px;
        background:rgba(255,255,255,.92);
        color:#0a66c2;
        font-size:12px;
        font-weight:900;
        padding:7px 12px;
        cursor:pointer;
    }
    .campaign-messages-app .message-row.is-pending-approval .bubble-edit,
    .campaign-messages-app .outbox-review-btn{
        background:#0a66c2;
        color:#fff;
    }
    .campaign-messages-app .btn-danger{
        border-color:#d92d20;
        background:#fff;
        color:#b42318;
    }
    .campaign-messages-app .btn-danger:hover,
    .campaign-messages-app .btn-danger:focus-visible{
        background:#fef3f2;
    }
    .campaign-messages-app .message-row.left .bubble-prepare,
    .campaign-messages-app .message-row.left .bubble-edit,
    .campaign-messages-app .message-row.is-incoming .bubble-prepare,
    .campaign-messages-app .message-row.is-incoming .bubble-edit{
        background:#101828;
        color:#fff;
    }
    .campaign-messages-app .thread-compose{
        display:flex;
        align-items:flex-end;
        gap:12px;
        padding:14px 16px;
        border-top:1px solid var(--line);
        background:#fff;
    }
    .campaign-messages-app .thread-compose textarea{
        min-height:46px;
        max-height:160px;
        resize:none;
        border-radius:18px;
        padding:12px 14px;
    }
    .campaign-messages-app .copy-feedback{
        color:#027a48 !important;
        background:#ecfdf3 !important;
        border-color:#abefc6 !important;
    }

    .campaign-messages-app .thread-context-menu{
        position:fixed;
        z-index:1000000;
        min-width:210px;
        padding:6px;
        border:1px solid rgba(16,24,40,.12);
        border-radius:14px;
        background:#fff;
        box-shadow:0 18px 40px rgba(15,23,42,.18);
        display:none;
    }
    .campaign-messages-app .thread-context-menu.show{
        display:block;
    }
    .campaign-messages-app .thread-context-menu button{
        display:flex;
        align-items:center;
        gap:9px;
        width:100%;
        min-height:38px;
        padding:0 11px;
        border:0;
        border-radius:10px;
        background:transparent;
        color:#344054;
        font-size:13px;
        font-weight:800;
        text-align:left;
        cursor:pointer;
    }
    .campaign-messages-app .thread-context-menu button:hover,
    .campaign-messages-app .thread-context-menu button:focus-visible{
        background:#f3f7fb;
        color:#0a66c2;
        outline:0;
    }
    .campaign-messages-app .thread-context-menu button.thread-context-score{
        display:grid;
        grid-template-columns:repeat(2, minmax(0, 1fr));
        align-items:center;
        gap:0;
        margin:2px 0 6px;
        padding:10px 11px;
        border:0;
        border-radius:10px;
        background:#f8fafc;
        color:#344054;
        cursor:pointer;
    }
    .campaign-messages-app .thread-context-menu button.thread-context-score[hidden]{display:none;}
    .campaign-messages-app .thread-context-score strong,
    .campaign-messages-app .thread-context-score span{
        display:flex;
        align-items:center;
        justify-content:center;
        min-width:0;
        text-align:center;
    }
    .campaign-messages-app .thread-context-score strong{font-size:13px;}
    .campaign-messages-app .thread-context-score span{margin-top:0;font-size:12px;font-weight:750;}
    .campaign-messages-app .score-reason-card{max-width:560px;}
    .campaign-messages-app .score-reason-grid{display:grid;gap:14px;}
    .campaign-messages-app .score-reason-item{padding:14px;border:1px solid var(--line);border-radius:14px;background:#f8fafc;}
    .campaign-messages-app .score-reason-item strong{display:block;margin-bottom:6px;color:#101828;}
    .campaign-messages-app .score-reason-item p{margin:0;color:#475467;line-height:1.55;}
    .campaign-messages-app .score-evidence{margin:8px 0 0;padding-left:19px;color:#475467;}

    .campaign-messages-app .messages-toast{
        position:fixed;
        right:22px;
        bottom:22px;
        z-index:1000001;
        display:flex;
        align-items:center;
        gap:10px;
        max-width:min(420px,calc(100vw - 44px));
        padding:13px 15px;
        border:1px solid #abefc6;
        border-radius:16px;
        background:#ecfdf3;
        color:#027a48;
        box-shadow:0 18px 40px rgba(15,23,42,.18);
        font-size:13px;
        font-weight:900;
        opacity:0;
        pointer-events:none;
        transform:translateY(8px);
        transition:opacity .16s ease, transform .16s ease;
    }
    .campaign-messages-app .messages-toast.show{
        opacity:1;
        transform:translateY(0);
    }
    .campaign-messages-app .messages-toast.error{
        border-color:#fecdca;
        background:#fef3f2;
        color:#b42318;
    }
    .campaign-messages-app .outbox-section{
        border-bottom:1px solid rgba(231,235,243,.92);
        background:#fff;
    }
    .campaign-messages-app .outbox-section:last-child{
        border-bottom:0;
    }
    .campaign-messages-app .outbox-section-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        padding:12px 16px;
        background:#f8fafc;
        border-bottom:1px solid rgba(231,235,243,.92);
    }
    .campaign-messages-app .outbox-section-head strong{
        color:#101828;
        font-size:13px;
        font-weight:950;
    }
    .campaign-messages-app .outbox-section-head span{
        color:#667085;
        font-size:12px;
        font-weight:800;
    }
    .campaign-messages-app .outbox-section.pending .outbox-section-head{
        background:#fff7e8;
    }
    .campaign-messages-app .outbox-section.pending .outbox-section-head strong{
        color:#b54708;
    }
    .campaign-messages-app .thread-item.outbox-item{
        align-items:center;
    }
    .campaign-messages-app .thread-item.pending-approval:not(.active){
        background:linear-gradient(90deg,rgba(247,144,9,.12),#fff 46%);
    }
    .campaign-messages-app .outbox-actions{
        flex:0 0 auto;
        display:flex;
        align-items:center;
        gap:8px;
        margin-left:6px;
    }
    .campaign-messages-app .outbox-thread-alert{
        margin:14px 16px 0;
        border:1px solid rgba(247,144,9,.26);
        border-radius:16px;
        background:#fff8ed;
        color:#b54708;
        padding:11px 13px;
        font-size:13px;
        font-weight:850;
        line-height:1.45;
    }
    @media (max-width:1100px){
        .campaign-messages-app .threads-layout{
            grid-template-columns:1fr;
        }
        .campaign-messages-app .thread-list-panel{
            min-height:420px;
            max-height:560px;
        }
        .campaign-messages-app .thread-view{
            min-height:640px;
        }
    }
    @media (max-width:767px){
        .campaign-messages-app .mailbox-switcher{
            grid-template-columns:1fr;
        }
        .campaign-messages-app .mailbox-tab{
            min-height:64px;
        }
        .campaign-messages-app .thread-list-toolbar,
        .campaign-messages-app .thread-view-head,
        .campaign-messages-app .thread-messages,
        .campaign-messages-app .thread-compose{
            padding-left:14px;
            padding-right:14px;
        }
        .campaign-messages-app .thread-view-head,
        .campaign-messages-app .thread-compose{
            flex-direction:column;
            align-items:stretch;
        }
        .campaign-messages-app .thread-view-contact{
            align-items:flex-start;
        }
        .campaign-messages-app .bubble{
            max-width:92%;
        }
    }

    .campaign-messages-content{
        max-width:none !important;
        width:100% !important;
        min-height:100vh;
        padding-left:18px !important;
        padding-right:18px !important;
    }
    .campaign-messages-app{
        width:100%;
        max-width:none;
    }
    .campaign-messages-app .panel{
        height:calc(100vh - 106px);
        min-height:640px;
        display:flex;
        flex-direction:column;
        margin-bottom:0;
    }
    .campaign-messages-app .panel-inner{
        flex:1 1 auto;
        min-height:0;
        display:flex;
        flex-direction:column;
    }
    .campaign-messages-app .section-head{
        flex:0 0 auto;
        margin-bottom:14px;
    }
    .campaign-messages-app .threads-layout{
        flex:1 1 auto;
        min-height:0;
        height:100%;
        grid-template-columns:minmax(340px,28vw) minmax(0,1fr);
    }
    .campaign-messages-app .thread-list-panel,
    .campaign-messages-app .thread-view{
        height:100%;
        min-height:0;
    }
    .campaign-messages-app .thread-list-panel,
    .campaign-messages-app .thread-list,
    .campaign-messages-app .thread-view{
        min-height:100%;
    }
    .campaign-messages-app .thread-messages{
        min-height:0;
    }
    .campaign-messages-app .thread-view-head{
        flex:0 0 auto;
    }
    .campaign-messages-app .thread-compose{
        align-items:stretch;
        flex-direction:column;
        gap:8px;
    }
    .campaign-messages-app .thread-compose-top{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
    }
    .campaign-messages-app .thread-compose-title{
        font-size:12px;
        font-weight:900;
        color:#475467;
    }
    .campaign-messages-app .composer-toolbar{
        display:flex;
        flex-wrap:wrap;
        gap:6px;
    }
    .campaign-messages-app .composer-tool{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:34px;
        height:32px;
        padding:0 10px;
        border:1px solid var(--line);
        border-radius:999px;
        background:#fff;
        color:#344054;
        font-size:14px;
        font-weight:900;
        cursor:pointer;
    }
    .campaign-messages-app .composer-tool:hover,
    .campaign-messages-app .composer-tool.active{
        color:#0a66c2;
        border-color:rgba(10,102,194,.28);
        background:rgba(10,102,194,.08);
    }
    .campaign-messages-app .rich-editor{
        width:100%;
        min-height:78px;
        max-height:170px;
        overflow:auto;
        border:1px solid var(--line);
        border-radius:18px;
        padding:12px 14px;
        background:#fff;
        color:#101828;
        font-size:14px;
        line-height:1.5;
        outline:0;
    }
    .campaign-messages-app .rich-editor:focus{
        border-color:rgba(10,102,194,.45);
        box-shadow:0 0 0 4px rgba(10,102,194,.10);
    }
    .campaign-messages-app .rich-editor:empty::before{
        content:attr(data-placeholder);
        color:#98a2b3;
        pointer-events:none;
    }
    .campaign-messages-app .composer-hint,
    .campaign-messages-app .composer-status{
        font-size:12px;
        color:#667085;
    }
    .campaign-messages-app .composer-status.error{
        color:#b42318;
    }
    .campaign-messages-app .composer-status.success{
        color:#027a48;
    }
    .campaign-messages-app .thread-compose-actions{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
    }
    .campaign-messages-app .thread-compose-actions .btn{
        white-space:nowrap;
    }

    .campaign-messages-app .inbox-access-banner{
        display:none;
        margin:0 0 16px;
        border:1px solid rgba(10,102,194,.18);
        border-radius:18px;
        padding:13px 15px;
        background:linear-gradient(135deg,rgba(10,102,194,.08),rgba(240,247,255,.94));
        color:#18456f;
        font-size:13px;
        font-weight:800;
        line-height:1.45;
    }
    .campaign-messages-app .inbox-access-banner.show{
        display:block;
    }
    .campaign-messages-app .inbox-access-banner.success{
        border-color:rgba(2,122,72,.22);
        background:linear-gradient(135deg,rgba(18,183,106,.10),rgba(240,253,244,.96));
        color:#027a48;
    }
    .campaign-messages-app .inbox-access-banner.error{
        border-color:rgba(180,35,24,.22);
        background:linear-gradient(135deg,rgba(240,68,56,.10),rgba(255,251,250,.98));
        color:#b42318;
    }
    .campaign-messages-app .inbox-access-card{
        margin:18px;
        border:1px solid rgba(10,102,194,.18);
        border-radius:22px;
        background:
                radial-gradient(circle at top right,rgba(10,102,194,.10),transparent 34%),
                linear-gradient(180deg,#fff,#f8fbff);
        padding:22px;
        box-shadow:0 18px 45px rgba(15,23,42,.06);
    }
    .campaign-messages-app .inbox-access-card.pending{
        border-color:rgba(247,144,9,.26);
        background:
                radial-gradient(circle at top right,rgba(247,144,9,.13),transparent 34%),
                linear-gradient(180deg,#fff,#fffaf3);
    }
    .campaign-messages-app .inbox-access-card h4{
        margin:0;
        color:#101828;
        font-size:18px;
        letter-spacing:-.02em;
    }
    .campaign-messages-app .inbox-access-card p{
        margin:8px 0 0;
        max-width:680px;
        color:#667085;
        font-size:14px;
        line-height:1.55;
    }
    .campaign-messages-app .inbox-access-card .access-card-actions{
        display:flex;
        flex-wrap:wrap;
        align-items:center;
        gap:10px;
        margin-top:16px;
    }
    .campaign-messages-app .access-state-pill{
        display:inline-flex;
        align-items:center;
        gap:8px;
        min-height:32px;
        padding:0 12px;
        border-radius:999px;
        border:1px solid rgba(247,144,9,.24);
        background:#fff8ed;
        color:#b54708;
        font-size:12px;
        font-weight:900;
    }
    .campaign-messages-app .access-state-pill.granted{
        border-color:rgba(2,122,72,.20);
        background:#f0fdf4;
        color:#027a48;
    }
    .campaign-messages-app .access-state-pill.locked{
        border-color:rgba(180,35,24,.18);
        background:#fff7f6;
        color:#b42318;
    }
    .campaign-messages-app .btn[disabled],
    .campaign-messages-app button[disabled]{
        opacity:.62;
        cursor:not-allowed;
    }
    @media (min-width:1500px){
        .campaign-messages-app .threads-layout{
            grid-template-columns:minmax(390px,24vw) minmax(0,1fr);
        }
        .campaign-messages-app .bubble{
            max-width:min(860px,74%);
        }
    }
    @media (max-width:1100px){
        .campaign-messages-app .panel{
            height:auto;
            min-height:0;
        }
        .campaign-messages-app .thread-list-panel{
            height:420px;
        }
        .campaign-messages-app .thread-view{
            height:calc(100vh - 120px);
            min-height:640px;
        }
    }

    .campaign-messages-app{
        --li-blue:#0a66c2;
        --li-green:#057642;
        --li-green-dark:#004b2f;
        --li-surface:#fff;
        --li-canvas:#f3f2ef;
        --li-border:#d6d6d6;
        --li-border-soft:#e0e0e0;
        --li-hover:#f3f6f8;
        --li-selected:#edf3f8;
        --li-text:#000;
        --li-muted:#666;
        --li-warning:#b54708;
    }
    .campaign-messages-app .panel{
        height:calc(100vh - 106px);
        min-height:640px;
        display:flex;
        flex-direction:column;
        margin-bottom:0;
        border:0;
        border-radius:0;
        background:transparent;
        box-shadow:none;
        overflow:visible;
    }
    .campaign-messages-app .panel-inner{
        flex:1 1 auto;
        min-height:0;
        display:flex;
        flex-direction:column;
        padding:0;
    }
    .campaign-messages-app .section-head{
        flex:0 0 auto;
        min-height:54px;
        margin:0;
        padding:7px 12px 7px 14px;
        align-items:center;
        border:1px solid var(--li-border);
        border-bottom:0;
        border-radius:8px 8px 0 0;
        background:var(--li-surface);
    }
    .campaign-messages-app .section-head h3{
        margin:0;
        color:var(--li-text);
        font-size:16px;
        line-height:1.25;
        font-weight:700;
        letter-spacing:0;
    }
    .campaign-messages-app .section-head p{
        display:none;
    }
    .campaign-messages-app .section-head > div[style]{
        min-width:180px !important;
        max-width:230px;
    }
    .campaign-messages-app .section-head .select,
    .campaign-messages-app #employeeSelect{
        min-height:34px;
        height:34px;
        border-color:#d5dde5;
        border-radius:6px;
        background:#f4f7f9;
        color:#29323b;
        font-size:13px;
        font-weight:600;
        padding:0 10px;
        box-shadow:none;
    }
    .campaign-messages-app .mailbox-switcher{
        flex:0 0 auto;
        width:100%;
        display:flex;
        flex-wrap:wrap;
        align-items:center;
        gap:6px;
        margin:0;
        padding:7px 14px;
        border:1px solid var(--li-border);
        border-bottom:1px solid var(--li-border-soft);
        background:var(--li-surface);
        box-shadow:none;
    }
    .campaign-messages-app .mailbox-tab,
    .campaign-messages-app .mailbox-tab[data-mailbox="outbox"].active{
        appearance:none;
        width:auto;
        min-height:30px;
        display:inline-flex;
        grid-template-columns:none;
        align-items:center;
        gap:7px;
        padding:0 13px;
        border:1px solid #b7b7b7;
        border-radius:999px;
        background:#fff;
        color:#1f2328;
        box-shadow:none;
    }
    .campaign-messages-app .mailbox-tab:hover,
    .campaign-messages-app .thread-filter-btn:hover{
        background:#f3f2ef;
    }
    .campaign-messages-app .mailbox-tab span{
        display:inline;
        color:inherit;
        font-size:14px;
        font-weight:700;
        letter-spacing:0;
        text-transform:none;
    }
    .campaign-messages-app .mailbox-tab small{
        display:none;
    }
    .campaign-messages-app .mailbox-tab strong,
    .campaign-messages-app .mailbox-tab[data-mailbox="outbox"] strong{
        grid-row:auto;
        grid-column:auto;
        min-width:auto;
        width:auto;
        height:auto;
        padding:0;
        border-radius:0;
        background:transparent;
        color:inherit;
        font-size:12px;
        font-weight:700;
        line-height:1;
    }
    .campaign-messages-app .mailbox-tab.active{
        border-color:var(--li-green);
        background:var(--li-green);
        color:#fff;
    }
    .campaign-messages-app .mailbox-tab.active span,
    .campaign-messages-app .mailbox-tab.active strong{
        color:#fff;
    }
    .campaign-messages-app .mailbox-tab[data-mailbox="outbox"] strong,
    .campaign-messages-app .mailbox-tab[data-mailbox="outbox"].active strong{
        background:transparent;
        color:inherit;
    }
    .campaign-messages-app .mailbox-tab[data-mailbox="outbox"].active{
        border-color:var(--li-warning);
        background:#fff3db;
        color:var(--li-warning);
    }
    .campaign-messages-app .threads-layout{
        flex:1 1 auto;
        min-height:0;
        height:100%;
        display:grid;
        grid-template-columns:clamp(280px,28vw,340px) minmax(0,1fr) !important;
        gap:0;
        border:1px solid var(--li-border);
        border-top:0;
        border-radius:0 0 8px 8px;
        background:var(--li-surface);
        box-shadow:none;
        overflow:hidden;
    }
    .campaign-messages-app .thread-list-panel,
    .campaign-messages-app .thread-view{
        height:100%;
        min-height:0;
        border:0;
        border-radius:0;
        background:var(--li-surface);
        box-shadow:none;
        overflow:hidden;
    }
    .campaign-messages-app .thread-list-panel{
        display:flex;
        flex-direction:column;
        border-right:1px solid var(--li-border-soft);
    }
    .campaign-messages-app .thread-list-toolbar{
        flex:0 0 auto;
        padding:7px 12px;
        border-bottom:1px solid var(--li-border-soft);
        background:var(--li-surface);
    }
    .campaign-messages-app .thread-list-toolbar > strong,
    .campaign-messages-app .thread-list-toolbar > span,
    .campaign-messages-app .thread-list-summary{
        display:none;
    }
    .campaign-messages-app .thread-filter-row{
        display:flex;
        flex-wrap:wrap;
        align-items:center;
        gap:6px;
        margin:0;
    }
    .campaign-messages-app .thread-filter-btn,
    .campaign-messages-app .thread-summary-pill{
        min-height:28px;
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:0 11px;
        border:1px solid #b7b7b7;
        border-radius:999px;
        background:#fff;
        color:#1f2328;
        font-size:13px;
        font-weight:600;
        line-height:1;
        box-shadow:none;
    }
    .campaign-messages-app .thread-filter-btn.active{
        border-color:var(--li-blue);
        background:#eaf4ff;
        color:var(--li-blue);
    }
    .campaign-messages-app .thread-list{
        flex:1 1 auto;
        min-height:0;
        overflow:auto;
        background:var(--li-surface);
    }
    .campaign-messages-app .thread-item{
        position:relative;
        min-height:84px;
        display:flex;
        align-items:flex-start;
        gap:10px;
        width:100%;
        padding:14px 12px;
        border:0;
        border-bottom:1px solid #ebebeb;
        background:#fff;
        text-align:left;
        cursor:pointer;
        transition:background .12s ease;
    }
    .campaign-messages-app .thread-item:hover,
    .campaign-messages-app .thread-item:focus-visible{
        background:var(--li-hover);
        outline:0;
    }
    .campaign-messages-app .thread-item::before{
        content:'';
        position:absolute;
        left:0;
        top:0;
        bottom:0;
        width:3px;
        border-radius:0;
        background:transparent;
    }
    .campaign-messages-app .thread-item.active{
        background:var(--li-selected);
    }
    .campaign-messages-app .thread-item.active::before{
        background:var(--li-green);
    }
    .campaign-messages-app .thread-item.needs-reply:not(.active),
    .campaign-messages-app .thread-item.pending-approval:not(.active){
        background:linear-gradient(90deg,rgba(247,144,9,.11),#fff 24%);
    }
    .campaign-messages-app .thread-item.needs-reply:not(.active)::before,
    .campaign-messages-app .thread-item.pending-approval:not(.active)::before{
        background:#f5a623;
    }
    .campaign-messages-app .thread-avatar-wrap{
        position:relative;
        flex:0 0 auto;
        margin-top:2px;
    }
    .campaign-messages-app .thread-avatar,
    .campaign-messages-app .thread-view-avatar{
        position:relative;
        overflow:hidden;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:50%;
        background:#425b76;
        color:#fff;
        font-weight:700;
        letter-spacing:0;
        box-shadow:none;
    }
    .campaign-messages-app .thread-avatar{
        width:48px;
        height:48px;
        font-size:15px;
    }
    .campaign-messages-app .thread-view-avatar{
        width:64px;
        height:64px;
        font-size:18px;
    }
    .campaign-messages-app .thread-status-dot{
        position:absolute;
        right:1px;
        bottom:1px;
        width:10px;
        height:10px;
        border:2px solid #fff;
        border-radius:50%;
        box-shadow:none;
    }
    .campaign-messages-app .thread-score-arcs{
        position:absolute;
        inset:-4px;
        width:56px;
        height:56px;
        z-index:2;
        pointer-events:none;
        overflow:visible;
    }
    .campaign-messages-app .thread-score-arcs path{
        fill:none;
        stroke-width:3.5;
        stroke-linecap:round;
    }
    .campaign-messages-app .thread-score-arcs .score-arc-track{stroke:#e4e7ec;}
    .campaign-messages-app .thread-score-arcs .score-arc-value{transition:stroke .18s ease;}
    .campaign-messages-app .thread-score-arcs + .thread-status-dot{display:none;}
    .campaign-messages-app .thread-avatar-wrap:has(.thread-score-arcs) .thread-avatar{
        width:48px;
        height:48px;
    }
    .campaign-messages-app .thread-score-legend{font-size:11px;color:#667085;}
    .campaign-messages-app .thread-filter-btn[data-filter="hot-threads"].active{color:#b42318;background:#fef3f2;border-color:#fecdca;}
    .campaign-messages-app .thread-filter-btn[data-filter="icp"].active{color:#027a48;background:#ecfdf3;border-color:#abefc6;}

    .campaign-messages-app .thread-body{
        flex:1 1 auto;
        min-width:0;
    }
    .campaign-messages-app .thread-body-top{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:8px;
    }
    .campaign-messages-app .thread-body-top > div{
        min-width:0;
    }
    .campaign-messages-app .thread-name{
        display:block;
        overflow:hidden;
        color:var(--li-text);
        font-size:14px;
        line-height:1.3;
        font-weight:600;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    .campaign-messages-app .thread-company{
        display:block;
        overflow:hidden;
        margin-top:1px;
        color:var(--li-muted);
        font-size:12px;
        line-height:1.3;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    .campaign-messages-app .thread-time{
        flex:0 0 auto;
        color:#1f2328;
        font-size:14px;
        font-weight:400;
        line-height:1.25;
        white-space:nowrap;
    }
    .campaign-messages-app .thread-time-wrap{
        flex:0 0 auto;
        display:flex;
        align-items:center;
        gap:4px;
        margin-left:auto;
    }
    .campaign-messages-app .thread-favorite-btn{
        width:28px;
        height:28px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:0;
        border:0;
        border-radius:50%;
        background:transparent;
        color:#666;
        cursor:pointer;
        box-shadow:none;
    }
    .campaign-messages-app .thread-favorite-btn:hover,
    .campaign-messages-app .thread-favorite-btn:focus-visible{
        background:#f3f2ef;
        color:#c1354a;
        outline:0;
        transform:none;
    }
    .campaign-messages-app .thread-favorite-btn.active{
        background:#fff1f2;
        color:#c1354a;
    }
    .campaign-messages-app .thread-favorite-btn span{
        font-size:17px;
        line-height:1;
    }
    .campaign-messages-app .thread-view-actions{
        flex:0 0 auto;
        display:flex;
        align-items:center;
        gap:8px;
    }
    .campaign-messages-app .thread-view-actions .thread-favorite-btn{
        width:34px;
        height:34px;
        border:1px solid var(--li-border);
        background:#fff;
    }
    .campaign-messages-app .thread-item.unread .thread-name,
    .campaign-messages-app .thread-item.unread .thread-time,
    .campaign-messages-app .thread-item.needs-reply .thread-name{
        color:#000;
        font-weight:700;
    }
    .campaign-messages-app .thread-snippet{
        display:-webkit-box;
        -webkit-box-orient:vertical;
        -webkit-line-clamp:1;
        overflow:hidden;
        margin-top:4px;
        color:#38434f;
        font-size:13px;
        line-height:1.35;
    }
    .campaign-messages-app .thread-item-meta{
        display:block;
        margin:0;
    }
    .campaign-messages-app .thread-item-meta .thread-state,
    .campaign-messages-app .thread-item-meta small{
        display:none;
    }
    .campaign-messages-app .thread-empty{
        padding:16px;
    }
    .campaign-messages-app .thread-empty .card{
        border-radius:6px;
        box-shadow:none;
    }
    .campaign-messages-app .thread-view{
        display:grid;
        grid-template-rows:auto minmax(0,1fr) auto;
    }
    .campaign-messages-app .thread-view-head{
        min-height:86px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:14px;
        padding:14px 18px;
        border-bottom:1px solid var(--li-border-soft);
        background:#fff;
    }
    .campaign-messages-app .thread-view-contact{
        display:flex;
        align-items:center;
        gap:12px;
        min-width:0;
    }
    .campaign-messages-app .thread-view-copy{
        min-width:0;
    }
    .campaign-messages-app .thread-view-copy strong{
        display:block;
        overflow:hidden;
        color:#000;
        font-size:16px;
        line-height:1.25;
        font-weight:700;
        letter-spacing:0;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    .campaign-messages-app .thread-view-copy span{
        display:block;
        margin-top:2px;
        color:#000;
        font-size:13px;
        line-height:1.35;
    }
    .campaign-messages-app .thread-id-chip-row{
        display:none;
    }
    .campaign-messages-app .thread-view-head > .thread-state,
    .campaign-messages-app .thread-view-actions > .thread-state{
        min-height:22px;
        padding:0 8px;
        border-radius:999px;
        background:#edf3f8;
        color:var(--li-blue);
        font-size:11px;
        font-weight:700;
    }
    .campaign-messages-app .thread-view-head > .thread-state::before,
    .campaign-messages-app .thread-view-actions > .thread-state::before{
        width:7px;
        height:7px;
        background:var(--li-blue);
    }
    .campaign-messages-app .thread-messages{
        min-height:0;
        height:100%;
        overflow-y:auto;
        overflow-x:hidden;
        padding:16px 20px 0;
        background:#fff;
        overscroll-behavior:contain;
    }
    .campaign-messages-app .thread-messages-inner{
        min-height:100%;
        min-width:100%;
        max-width:860px;
        display:flex;
        flex-direction:column;
        justify-content:flex-start;
        box-sizing:border-box;
        margin:0 auto;
        padding-bottom:14px;
    }
    .campaign-messages-app .thread-messages-inner .message-row:first-child,
    .campaign-messages-app .thread-messages-inner .message-date-separator:first-child{
        margin-top:auto;
    }
    .campaign-messages-app .message-row{
        display:flex;
        width:100% !important;
        min-width:0;
        margin:0 0 10px;
        clear:both;
    }
    .campaign-messages-app .message-row.left,
    .campaign-messages-app .message-row.is-incoming{
        justify-content:flex-start;
    }
    .campaign-messages-app .message-row.right,
    .campaign-messages-app .message-row.is-outgoing{
        justify-content:flex-end;
    }
    .campaign-messages-app .bubble{
        max-width:min(620px,76%);
        padding:4px 0;
        border:0;
        border-radius:0;
        background:transparent;
        box-shadow:none;
        color:#000;
        word-break:break-word;
    }
    .campaign-messages-app .message-row.left .bubble,
    .campaign-messages-app .message-row.is-incoming .bubble{
        padding:4px 0;
        border:0;
        border-radius:0;
        background:transparent;
        color:#000;
        box-shadow:none;
    }    .campaign-messages-app .message-row.right .bubble,
    .campaign-messages-app .message-row.is-outgoing .bubble{
        padding:9px 11px;
        border:1px solid #cfe4f8;
        border-radius:14px;
        background:#e8f3ff;
        color:#000;
    }
    .campaign-messages-app .message-row.is-pending-approval .bubble{
        padding:10px 12px;
        border:1px solid #f1c27d;
        border-radius:14px;
        background:#fff7e8;
        color:#000;
        box-shadow:none;
    }
    .campaign-messages-app .bubble-text{
        color:inherit;
        font-size:14px;
        line-height:1.45;
    }
    .campaign-messages-app .bubble-text p{
        margin:0;
    }
    .campaign-messages-app .bubble-meta,
    .campaign-messages-app .message-row.left .bubble-meta,
    .campaign-messages-app .message-row.is-incoming .bubble-meta{
        display:block;
        margin-top:4px;
        color:var(--li-muted);
        font-size:11px;
        font-weight:600;
    }
    .campaign-messages-app .message-row.right .bubble-meta,
    .campaign-messages-app .message-row.is-outgoing .bubble-meta{
        color:#4f657a;
    }
    .campaign-messages-app .message-row.is-pending-approval .bubble-meta,
    .campaign-messages-app .bubble-pending{
        color:#b54708;
        font-size:11px;
        font-weight:700;
    }
    .campaign-messages-app .bubble-status{
        color:var(--li-blue);
        font-size:11px;
        font-weight:700;
    }
    .campaign-messages-app .message-date-separator{
        display:flex;
        align-items:center;
        justify-content:center;
        width:100%;
        margin:12px 0 14px;
        color:#666;
        font-size:11px;
        font-weight:500;
        letter-spacing:.04em;
        text-transform:uppercase;
    }
    .campaign-messages-app .message-date-separator::before,
    .campaign-messages-app .message-date-separator::after{
        content:'';
        flex:1 1 auto;
        height:1px;
        background:#e0e0e0;
    }
    .campaign-messages-app .message-date-separator span{
        min-height:auto;
        margin:0 12px;
        padding:0 8px;
        border:0;
        border-radius:0;
        background:#fff;
        color:#666;
        box-shadow:none;
        white-space:nowrap;
    }
    .campaign-messages-app .bubble-actions{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-top:8px;
    }
    .campaign-messages-app .bubble-prepare,
    .campaign-messages-app .bubble-edit,
    .campaign-messages-app .outbox-review-btn{
        min-height:28px;
        border:1px solid var(--li-blue);
        border-radius:999px;
        background:#fff;
        color:var(--li-blue);
        font-size:13px;
        font-weight:700;
        padding:0 13px;
        cursor:pointer;
    }
    .campaign-messages-app .thread-compose{
        display:flex;
        flex-direction:column;
        align-items:stretch;
        gap:8px;
        padding:10px 12px;
        border-top:2px solid #e0e0e0;
        background:#fff;
    }
    .campaign-messages-app .thread-compose-top{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        min-height:28px;
    }
    .campaign-messages-app .thread-compose-title{
        color:#38434f;
        font-size:12px;
        font-weight:700;
    }
    .campaign-messages-app .composer-toolbar{
        display:flex;
        align-items:center;
        justify-content:flex-end;
        flex-wrap:wrap;
        gap:4px;
    }
    .campaign-messages-app .composer-tool{
        min-width:30px;
        height:28px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:0 6px;
        border:0;
        border-radius:4px;
        background:transparent;
        color:#38434f;
        font-size:14px;
        font-weight:700;
        cursor:pointer;
    }
    .campaign-messages-app .composer-tool:hover,
    .campaign-messages-app .composer-tool.active{
        background:#eef3f8;
        color:var(--li-blue);
    }
    .campaign-messages-app .rich-editor{
        width:100%;
        min-height:92px;
        max-height:170px;
        overflow:auto;
        border:0;
        border-radius:6px;
        padding:14px 12px;
        background:var(--li-canvas);
        color:#000;
        font-size:14px;
        line-height:1.45;
        outline:0;
    }
    .campaign-messages-app .rich-editor:focus{
        background:#fff;
        box-shadow:inset 0 0 0 1px var(--li-blue);
    }
    .campaign-messages-app .rich-editor:empty::before{
        content:attr(data-placeholder);
        color:#666;
        pointer-events:none;
    }
    .campaign-messages-app .thread-compose-actions{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        min-height:34px;
    }
    .campaign-messages-app .composer-hint,
    .campaign-messages-app .composer-status{
        color:#666;
        font-size:12px;
    }
    .campaign-messages-app .thread-compose-actions .btn{
        min-height:32px;
        border-radius:999px;
        padding:0 14px;
        font-size:13px;
        font-weight:700;
        box-shadow:none;
        white-space:nowrap;
    }
    .campaign-messages-app .thread-compose-actions .btn-primary{
        background:var(--li-blue);
        color:#fff;
    }
    .campaign-messages-app .outbox-section{
        border-bottom:1px solid #ebebeb;
        background:#fff;
    }
    .campaign-messages-app .outbox-section-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        padding:8px 12px;
        border-bottom:1px solid #ebebeb;
        background:#f7f7f7;
    }
    .campaign-messages-app .outbox-section-head strong{
        color:#000;
        font-size:13px;
        font-weight:700;
    }
    .campaign-messages-app .outbox-section-head span{
        color:#666;
        font-size:12px;
        font-weight:500;
    }
    .campaign-messages-app .outbox-section.pending .outbox-section-head{
        background:#fff7e8;
    }
    .campaign-messages-app .outbox-thread-alert{
        margin:10px 12px 0;
        border:1px solid #f1c27d;
        border-radius:6px;
        background:#fff7e8;
        color:#8a4b00;
        padding:9px 10px;
        font-size:12px;
        font-weight:600;
        line-height:1.4;
        max-height: 40px;
    }
    .campaign-messages-app .inbox-access-banner,
    .campaign-messages-app .inbox-access-card{
        border-radius:6px;
        box-shadow:none;
    }
    .campaign-messages-app .btn[disabled],
    .campaign-messages-app button[disabled]{
        opacity:.55;
        cursor:not-allowed;
    }
    @media (min-width:1500px){
        .campaign-messages-app .threads-layout{
            grid-template-columns:clamp(300px,24vw,360px) minmax(0,1fr) !important;
        }
        .campaign-messages-app .bubble{
            max-width:min(680px,72%);
        }
    }
    @media (max-width:640px){
        .campaign-messages-app .panel{
            height:auto;
            min-height:0;
        }
        .campaign-messages-app .threads-layout{
            grid-template-columns:1fr !important;
            grid-template-rows:auto auto;
            height:auto;
            min-height:0;
            gap:10px;
            overflow:visible;
            border:0;
            border-radius:0;
            background:transparent;
        }
        .campaign-messages-app .thread-list-panel,
        .campaign-messages-app .thread-view{
            width:100%;
            min-width:0;
            border:1px solid var(--li-border);
            border-radius:8px;
        }
        .campaign-messages-app .thread-list-panel{
            height:420px;
            max-height:560px;
            border-right:1px solid var(--li-border);
        }
        .campaign-messages-app .thread-view{
            height:auto;
            min-height:640px;
        }
    }
    @media (max-width:640px){
        .campaign-messages-app .panel-inner{
            padding:0;
        }
        .campaign-messages-app .section-head{
            min-height:0;
            display:grid;
            grid-template-columns:1fr;
            gap:8px;
            padding:10px 12px;
        }
        .campaign-messages-app .section-head > div[style]{
            width:100%;
            min-width:0 !important;
            max-width:none;
        }
        .campaign-messages-app .mailbox-switcher{
            flex-wrap:nowrap;
            overflow-x:auto;
            padding:7px 12px;
        }
        .campaign-messages-app .mailbox-tab,
        .campaign-messages-app .mailbox-tab[data-mailbox="outbox"].active,
        .campaign-messages-app .thread-filter-btn{
            flex:0 0 auto;
        }
        .campaign-messages-app .thread-list-toolbar{
            padding:7px 10px;
            overflow-x:auto;
        }
        .campaign-messages-app .thread-filter-row{
            flex-wrap:nowrap;
        }
        .campaign-messages-app .thread-item{
            min-height:82px;
            padding:12px 10px;
        }
        .campaign-messages-app .thread-avatar{
            width:46px;
            height:46px;
        }
        .campaign-messages-app .thread-time{
            max-width:78px;
            overflow:hidden;
            text-overflow:ellipsis;
        }
        .campaign-messages-app .thread-view-head{
            min-height:74px;
            padding:10px 12px;
        }
        .campaign-messages-app .thread-view-avatar{
            width:44px;
            height:44px;
            font-size:15px;
        }
        .campaign-messages-app .thread-view-copy strong{
            font-size:15px;
        }
        .campaign-messages-app .thread-view-copy span{
            font-size:12px;
        }
        .campaign-messages-app .thread-view-head > .thread-state,
        .campaign-messages-app .thread-view-actions > .thread-state{
            align-self:flex-start;
        }
        .campaign-messages-app .thread-messages{
            padding:14px 20px 0;
        }
        .campaign-messages-app .bubble{
            max-width:88%;
        }
        .campaign-messages-app .thread-compose{
            padding:10px 12px;
        }
        .campaign-messages-app .thread-compose-actions{
            align-items:stretch;
            flex-direction:column;
        }
        .campaign-messages-app .thread-compose-actions .btn{
            width:100%;
        }
    }
    /* Campaign Messages workspace rebuilt from the inbox concept sketch. */
    .campaign-messages-app{--msg-ink:#17202b;--msg-muted:#697586;--msg-line:#e4e9f0;--msg-soft:#f6f8fb;--msg-blue:#2563eb;--msg-green:#16834b;--msg-purple:#6d5dfc}
    .campaign-messages-app .panel{height:calc(100vh - 104px);min-height:680px}.campaign-messages-app .section-head{min-height:72px;display:grid;grid-template-columns:minmax(180px,1fr) auto minmax(210px,280px);gap:20px;padding:10px 18px;border-color:var(--msg-line);border-radius:16px 16px 0 0;background:#fff}.campaign-messages-app .section-head h3{color:var(--msg-ink);font-size:22px;font-weight:800;letter-spacing:-.035em}.campaign-messages-app .section-head p{display:block;margin:3px 0 0;color:var(--msg-muted);font-size:12px}.campaign-messages-app .messages-title-block,.campaign-messages-app .messages-owner-select{min-width:0}.campaign-messages-app .messages-owner-select{justify-self:end;width:100%}.campaign-messages-app .messages-owner-select label{display:block;margin:0 0 4px;color:var(--msg-muted);font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.campaign-messages-app .section-head #employeeSelect{width:100%;height:38px;border:1px solid var(--msg-line);border-radius:10px;background:var(--msg-soft);color:var(--msg-ink)}
    .campaign-messages-app .message-channel-tabs{display:flex;align-items:center;gap:8px;padding:5px;border:1px solid var(--msg-line);border-radius:13px;background:var(--msg-soft)}.campaign-messages-app .message-channel-tab,.campaign-messages-app .channel-rail-item{appearance:none;display:inline-flex;align-items:center;justify-content:center;padding:0;border:0;background:transparent;cursor:pointer}.campaign-messages-app .message-channel-tab{width:36px;height:36px;border-radius:9px}.campaign-messages-app .message-channel-tab.active{background:#fff;box-shadow:0 5px 14px rgba(17,24,39,.09)}.campaign-messages-app .channel-icon{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;font-size:15px;font-weight:900;font-style:normal;line-height:1}.campaign-messages-app .channel-linkedin{background:#0a66c2;color:#fff;letter-spacing:-.08em}.campaign-messages-app .channel-whatsapp{background:#20bd5f;color:#fff}.campaign-messages-app .channel-email{border:1px solid #d3dae4;background:#fff;color:#344054}.campaign-messages-app button[disabled] .channel-icon{filter:saturate(.6);opacity:.58}
    .campaign-messages-app .messages-workspace{flex:1 1 auto;min-height:0;display:grid;grid-template-columns:64px clamp(310px,28vw,390px) minmax(0,1fr);border:1px solid var(--msg-line);border-radius:0 0 16px 16px;background:#fff;overflow:hidden}.campaign-messages-app .message-channel-rail{display:flex;flex-direction:column;align-items:center;gap:12px;padding:18px 10px;border-right:1px solid var(--msg-line);background:#fbfcfe}.campaign-messages-app .channel-rail-item{position:relative;width:44px;height:44px;border-radius:12px}.campaign-messages-app .channel-rail-item:hover{background:#eef2f7}.campaign-messages-app .channel-rail-item.active{background:#eaf3ff}.campaign-messages-app .channel-rail-item.active:before{content:'';position:absolute;left:-10px;width:3px;height:26px;border-radius:0 4px 4px 0;background:var(--msg-blue)}.campaign-messages-app .channel-rail-divider{width:30px;height:1px;margin:2px 0;background:var(--msg-line)}
    .campaign-messages-app .messages-inbox-column{min-width:0;min-height:0;display:flex;flex-direction:column;border-right:1px solid var(--msg-line);background:#fff}.campaign-messages-app .mailbox-switcher{width:auto;min-height:58px;flex-wrap:nowrap;gap:8px;padding:10px 14px;border:0;border-bottom:1px solid var(--msg-line)}.campaign-messages-app .mailbox-tab,.campaign-messages-app .mailbox-tab[data-mailbox="outbox"].active{min-height:36px;padding:0 14px;border-color:#d8dee8;border-radius:10px;color:#475467}.campaign-messages-app .mailbox-tab.active{border-color:var(--msg-green);background:var(--msg-green);color:#fff;box-shadow:0 5px 14px rgba(22,131,75,.18)}.campaign-messages-app .mailbox-tab[data-mailbox="outbox"].active{border-color:#f0b656;background:#fff6e6;color:#9a5b00}.campaign-messages-app .thread-list-panel{flex:1 1 auto;height:auto;border:0;border-radius:0}.campaign-messages-app .thread-list-toolbar{padding:10px 14px;border-bottom:1px solid var(--msg-line)}.campaign-messages-app .thread-filter-row{flex-wrap:nowrap;gap:6px;overflow-x:auto;scrollbar-width:none}.campaign-messages-app .thread-filter-row::-webkit-scrollbar{display:none}.campaign-messages-app .thread-filter-btn{flex:0 0 auto;min-height:31px;padding:0 11px;border-color:#d8dee8;color:#566274;font-size:12px}.campaign-messages-app .thread-filter-btn.active{border-color:#b9cdf7;background:#eef5ff;color:var(--msg-blue)}
    .campaign-messages-app .thread-item{min-height:92px;gap:11px;padding:15px 13px;border-color:#edf0f4}.campaign-messages-app .thread-item.active{background:#eef5fb}.campaign-messages-app .thread-item.active:before{width:4px;background:var(--msg-blue)}.campaign-messages-app .thread-avatar{width:48px;height:48px;background:#536783}.campaign-messages-app .thread-name{font-size:14px;font-weight:700;color:var(--msg-ink)}.campaign-messages-app .thread-company,.campaign-messages-app .thread-time{color:var(--msg-muted)}.campaign-messages-app .thread-time{font-size:11px}.campaign-messages-app .thread-snippet{margin-top:7px;color:#536173;font-size:12px}
    .campaign-messages-app .thread-view{height:100%;min-height:0;border:0;border-radius:0;background:#fff}.campaign-messages-app .thread-view-head{min-height:78px;padding:12px 18px;border-color:var(--msg-line)}.campaign-messages-app .thread-view-avatar{width:50px;height:50px;font-size:16px}.campaign-messages-app .thread-view-copy strong{color:var(--msg-ink);font-size:16px}.campaign-messages-app .thread-view-copy span{color:var(--msg-muted);font-size:12px}.campaign-messages-app .thread-messages{padding:20px 26px 0;background:linear-gradient(180deg,#fbfcff 0,#fff 18%)}.campaign-messages-app #threadMessages .thread-messages-inner{max-width:900px;padding-bottom:0}
    .campaign-messages-app #threadMessages .message-row:last-child{margin-bottom:10px!important;padding-bottom:10px!important;}
    .campaign-messages-app .bubble{max-width:min(680px,78%);padding:8px 0}.campaign-messages-app .message-row.right .bubble,.campaign-messages-app .message-row.is-outgoing .bubble{padding:13px 15px;border-color:#cfe0ff;border-radius:18px 18px 5px 18px;background:#edf4ff}.campaign-messages-app .message-row.is-pending-approval .bubble{border-radius:18px 18px 5px 18px}.campaign-messages-app .bubble-text{font-size:14px;line-height:1.55}.campaign-messages-app .thread-compose{gap:9px;padding:11px 16px 13px;border-top:1px solid var(--msg-line);box-shadow:0 -8px 22px rgba(30,41,59,.035)}.campaign-messages-app .rich-editor{min-height:86px;padding:13px 14px;border:1px solid transparent;border-radius:11px;background:#f6f7f9}.campaign-messages-app .thread-compose-actions .btn-primary{background:var(--msg-purple);box-shadow:0 7px 16px rgba(109,93,252,.2)}
    @media(max-width:1100px){.campaign-messages-app .messages-workspace{grid-template-columns:54px 320px minmax(0,1fr)}.campaign-messages-app .section-head{grid-template-columns:1fr auto minmax(180px,230px)}}
    @media(max-width:800px){.campaign-messages-app .panel{height:auto;min-height:0}.campaign-messages-app .section-head{grid-template-columns:1fr auto;gap:10px;padding:12px}.campaign-messages-app .messages-title-block p{display:none}.campaign-messages-app .messages-owner-select{grid-column:1/-1}.campaign-messages-app .messages-workspace{grid-template-columns:52px minmax(0,1fr);grid-template-rows:500px minmax(640px,auto);overflow:visible}.campaign-messages-app .message-channel-rail{grid-row:1;padding:12px 6px}.campaign-messages-app .messages-inbox-column{grid-row:1;border-right:0}.campaign-messages-app .thread-view{grid-column:1/-1;grid-row:2;border-top:1px solid var(--msg-line)}.campaign-messages-app .thread-list-panel{height:auto;max-height:none;border:0}}
    @media(max-width:520px){.campaign-messages-app .section-head{grid-template-columns:1fr}.campaign-messages-app .message-channel-tabs{grid-row:2;justify-self:start}.campaign-messages-app .messages-owner-select{grid-column:1}.campaign-messages-app .messages-workspace{grid-template-columns:46px minmax(0,1fr);grid-template-rows:460px minmax(620px,auto)}.campaign-messages-app .channel-rail-item{width:38px;height:40px}.campaign-messages-app .mailbox-switcher,.campaign-messages-app .thread-list-toolbar{padding-inline:10px}.campaign-messages-app .thread-messages{padding:16px 12px 0}.campaign-messages-app .bubble{max-width:90%}}

    /* Focused messages shell: no application sidebar or campaign top navigation. */
    .campaign-messages-app .page-body,.page-body:has(.campaign-messages-app){margin-left:0!important;width:100%!important;max-width:none!important;padding:0!important}
    .campaign-messages-app .messages-content{max-width:none!important;margin:0!important;padding:0!important}
    .campaign-messages-app .panel{height:100vh}
    .campaign-messages-app .section-head{border:0!important;border-bottom:1px solid var(--msg-line)!important;border-radius:0!important}
    .campaign-messages-app .messages-workspace{border:0!important;border-radius:0!important}
    .campaign-messages-app .messages-viewer{display:flex;align-items:center;gap:11px;min-width:0}
    .campaign-messages-app .messages-viewer-avatar{position:relative;flex:0 0 auto;width:44px;height:44px;display:flex;align-items:center;justify-content:center;overflow:hidden;border-radius:50%;background:linear-gradient(145deg,#526b88,#334a66);color:#fff;font-size:13px;font-weight:800;box-shadow:0 0 0 3px #edf3f8}
    .campaign-messages-app .messages-viewer-avatar img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:none}.campaign-messages-app .messages-viewer-avatar.has-image img{display:block}
    .campaign-messages-app .messages-viewer small{display:block;margin-bottom:2px;color:var(--msg-muted);font-size:10px;font-weight:800;letter-spacing:.07em;text-transform:uppercase}.campaign-messages-app .messages-viewer strong{display:block;overflow:hidden;color:var(--msg-ink);font-size:15px;font-weight:800;text-overflow:ellipsis;white-space:nowrap}
    .campaign-messages-app .thread-snippet{display:flex!important;align-items:center;gap:7px}.campaign-messages-app .thread-snippet>span:last-child{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .campaign-messages-app .bubble-meta{display:flex!important;align-items:center;gap:7px;flex-wrap:wrap}
    .campaign-messages-app .message-source{flex:0 0 auto;display:inline-flex;align-items:center;gap:4px;width:auto;min-height:19px;padding:0 6px;border-radius:5px;font-size:9px;font-weight:900;line-height:1;letter-spacing:-.02em}.campaign-messages-app .message-source span{font-size:9px;font-weight:800;letter-spacing:0}.campaign-messages-app .message-source.linkedin{background:#e8f3ff;color:#0a66c2}.campaign-messages-app .message-source.whatsapp{background:#e9f9ef;color:#128c47}
    @media(max-width:800px){.campaign-messages-app .messages-content{padding:0!important}.campaign-messages-app .panel{height:auto}.campaign-messages-app .section-head{grid-template-columns:minmax(0,1fr) auto}.campaign-messages-app .messages-owner-select{grid-column:1/-1}}
    /* Channel selectors removed; channel context now lives beside each thread name. */
    .campaign-messages-app .message-channel-tabs,.campaign-messages-app .message-channel-rail{display:none!important}
    .campaign-messages-app .section-head{grid-template-columns:minmax(0,1fr) minmax(230px,340px)}
    .campaign-messages-app .messages-workspace{grid-template-columns:clamp(330px,30vw,430px) minmax(0,1fr)}
    .campaign-messages-app .thread-name-line,.campaign-messages-app .thread-view-name-line{display:flex;align-items:center;gap:7px;min-width:0}
    .campaign-messages-app .thread-view-name-link{
        color:inherit;
        text-decoration:underline;
        text-decoration-thickness:1px;
        text-underline-offset:3px;
    }
    .campaign-messages-app .thread-view-name-link:hover{color:#0a66c2;}
    .campaign-messages-app .thread-name-line .thread-name{flex:0 1 auto;min-width:0}
    .campaign-messages-app .thread-channel-badges{display:inline-flex;align-items:center;gap:4px;flex:0 0 auto}
    .campaign-messages-app .thread-channel-link{display:inline-flex;text-decoration:none;border-radius:5px}
    .campaign-messages-app .thread-channel-link:focus-visible{outline:2px solid var(--msg-blue);outline-offset:2px}
    .campaign-messages-app .thread-name-line .message-source,.campaign-messages-app .thread-view-name-line .message-source{min-height:18px;padding:0 5px;border-radius:5px;font-size:8px}
    .campaign-messages-app .thread-name-line .message-source span,.campaign-messages-app .thread-view-name-line .message-source span{font-size:8px}
    .campaign-messages-app .message-source.email{background:#f1f3f6;color:#526173}
    @media(max-width:800px){.campaign-messages-app .section-head{grid-template-columns:1fr}.campaign-messages-app .messages-owner-select{grid-column:1}.campaign-messages-app .messages-workspace{grid-template-columns:minmax(0,1fr);grid-template-rows:500px minmax(640px,auto)}.campaign-messages-app .messages-inbox-column{grid-column:1;grid-row:1}.campaign-messages-app .thread-view{grid-column:1;grid-row:2}}
    /* Channel badges are logo-only; names remain available as tooltips. */
    .campaign-messages-app .message-source{width:20px!important;min-width:20px!important;height:20px!important;min-height:20px!important;padding:0!important;justify-content:center!important;border-radius:5px!important}
    .campaign-messages-app .thread-name-line .message-source,.campaign-messages-app .thread-view-name-line .message-source{width:20px!important;min-width:20px!important;height:20px!important;padding:0!important;font-size:9px!important}
    /* Keep logo-only channel marks perfectly centered inside their tiles. */
    .campaign-messages-app .message-source{display:inline-flex!important;align-items:center!important;justify-content:center!important;box-sizing:border-box!important;line-height:1!important;text-align:center!important;vertical-align:middle!important}
    .campaign-messages-app .message-source.linkedin{font-family:Arial,Helvetica,sans-serif!important;font-size:10px!important;font-weight:900!important;letter-spacing:-.08em!important;text-indent:-.04em!important}
    .campaign-messages-app .message-source.whatsapp,.campaign-messages-app .message-source.email{font-size:11px!important;text-indent:0!important}
    /* Direct return path to Campaign Dashboard. */
    .campaign-messages-app .messages-dashboard-back{flex:0 0 auto;display:inline-flex;align-items:center;gap:7px;min-height:38px;padding:0 11px;border:1px solid #dce3ec;border-radius:10px;background:#fff;color:#42526a;box-shadow:0 3px 10px rgba(15,23,42,.05);transition:background .15s ease,border-color .15s ease,transform .15s ease}
    .campaign-messages-app .messages-dashboard-back:hover{border-color:#bdc8d8;background:#f6f8fb;transform:translateX(-1px)}
    .campaign-messages-app .messages-dashboard-back>span{font-size:17px;line-height:1}.campaign-messages-app .messages-dashboard-back>strong{font-size:11px;font-weight:800;white-space:nowrap}
    @media(max-width:620px){.campaign-messages-app .messages-dashboard-back{width:38px;padding:0;justify-content:center}.campaign-messages-app .messages-dashboard-back>strong{display:none}}
    /* Connection freshness for the selected inbox owner. */
    .campaign-messages-app .section-head{grid-template-columns:minmax(0,1fr) auto minmax(230px,340px)}
    .campaign-messages-app .connection-statuses{display:flex;align-items:center;justify-content:center;gap:11px;min-width:0}
    .campaign-messages-app .connection-status{position:relative;display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border:1px solid var(--msg-line);border-radius:11px;background:#fff;color:#667085;box-shadow:0 3px 10px rgba(15,23,42,.045)}
    .campaign-messages-app .connection-status svg{width:20px;height:20px;display:block}
    .campaign-messages-app .connection-status[href]{cursor:pointer;text-decoration:none;transition:border-color .15s ease,box-shadow .15s ease,transform .15s ease}
    .campaign-messages-app .connection-status[href]:hover{border-color:#b8c4d4;box-shadow:0 6px 16px rgba(15,23,42,.1);transform:translateY(-1px)}
    .campaign-messages-app .connection-status.linkedin{color:#0a66c2}.campaign-messages-app .connection-status.whatsapp{color:#18a957}.campaign-messages-app .connection-status.email{color:#475467}
    .campaign-messages-app .connection-status-dot{position:absolute;right:-3px;bottom:-3px;width:12px;height:12px;border:2px solid #fff;border-radius:50%;background:#98a2b3;box-shadow:0 0 0 1px rgba(15,23,42,.08)}
    .campaign-messages-app .connection-status[data-freshness="green"] .connection-status-dot{background:#22a75a}.campaign-messages-app .connection-status[data-freshness="yellow"] .connection-status-dot{background:#e9aa18}.campaign-messages-app .connection-status[data-freshness="gray"] .connection-status-dot{background:#98a2b3}
    @media(max-width:900px){.campaign-messages-app .section-head{grid-template-columns:minmax(0,1fr) auto minmax(190px,260px)}.campaign-messages-app .connection-statuses{gap:7px}.campaign-messages-app .connection-status{width:34px;height:34px}}
    @media(max-width:700px){.campaign-messages-app .section-head{grid-template-columns:minmax(0,1fr) auto}.campaign-messages-app .messages-owner-select{grid-column:1/-1}.campaign-messages-app .connection-status{width:32px;height:32px}}
    /* The support chat is intentionally disabled on the focused Messages workspace. */
    .audience-assistant{display:none!important}
    /* Incoming messages use a distinct white card, mirroring outgoing bubbles. */
    .campaign-messages-app .message-row.left .bubble,
    .campaign-messages-app .message-row.is-incoming .bubble{padding:13px 15px!important;border:1px solid #e1e7ef!important;border-radius:18px 18px 18px 5px!important;background:#fff!important;box-shadow:0 3px 10px rgba(15,23,42,.045)!important;color:#17202b!important}
    /* Remove the 520px ceiling inherited from campaigns-shared.php and use the complete grid-row height. */
    .campaign-messages-app #threadMessages{max-height:none}
    .campaign-messages-app #threadMessages .thread-messages-inner{min-height:100%;height:100%;justify-content:flex-start!important}
    .campaign-messages-app #threadMessages .message-history-spacer{flex:1 1 auto;min-height:0;width:100%;pointer-events:none}
    .campaign-messages-app #threadMessages .message-row,
    .campaign-messages-app #threadMessages .message-date-separator{flex:0 0 auto}
    .campaign-messages-app .message-search-card{width:min(620px,calc(100vw - 32px))}
    .campaign-messages-app .message-search-scopes{display:flex;gap:8px;margin:0 0 18px}
    .campaign-messages-app .message-search-scope{min-height:38px;padding:0 16px;border:1px solid #d8dee8;border-radius:10px;background:#fff;color:#566274;font-weight:700;cursor:pointer}
    .campaign-messages-app .message-search-scope.active{border-color:#b9cdf7;background:#eef5ff;color:var(--msg-blue)}
    .campaign-messages-app .message-search-field{display:flex;gap:10px;align-items:flex-end}
    .campaign-messages-app .message-search-field .field{flex:1 1 auto;margin:0}
    .campaign-messages-app .message-search-field input{width:100%;min-height:42px;padding:0 12px;border:1px solid #d8dee8;border-radius:10px;background:#fff;color:var(--msg-ink);font-size:14px}
    .campaign-messages-app .message-search-results{max-height:360px;margin-top:18px;overflow:auto;border-top:1px solid var(--msg-line)}
    .campaign-messages-app .message-search-result{display:block;width:100%;padding:13px 4px;border:0;border-bottom:1px solid var(--msg-line);background:#fff;text-align:left;cursor:pointer}
    .campaign-messages-app .message-search-result:hover,.campaign-messages-app .message-search-result:focus-visible{background:#f7f9fc}
    .campaign-messages-app .message-search-result strong,.campaign-messages-app .message-search-result span,.campaign-messages-app .message-search-result small{display:block}
    .campaign-messages-app .message-search-result span{margin-top:3px;color:#526173;font-size:13px}
    .campaign-messages-app .message-search-result small{margin-top:5px;color:var(--msg-muted);font-size:11px}
    .campaign-messages-app .message-search-status{padding:18px 4px;color:var(--msg-muted);font-size:13px}
</style>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <div class="page-body no-top-margin">
            <div class="app-shell campaign-messages-app">
                <main class="content messages-content">
                    <section class="panel">
                        <div class="panel-inner">
                            <div class="section-head">
                                <div class="messages-viewer" id="messagesViewer">
                                    <a class="messages-dashboard-back" href="campaigns-dashboard.php" aria-label="Go back to Campaign Dashboard" title="Go back to Campaign Dashboard"><span aria-hidden="true">&#8592;</span><strong>Campaign Dashboard</strong></a>
                                    <div class="messages-viewer-avatar" id="messagesViewerAvatar"><span>?</span></div>
                                    <div>
                                        <small>Viewing messages for</small>
                                        <strong id="messagesViewerName">Loading inbox…</strong>
                                    </div>
                                </div>
                                <div class="connection-statuses" id="connectionStatuses" aria-label="Connection synchronization status">
                                    <a class="connection-status linkedin" data-channel="linkedin" data-freshness="gray" title="LinkedIn · No synchronization" aria-label="LinkedIn: No synchronization">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M5.2 3.7A2.2 2.2 0 1 1 5.2 8a2.2 2.2 0 0 1 0-4.3ZM3.3 9.5h3.8V21H3.3V9.5Zm6.1 0H13v1.6h.1c.5-.9 1.7-2 3.5-2 3.8 0 4.5 2.5 4.5 5.8V21h-3.8v-5.4c0-1.3 0-3-1.9-3s-2.2 1.4-2.2 2.9V21H9.4V9.5Z"/></svg>
                                        <i class="connection-status-dot" aria-hidden="true"></i>
                                    </a>
                                    <a class="connection-status whatsapp" data-channel="whatsapp" data-freshness="gray" title="WhatsApp · No synchronization" aria-label="WhatsApp: No synchronization">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M20.5 3.5A11.6 11.6 0 0 0 2.2 17.4L.6 23.2l6-1.6A11.6 11.6 0 0 0 20.5 3.5Zm-8.2 17.1c-1.8 0-3.6-.5-5.1-1.4l-.4-.2-3.5.9.9-3.4-.2-.4a9.5 9.5 0 1 1 8.3 4.5Zm5.2-7.1c-.3-.1-1.7-.8-2-1-.3-.1-.5-.1-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.2-.7.1-2-.8-3.4-2.9-3.5-3.1-.2-.3 0-.5.1-.6l.5-.6c.2-.2.2-.3.3-.5.1-.2 0-.4 0-.6l-.9-2.1c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.3 1.4 3.5c.2.2 2.4 3.7 5.9 5.2.8.4 1.5.6 2 .7.8.3 1.6.2 2.2.1.7-.1 1.7-.7 1.9-1.4.2-.7.2-1.3.2-1.4-.1-.2-.3-.3-.6-.5Z"/></svg>
                                        <i class="connection-status-dot" aria-hidden="true"></i>
                                    </a>
                                    <span class="connection-status email" data-channel="email" data-freshness="gray" title="Email · No synchronization" aria-label="Email: No synchronization">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 5h18v14H3zM3 7l9 6 9-6"/></svg>
                                        <i class="connection-status-dot" aria-hidden="true"></i>
                                    </span>
                                </div>
                                <div class="messages-owner-select">
                                    <label for="employeeSelect">Inbox owner</label>
                                    <select class="select" id="employeeSelect">
                                        <?php foreach ($campaignEmployees as $item): ?>
                                            <option value="<?php echo (int)$item['id']; ?>"><?php echo htmlspecialchars($item['full_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="messages-workspace">
                                <div class="messages-inbox-column">
                                    <div class="mailbox-switcher" id="mailboxSwitcher" aria-label="Message mailbox">
                                        <button class="mailbox-tab active" type="button" data-mailbox="inbox"><span>Inbox</span><small id="inboxMailboxMeta">LinkedIn conversations</small><strong id="inboxMailboxCount">0</strong></button>
                                        <button class="mailbox-tab" type="button" data-mailbox="outbox"><span>Outbox</span><small id="outboxMailboxMeta">Pending approval</small><strong id="outboxMailboxCount">0</strong></button>
                                    </div>
                                    <div class="inbox-access-banner" id="inboxAccessBanner" hidden></div>
                                    <div class="thread-list-panel">
                                        <div class="thread-list-toolbar">
                                            <strong id="mailboxPanelTitle"><span class="linkedin-mini-mark">in</span> Messaging</strong>
                                            <span id="threadListMeta">Select a user to load the inbox.</span>
                                            <div class="thread-filter-row" id="threadFilterRow">
                                                <button class="thread-filter-btn active" type="button" data-filter="hearted">Hearted</button>
                                                <button class="thread-filter-btn" type="button" data-filter="needs-reply">Needs reply</button>
                                                <button class="thread-filter-btn" type="button" data-filter="all">All</button>
                                            </div>
                                            <div class="thread-list-summary" id="threadListSummary"></div>
                                        </div>
                                        <div class="thread-list" id="threadList"></div>
                                    </div>
                                </div>
                                <div class="thread-view" id="threadView"></div>
                            </div>
                        </div>
                    </section>
                    <div class="modal-overlay" id="editReplyModal">
                        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="editReplyModalTitle">
                            <div class="modal-head">
                                <div>
                                    <h3 id="editReplyModalTitle">Review message</h3>
                                    <p>Update the prepared message, then approve and send it.</p>
                                </div>
                                <button class="modal-close" type="button" id="closeEditReplyModal">×</button>
                            </div>
                            <div class="modal-body">
                                <div class="field">
                                    <label for="editReplyEditor">Message</label>
                                    <div class="composer-toolbar" data-editor-toolbar="editReplyEditor"><button class="composer-tool" type="button" data-command="bold"><strong>B</strong></button><button class="composer-tool" type="button" data-emoji="😊">😊</button><button class="composer-tool" type="button" data-emoji="😃">😃</button><button class="composer-tool" type="button" data-emoji="👍">👍</button><button class="composer-tool" type="button" data-emoji="🔥">🔥</button><button class="composer-tool" type="button" data-emoji="🙏">🙏</button></div><div class="rich-editor" id="editReplyEditor" contenteditable="true" data-placeholder="Update the message..."></div><small class="composer-hint">Use the B button for bold, click emojis, or press Ctrl/Cmd + Enter to save and send.</small>
                                </div>
                                <div class="modal-actions">
                                    <button class="btn btn-danger" type="button" id="deleteEditReplyBtn">Delete message</button>
                                    <button class="btn btn-outline" type="button" id="cancelEditReplyBtn">Cancel</button>
                                    <button class="btn btn-primary" type="button" id="saveEditReplyBtn">Approve and send</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-overlay" id="messageSearchModal">
                        <div class="modal-card message-search-card" role="dialog" aria-modal="true" aria-labelledby="messageSearchModalTitle">
                            <div class="modal-head">
                                <div>
                                    <h3 id="messageSearchModalTitle">Find conversations</h3>
                                    <p>Choose where to search, then enter a name or message text.</p>
                                </div>
                                <button class="modal-close" type="button" id="closeMessageSearchModal" aria-label="Close search">×</button>
                            </div>
                            <div class="modal-body">
                                <div class="message-search-scopes" id="messageSearchScopes" role="group" aria-label="Search in">
                                    <button class="message-search-scope active" type="button" data-search-scope="everything" aria-pressed="true">Everything</button>
                                    <button class="message-search-scope" type="button" data-search-scope="person" aria-pressed="false">Person</button>
                                    <button class="message-search-scope" type="button" data-search-scope="message" aria-pressed="false">Message</button>
                                </div>
                                <form class="message-search-field" id="messageSearchForm">
                                    <div class="field">
                                        <label for="messageSearchInput">Search</label>
                                        <input id="messageSearchInput" name="query" type="search" minlength="2" maxlength="200" autocomplete="off" placeholder="Enter at least 2 characters" required>
                                    </div>
                                    <button class="btn btn-primary" type="submit" id="messageSearchSubmit">Search</button>
                                </form>
                                <div class="message-search-results" id="messageSearchResults" aria-live="polite" hidden></div>
                            </div>
                        </div>
                    </div>
                    <div class="thread-context-menu" id="threadContextMenu" role="menu" aria-hidden="true">
                        <button class="thread-context-score" type="button" data-context-action="score" role="menuitem" hidden>
                            <strong data-context-purchase-score title="Purchase likelihood" aria-label="Purchase likelihood: 0 out of 100">🔥 0/100</strong>
                            <span data-context-icp-score title="ICP likelihood" aria-label="ICP likelihood: 0 out of 100">🎯 0/100</span>
                        </button>
                        <button type="button" data-context-action="mark-unread" role="menuitem">● Mark as unread</button>
                        <button type="button" data-context-action="favorite" role="menuitem"><span data-context-favorite-icon>&#9825;</span><span data-context-favorite-label>Mark with heart</span></button>
                        <button type="button" data-context-action="share" role="menuitem">🔗 Share with Colleague</button>
                    </div>
                    <div class="modal-overlay" id="scoreReasonModal">
                        <div class="modal-card score-reason-card" role="dialog" aria-modal="true" aria-labelledby="scoreReasonModalTitle">
                            <div class="modal-head"><div><h3 id="scoreReasonModalTitle">Thread score</h3><p id="scoreReasonBasis"></p></div><button class="modal-close" type="button" id="closeScoreReasonModal">×</button></div>
                            <div class="modal-body"><div class="score-reason-grid" id="scoreReasonContent"></div></div>
                        </div>
                    </div>
                    <div class="messages-toast" id="messagesToast" aria-live="polite"></div>
                </main>
            </div>
        </div>
    </div>
</div>
<script>
    (function(){
        const api = window.CohertaCampaignsApi;
        const messagesApp = document.querySelector('.campaign-messages-app');
        const employeeSelect = document.getElementById('employeeSelect');
        const mailboxSwitcher = document.getElementById('mailboxSwitcher');
        const mailboxPanelTitle = document.getElementById('mailboxPanelTitle');
        const inboxMailboxCount = document.getElementById('inboxMailboxCount');
        const inboxMailboxMeta = document.getElementById('inboxMailboxMeta');
        const outboxMailboxCount = document.getElementById('outboxMailboxCount');
        const outboxMailboxMeta = document.getElementById('outboxMailboxMeta');
        const threadList = document.getElementById('threadList');
        const threadView = document.getElementById('threadView');
        const threadListMeta = document.getElementById('threadListMeta');
        const threadListSummary = document.getElementById('threadListSummary');
        const threadFilterRow = document.getElementById('threadFilterRow');
        const editReplyModal = document.getElementById('editReplyModal');
        const editReplyEditor = document.getElementById('editReplyEditor');
        const closeEditReplyModalBtn = document.getElementById('closeEditReplyModal');
        const cancelEditReplyBtn = document.getElementById('cancelEditReplyBtn');
        const deleteEditReplyBtn = document.getElementById('deleteEditReplyBtn');
        const saveEditReplyBtn = document.getElementById('saveEditReplyBtn');
        const messageSearchModal = document.getElementById('messageSearchModal');
        const closeMessageSearchModalBtn = document.getElementById('closeMessageSearchModal');
        const messageSearchScopes = document.getElementById('messageSearchScopes');
        const messageSearchForm = document.getElementById('messageSearchForm');
        const messageSearchInput = document.getElementById('messageSearchInput');
        const messageSearchSubmit = document.getElementById('messageSearchSubmit');
        const messageSearchResults = document.getElementById('messageSearchResults');
        const threadContextMenu = document.getElementById('threadContextMenu');
        const scoreReasonModal = document.getElementById('scoreReasonModal');
        const scoreReasonContent = document.getElementById('scoreReasonContent');
        const scoreReasonBasis = document.getElementById('scoreReasonBasis');
        const closeScoreReasonModalBtn = document.getElementById('closeScoreReasonModal');
        const messagesToast = document.getElementById('messagesToast');
        const inboxAccessBanner = document.getElementById('inboxAccessBanner');
        const messagesViewerName = document.getElementById('messagesViewerName');
        const messagesViewerAvatar = document.getElementById('messagesViewerAvatar');
        const connectionStatuses = document.getElementById('connectionStatuses');
        const initialEmployeeOptions = Array.from(employeeSelect.options).map(option => ({
            id: Number(option.value),
            full_name: option.textContent.trim()
        }));

        const endpointPath = '/campaigns/messages';
        const accessEndpointPath = '/campaigns/messages/access';
        const searchEndpointPath = '/campaigns/messages/search';
        let activeEmployeeId = employeeSelect.value;
        let activeThreadId = null;
        let activeMailbox = 'inbox';
        let activeFilter = 'hearted';
        let selectedThreadOverridesFilter = false;
        let applyAutomaticFilterPriority = true;
        let localThreads = [];
        let activeEmployee = null;
        let activeThread = null;
        let pendingEditMessageId = null;
        let lastFocusedEditor = null;
        let isChangingHashFromCode = false;
        let contextThreadId = null;
        let toastTimer = null;
        let keepOpenedUnreadThreadVisibleId = null;
        let accessibleInboxes = [];
        let inaccessibleInboxes = [];
        let accessActionInProgress = false;
        let preserveAccessBannerOnNextLoad = false;
        let activeSearchScope = 'everything';
        let searchRequestController = null;

        function esc(value){
            return String(value ?? '')
                .replaceAll('&','&amp;')
                .replaceAll('<','&lt;')
                .replaceAll('>','&gt;')
                .replaceAll('"','&quot;')
                .replaceAll("'",'&#39;');
        }

        function parseUrlState(){
            const hashParams = new URLSearchParams((window.location.hash || '').replace(/^#/, ''));
            const queryParams = new URLSearchParams(window.location.search || '');
            return {
                employeeId: hashParams.get('user_id') || hashParams.get('employee_id') || queryParams.get('user_id') || queryParams.get('employee_id') || '',
                threadId: hashParams.get('thread_id') || queryParams.get('thread_id') || '',
                mailbox: hashParams.get('mailbox') || queryParams.get('mailbox') || '',
                filter: hashParams.get('filter') || hashParams.get('group') || queryParams.get('filter') || queryParams.get('group') || '',
                requestToken: hashParams.get('request_token') || hashParams.get('accept_token') || queryParams.get('request_token') || queryParams.get('accept_token') || '',
                acceptId: hashParams.get('accept_id') || queryParams.get('accept_id') || ''
            };
        }

        function buildThreadLink(employeeId, threadId){
            const url = new URL(window.location.href);
            url.searchParams.delete('user_id');
            url.searchParams.delete('employee_id');
            url.searchParams.delete('thread_id');
            url.searchParams.delete('mailbox');
            url.searchParams.delete('filter');
            url.searchParams.delete('group');
            const params = new URLSearchParams();
            if (employeeId) params.set('user_id', String(employeeId));
            if (threadId) params.set('thread_id', String(threadId));
            params.set('mailbox', activeMailbox);
            params.set('filter', activeFilter);
            url.hash = params.toString();
            return url.toString();
        }

        function syncThreadUrl(employeeId, threadId){
            const nextUrl = buildThreadLink(employeeId, threadId);
            if (nextUrl === window.location.href) return;
            isChangingHashFromCode = true;
            window.history.replaceState({}, '', nextUrl);
            window.setTimeout(() => { isChangingHashFromCode = false; }, 0);
        }

        function showToast(message, type = ''){
            if (!messagesToast) return;
            window.clearTimeout(toastTimer);
            messagesToast.textContent = message;
            messagesToast.className = 'messages-toast' + (type ? ' ' + type : '');
            window.requestAnimationFrame(() => messagesToast.classList.add('show'));
            toastTimer = window.setTimeout(() => {
                messagesToast.classList.remove('show');
            }, 2400);
        }



        function openMessageSearchModal(){
            if (!messageSearchModal) return;
            messageSearchModal.classList.add('show');
            messageSearchResults.hidden = true;
            messageSearchResults.innerHTML = '';
            window.setTimeout(() => {
                messageSearchInput.focus();
                messageSearchInput.select();
            }, 0);
        }

        function closeMessageSearchModal(){
            if (!messageSearchModal) return;
            searchRequestController?.abort();
            searchRequestController = null;
            messageSearchModal.classList.remove('show');
        }

        function renderMessageSearchResults(results, query){
            messageSearchResults.hidden = false;
            if (!results.length) {
                messageSearchResults.innerHTML = '<div class="message-search-status">No conversations found for “' + esc(query) + '”.</div>';
                return;
            }

            messageSearchResults.innerHTML = results.map(result => {
                const threadId = Number(result.thread_id ?? result.id ?? result.thread?.id ?? 0);
                const person = result.person_name || result.name || result.thread?.name || 'Unknown person';
                const company = result.company || result.company_name || result.thread?.company || '';
                const excerpt = result.message_excerpt || result.excerpt || result.snippet || result.thread?.snippet || '';
                const matchedIn = result.matched_in || result.match_type || '';
                return '<button class="message-search-result" type="button" data-search-thread-id="' + threadId + '">'
                    + '<strong>' + esc(person) + '</strong>'
                    + (excerpt ? '<span>' + esc(excerpt) + '</span>' : '')
                    + '<small>' + esc([company, matchedIn].filter(Boolean).join(' · ')) + '</small>'
                    + '</button>';
            }).join('');
        }

        async function searchMessageThreads(){
            const query = messageSearchInput.value.trim();
            if (query.length < 2) {
                messageSearchInput.reportValidity();
                return;
            }

            searchRequestController?.abort();
            searchRequestController = new AbortController();
            messageSearchSubmit.disabled = true;
            messageSearchSubmit.textContent = 'Searching…';
            messageSearchResults.hidden = false;
            messageSearchResults.innerHTML = '<div class="message-search-status">Searching conversations…</div>';

            try {
                const params = new URLSearchParams({
                    employee_id: String(activeEmployeeId || ''),
                    scope: activeSearchScope,
                    query,
                    limit: '50'
                });
                const payload = await api.request(searchEndpointPath + '?' + params.toString(), {
                    signal: searchRequestController.signal
                });
                const results = Array.isArray(payload.results) ? payload.results : [];
                renderMessageSearchResults(results, query);
            } catch (error) {
                if (error?.name === 'AbortError') return;
                messageSearchResults.innerHTML = '<div class="message-search-status">Search failed: ' + esc(error.message || 'Please try again.') + '</div>';
            } finally {
                messageSearchSubmit.disabled = false;
                messageSearchSubmit.textContent = 'Search';
                searchRequestController = null;
            }
        }
        function setAccessBanner(message, type = ''){
            if (!inboxAccessBanner) return;
            inboxAccessBanner.textContent = message;
            inboxAccessBanner.className = 'inbox-access-banner show' + (type ? ' ' + type : '');
            inboxAccessBanner.hidden = false;
        }

        function clearAccessBanner(){
            if (!inboxAccessBanner) return;
            inboxAccessBanner.textContent = '';
            inboxAccessBanner.className = 'inbox-access-banner';
            inboxAccessBanner.hidden = true;
        }

        function numericId(value){
            const id = Number(value || 0);
            return Number.isFinite(id) ? id : 0;
        }

        function optionHasValue(value){
            const raw = String(value || '');
            return Array.from(employeeSelect.options).some(option => option.value === raw);
        }

        function employeeDisplayName(employee){
            return String(employee?.full_name || employee?.name || employee?.email || employee?.username || 'This user').trim();
        }
        function employeeAvatarUrl(employee){
            const raw = employee?.avatar_url || employee?.profile_image_url || employee?.profile_image || employee?.image || '';
            if (!raw) return '';
            if (/^https?:\/\//i.test(String(raw))) return String(raw);
            return 'https://api.coherta.com/images/' + String(raw).replace(/^\/+/, '');
        }

        function updateViewerIdentity(employee){
            const resolved = employee || getKnownInbox(activeEmployeeId) || {};
            const name = employeeDisplayName(resolved);
            if (messagesViewerName) messagesViewerName.textContent = name;
            if (!messagesViewerAvatar) return;
            const imageUrl = employeeAvatarUrl(resolved);
            messagesViewerAvatar.innerHTML = '<span>' + esc(initials(name)) + '</span>' + (imageUrl ? '<img src="' + esc(imageUrl) + '" alt="' + esc(name) + '">' : '');
            messagesViewerAvatar.classList.toggle('has-image', Boolean(imageUrl));
            const image = messagesViewerAvatar.querySelector('img');
            image?.addEventListener('error', () => messagesViewerAvatar.classList.remove('has-image'), { once:true });
        }

        function firstSyncValue(source, keys){
            if (!source || typeof source !== 'object') return '';
            for (const key of keys) {
                const value = source[key];
                if (value !== undefined && value !== null && String(value).trim() !== '') return value;
            }
            return '';
        }

        function channelSyncValue(payload, channel){
            const employee = payload?.employee || activeEmployee || {};
            const containers = [
                payload?.connection_statuses, payload?.sync_statuses, payload?.integrations, payload?.connectors,
                employee?.connection_statuses, employee?.sync_statuses, employee?.integrations, employee?.connectors,
                payload, employee
            ];
            const aliases = channel === 'email' ? ['email','office365','outlook','mail'] : [channel];
            const timeKeys = ['last_synced_at','last_sync_at','last_sync','synced_at','sync_at','last_updated_at','updated_at'];
            for (const container of containers) {
                if (!container || typeof container !== 'object') continue;
                for (const alias of aliases) {
                    const nestedValue = firstSyncValue(container[alias], timeKeys);
                    if (nestedValue) return nestedValue;
                    const flatValue = firstSyncValue(container, timeKeys.map(key => alias + '_' + key).concat([
                        alias + '_last_synced', alias + '_last_sync', 'last_' + alias + '_sync_at', 'last_' + alias + '_synced_at'
                    ]));
                    if (flatValue) return flatValue;
                }
            }
            return '';
        }

        function normalizedConnectionState(value){
            if (value === true || value === 1) return true;
            if (value === false || value === 0) return false;
            const normalized = String(value ?? '').trim().toLowerCase();
            if (['1','true','yes','connected','healthy','active','online'].includes(normalized)) return true;
            if (['0','false','no','disconnected','inactive','expired','missing','offline'].includes(normalized)) return false;
            return null;
        }

        function channelConnectionState(payload, channel){
            const employee = payload?.employee || activeEmployee || {};
            const aliases = channel === 'email' ? ['email','office365','outlook','mail'] : [channel];
            const containers = [
                payload?.connection_statuses, payload?.integrations, payload?.connectors,
                employee?.connection_statuses, employee?.integrations, employee?.connectors,
                payload, employee
            ];
            const statusKeys = ['connected','is_connected','status','connection_status','connect'];
            for (const container of containers) {
                if (!container || typeof container !== 'object') continue;
                for (const alias of aliases) {
                    const nested = container[alias];
                    if (nested !== undefined && nested !== null && typeof nested !== 'object') {
                        const directState = normalizedConnectionState(nested);
                        if (directState !== null) return directState;
                    }
                    if (nested && typeof nested === 'object') {
                        for (const key of statusKeys) {
                            const nestedState = normalizedConnectionState(nested[key]);
                            if (nestedState !== null) return nestedState;
                        }
                    }
                    for (const key of [alias + '_connected', 'is_' + alias + '_connected', alias + '_status', alias + '_connection_status']) {
                        const flatState = normalizedConnectionState(container[key]);
                        if (flatState !== null) return flatState;
                    }
                }
                if (channel === 'linkedin') {
                    const legacyState = normalizedConnectionState(container.connect);
                    if (legacyState !== null) return legacyState;
                }
            }
            return null;
        }
        function synchronizationFreshness(rawValue){
            if (!rawValue) return { key:'gray', label:'No synchronization' };
            const date = rawValue instanceof Date ? rawValue : new Date(rawValue);
            const ageHours = (Date.now() - date.getTime()) / 3600000;
            if (!Number.isFinite(ageHours) || ageHours < 0) return { key:'gray', label:'No synchronization' };
            if (ageHours <= 12) return { key:'green', label:'Synchronized within the last 12 hours' };
            if (ageHours <= 48) return { key:'yellow', label:'Synchronized within the last 48 hours' };
            return { key:'gray', label:'No synchronization or more than 48 hours ago' };
        }

        function updateConnectionStatuses(payload = null){
            if (!connectionStatuses) return;
            connectionStatuses.querySelectorAll('.connection-status').forEach(item => {
                const channel = item.dataset.channel || '';
                const rawValue = channelSyncValue(payload || {}, channel);
                const freshness = synchronizationFreshness(rawValue);
                const connected = channelConnectionState(payload || {}, channel);
                const channelName = channel === 'linkedin' ? 'LinkedIn' : (channel === 'whatsapp' ? 'WhatsApp' : 'Email');
                const parsedDate = rawValue ? new Date(rawValue) : null;
                const timestamp = parsedDate && Number.isFinite(parsedDate.getTime())
                    ? ' · ' + new Intl.DateTimeFormat(undefined, { dateStyle:'medium', timeStyle:'short' }).format(parsedDate)
                    : '';
                item.dataset.freshness = freshness.key;
                item.dataset.connection = connected === false ? 'disconnected' : (connected === true ? 'connected' : 'unknown');
                if (item instanceof HTMLAnchorElement) {
                    const connectUrl = channel === 'linkedin'
                        ? 'https://www.coherta.com/app/connect_linkedin'
                        : (channel === 'whatsapp' ? 'https://www.coherta.com/app/connect_whatsapp' : '');
                    if (connected === false && connectUrl) {
                        item.href = connectUrl;
                    } else {
                        item.removeAttribute('href');
                    }
                }
                const connectionLabel = connected === false ? ' · Disconnected — click to connect' : '';
                item.title = channelName + ' · ' + freshness.label + timestamp + connectionLabel;
                item.setAttribute('aria-label', channelName + ': ' + freshness.label + timestamp + connectionLabel);
            });
        }
        function messageChannel(item, thread = null){
            const raw = String(item?.channel || item?.message_channel || item?.platform || item?.source || item?.provider || thread?.channel || thread?.message_channel || thread?.platform || thread?.source || 'linkedin').toLowerCase();
            if (raw.includes('whats')) return { key:'whatsapp', label:'WhatsApp' };
            if (raw.includes('mail') || raw.includes('email')) return { key:'email', label:'Email' };
            return { key:'linkedin', label:'LinkedIn' };
        }

        function linkedInThreadUrl(thread){
            const directUrl = firstNonEmpty(
                thread?.linkedin_thread_url,
                thread?.linkedin_conversation_url,
                thread?.messaging_url,
                thread?.conversation_url,
                thread?.thread_url,
                thread?.linkedin?.thread_url,
                thread?.linkedin?.conversation_url,
                thread?.linkedin?.messaging_url
            );
            if (/^https:\/\/([a-z0-9-]+\.)?linkedin\.com\/messaging\/(thread|compose)\//i.test(directUrl)) {
                return directUrl;
            }

            const linkedinUrl = firstNonEmpty(thread?.linkedin_url, thread?.linkedin?.url);
            if (/^https:\/\/([a-z0-9-]+\.)?linkedin\.com\/messaging\/(thread|compose)\//i.test(linkedinUrl)) {
                return linkedinUrl;
            }

            const conversationId = firstNonEmpty(
                thread?.linkedin_conversation_id,
                thread?.conversation_id,
                thread?.linkedin_thread_id,
                thread?.provider_thread_id,
                thread?.external_thread_id,
                thread?.linkedin?.conversation_id,
                thread?.linkedin?.thread_id
            );
            if (conversationId) {
                return 'https://www.linkedin.com/messaging/thread/' + encodeURIComponent(conversationId) + '/';
            }

            const recipientId = firstNonEmpty(
                thread?.participant_linkedin_id,
                thread?.linkedin_member_id,
                thread?.linkedin_public_identifier,
                thread?.public_identifier,
                thread?.linkedin?.member_id,
                thread?.linkedin?.public_identifier
            );
            const profileMatch = linkedinUrl.match(/^https:\/\/([a-z0-9-]+\.)?linkedin\.com\/in\/([^/?#]+)/i);
            const recipient = recipientId || (profileMatch ? decodeURIComponent(profileMatch[2]) : '');
            return recipient
                ? 'https://www.linkedin.com/messaging/compose/?recipient=' + encodeURIComponent(recipient)
                : '';
        }

        function channelBadgeHtml(item, thread = null, linkToThread = false){
            const channel = messageChannel(item, thread);
            const mark = channel.key === 'linkedin' ? 'in' : (channel.key === 'whatsapp' ? '☎' : '✉');
            const badge = '<span class="message-source ' + channel.key + '" aria-hidden="true">' + mark + '</span>';
            const threadUrl = linkToThread && channel.key === 'linkedin' ? linkedInThreadUrl(thread) : '';
            if (!threadUrl) return '<span title="' + esc(channel.label) + '" aria-label="' + esc(channel.label) + '">' + badge + '</span>';
            return '<a class="thread-channel-link" href="' + esc(threadUrl) + '" target="_blank" rel="noopener" title="Open LinkedIn conversation" aria-label="Open LinkedIn conversation">' + badge + '</a>';
        }

        function threadChannelBadgesHtml(thread){
            const values = [];
            const append = value => {
                if (Array.isArray(value)) return value.forEach(append);
                if (value && typeof value === 'object') return append(value.channel || value.platform || value.source || value.name);
                String(value || '').split(/[,|]/).forEach(part => { if (part.trim()) values.push(part.trim()); });
            };
            append(thread?.channels);
            append(thread?.message_channels);
            append(thread?.platforms);
            append(thread?.channel);
            append(thread?.message_channel);
            (thread?.messages || []).forEach(message => append(message?.channel || message?.message_channel || message?.platform || message?.source || message?.provider));
            if (!values.length) values.push('linkedin');
            const unique = [];
            values.forEach(value => {
                const channel = messageChannel({ channel:value });
                if (!unique.some(item => item.key === channel.key)) unique.push(channel);
            });
            return '<span class="thread-channel-badges">' + unique.map(channel => channelBadgeHtml({ channel:channel.key }, thread, true)).join('') + '</span>';
        }

        function inboxHasPendingRequest(employee){
            if (!employee) return false;
            if (employee.request_pending === true || employee.request_pending === 1) return true;
            const pendingRaw = String(employee.request_pending ?? '').trim().toLowerCase();
            if (['1','true','yes','pending'].includes(pendingRaw)) return true;
            return !isNullishDate(employee.rquesed_access || employee.requested_access || employee.requested_at);
        }

        function findInboxById(list, employeeId){
            const id = numericId(employeeId);
            if (!id) return null;
            return (list || []).find(item => numericId(item.id) === id) || null;
        }

        function getAccessibleInbox(employeeId){
            return findInboxById(accessibleInboxes, employeeId);
        }

        function getInaccessibleInbox(employeeId){
            return findInboxById(inaccessibleInboxes, employeeId);
        }

        function getKnownInbox(employeeId){
            return getAccessibleInbox(employeeId)
                || getInaccessibleInbox(employeeId)
                || findInboxById(initialEmployeeOptions, employeeId);
        }

        function employeeOptionLabel(employee, accessType){
            const name = employeeDisplayName(employee);
            if (accessType === 'own') return name + ' — your own inbox';
            if (accessType === 'granted') return name + ' — access granted';
            if (inboxHasPendingRequest(employee)) return '⏳ ' + name + ' — request sent';
            return '🔒 ' + name + ' — request access';
        }

        function renderEmployeeSelect(preferredValue = ''){
            if (!employeeSelect) return;
            const selectedValue = String(preferredValue || activeEmployeeId || employeeSelect.value || '');

            if (!accessibleInboxes.length && !inaccessibleInboxes.length) {
                if (selectedValue && optionHasValue(selectedValue)) employeeSelect.value = selectedValue;
                return;
            }

            const html = [];
            if (accessibleInboxes.length) {
                html.push('<optgroup label="Accessible inboxes">');
                accessibleInboxes.forEach(employee => {
                    const id = numericId(employee.id);
                    const type = employee.inbox_access_type === 'own' ? 'own' : 'granted';
                    html.push('<option value="' + id + '" data-access="' + type + '">' + esc(employeeOptionLabel(employee, type)) + '</option>');
                });
                html.push('</optgroup>');
            }
            if (inaccessibleInboxes.length) {
                html.push('<optgroup label="Inboxes without access">');
                inaccessibleInboxes.forEach(employee => {
                    const id = numericId(employee.id);
                    const pending = inboxHasPendingRequest(employee);
                    html.push('<option value="' + id + '" data-access="none" data-pending="' + (pending ? '1' : '0') + '">' + esc(employeeOptionLabel(employee, 'none')) + '</option>');
                });
                html.push('</optgroup>');
            }

            employeeSelect.innerHTML = html.join('');
            const matchingOption = Array.from(employeeSelect.options).find(option => option.value === selectedValue);
            if (matchingOption) {
                employeeSelect.value = selectedValue;
            } else if (accessibleInboxes[0]?.id) {
                employeeSelect.value = String(accessibleInboxes[0].id);
            }
        }

        function syncAccessOverview(payload, preferredValue = ''){
            if (!payload || typeof payload !== 'object') return;
            if (Array.isArray(payload.accessible_inboxes)) accessibleInboxes = payload.accessible_inboxes;
            if (Array.isArray(payload.inaccessible_inboxes)) inaccessibleInboxes = payload.inaccessible_inboxes;
            renderEmployeeSelect(preferredValue || payload.employee?.id || activeEmployeeId);
        }

        function removeAccessTokenFromUrl(){
            const url = new URL(window.location.href);
            url.searchParams.delete('request_token');
            url.searchParams.delete('accept_token');
            url.searchParams.delete('accept_id');

            const hashParams = new URLSearchParams((window.location.hash || '').replace(/^#/, ''));
            hashParams.delete('request_token');
            hashParams.delete('accept_token');
            hashParams.delete('accept_id');
            url.hash = hashParams.toString();
            window.history.replaceState({}, '', url.toString());
        }

        function accessRequestCardHtml(employee){
            const id = numericId(employee?.id);
            const name = employeeDisplayName(employee);
            const pending = inboxHasPendingRequest(employee);
            return `
                <div class="inbox-access-card ${pending ? 'pending' : ''}">
                    <span class="access-state-pill ${pending ? '' : 'locked'}">${pending ? '⏳ Waiting for approval' : '🔒 No access yet'}</span>
                    <h4>${pending ? 'Access has been requested' : 'Request inbox access'}</h4>
                    <p>${pending
                ? `You have already requested access to ${esc(name)}'s inbox. ${esc(name)} will receive an email with an approval link and must approve your request before the inbox becomes available here.`
                : `You do not have access to ${esc(name)}'s inbox yet. Send a request so ${esc(name)} receives an email with an approval link.`}
                    </p>
                    <div class="access-card-actions">
                        ${pending
                ? '<button class="btn btn-outline" type="button" disabled>Request sent</button>'
                : `<button class="btn btn-primary" type="button" data-request-inbox-access="${id}">Request access</button>`}
                    </div>
                </div>
            `;
        }

        function renderNoAccessInbox(employee){
            const id = numericId(employee?.id || activeEmployeeId);
            const pending = inboxHasPendingRequest(employee);
            activeEmployeeId = String(id || activeEmployeeId || '');
            activeEmployee = null;
            activeThread = null;
            activeThreadId = null;
            setLocalThreads([]);
            renderMailboxChrome();
            renderFilterButtons();
            renderEmployeeSelect(activeEmployeeId);
            threadListMeta.textContent = pending
                ? 'You have requested access. The user must approve it by email.'
                : 'You do not have access to this inbox yet.';
            threadListSummary.innerHTML = '<span class="thread-summary-pill ' + (pending ? 'needs-reply' : 'read') + '">' + (pending ? 'Waiting for approval' : 'Access required') + '</span>';
            threadList.innerHTML = accessRequestCardHtml(employee);
            threadView.innerHTML = accessRequestCardHtml(employee);
            syncThreadUrl(activeEmployeeId, null);
        }

        function markInboxRequestPending(employeeId, accessId = null){
            const id = numericId(employeeId);
            const index = inaccessibleInboxes.findIndex(item => numericId(item.id) === id);
            const existing = index >= 0 ? inaccessibleInboxes[index] : getKnownInbox(id) || { id };
            const updated = {
                ...existing,
                id,
                inbox_access_id: accessId || existing.inbox_access_id || null,
                request_pending: true,
                rquesed_access: existing.rquesed_access || new Date().toISOString()
            };
            if (index >= 0) inaccessibleInboxes[index] = updated;
            else inaccessibleInboxes.push(updated);
            renderEmployeeSelect(id);
            return updated;
        }

        async function requestInboxAccess(employeeId, button = null){
            const id = numericId(employeeId);
            if (!id || accessActionInProgress) return;
            const employee = getInaccessibleInbox(id) || getKnownInbox(id) || { id };

            try {
                accessActionInProgress = true;
                if (button) {
                    button.disabled = true;
                    button.dataset.originalText = button.textContent;
                    button.textContent = 'Sending request...';
                }

                const payload = await api.request(accessEndpointPath, {
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'request',
                        user_id: id
                    })
                });

                if (payload?.already_granted) {
                    preserveAccessBannerOnNextLoad = true;
                    setAccessBanner('You already have access to this inbox. Loading the inbox now.', 'success');
                    await loadEmployee(id);
                    return;
                }

                const updatedEmployee = markInboxRequestPending(id, payload?.id || null);
                renderNoAccessInbox(updatedEmployee);
                setAccessBanner('The request has been sent. The user will receive an email and must approve access through the link in the email.', 'success');
                showToast('The request has been sent.');
            } catch (error) {
                setAccessBanner(error.message || 'Could not send the access request.', 'error');
                showToast(error.message || 'Could not send the request.', 'error');
                if (button) {
                    button.disabled = false;
                    button.textContent = button.dataset.originalText || 'Request access';
                }
            } finally {
                accessActionInProgress = false;
            }
        }

        async function acceptInboxAccessRequest(requestToken, acceptId = ''){
            const token = String(requestToken || '').trim();
            const id = String(acceptId || '').trim();
            if (!token && !id) return;

            try {
                const payload = await api.request(accessEndpointPath, {
                    method: 'PATCH',
                    body: JSON.stringify({
                        action: 'accept',
                        request_token: token,
                        accept_id: id
                    })
                });
                removeAccessTokenFromUrl();
                preserveAccessBannerOnNextLoad = true;
                setAccessBanner(payload?.already_granted ? 'Access was already approved.' : 'Access has been approved. The user now has access to your inbox.', 'success');
                showToast('Access has been approved.');
            } catch (error) {
                removeAccessTokenFromUrl();
                preserveAccessBannerOnNextLoad = true;
                setAccessBanner(error.message || 'Could not approve the request.', 'error');
                showToast(error.message || 'Could not approve the request.', 'error');
            }
        }

        function hideThreadContextMenu(){
            if (!threadContextMenu) return;
            threadContextMenu.classList.remove('show');
            threadContextMenu.setAttribute('aria-hidden', 'true');
            contextThreadId = null;
        }

        function showThreadContextMenu(event, threadId){
            if (!threadContextMenu) return;
            event.preventDefault();
            event.stopPropagation();
            contextThreadId = Number(threadId);

            const thread = localThreads.find(item => Number(item.id) === contextThreadId) || activeThread || null;
            const isFavorite = threadIsFavorite(thread);
            const favoriteIcon = threadContextMenu.querySelector('[data-context-favorite-icon]');
            const favoriteLabel = threadContextMenu.querySelector('[data-context-favorite-label]');
            if (favoriteIcon) favoriteIcon.innerHTML = isFavorite ? '&#9829;' : '&#9825;';
            if (favoriteLabel) favoriteLabel.textContent = isFavorite ? 'Remove heart' : 'Mark with heart';
            const scoreButton = threadContextMenu.querySelector('[data-context-action="score"]');
            const purchaseScore = threadScoreValue(thread, 'purchase_likelihood');
            const icpScore = threadScoreValue(thread, 'icp_match_likelihood');
            if (scoreButton) scoreButton.hidden = !thread?.score;
            const purchaseLabel = threadContextMenu.querySelector('[data-context-purchase-score]');
            const icpLabel = threadContextMenu.querySelector('[data-context-icp-score]');
            if (purchaseLabel) { purchaseLabel.textContent = '🔥 ' + purchaseScore + '/100'; purchaseLabel.setAttribute('aria-label', 'Purchase likelihood: ' + purchaseScore + ' out of 100'); }
            if (icpLabel) {
                icpLabel.textContent = '🎯 ' + icpScore + '/100';
                icpLabel.setAttribute('aria-label', 'ICP likelihood: ' + icpScore + ' out of 100');
            }

            threadContextMenu.classList.add('show');
            threadContextMenu.setAttribute('aria-hidden', 'false');

            const menuRect = threadContextMenu.getBoundingClientRect();
            const padding = 8;
            const left = Math.min(event.clientX, window.innerWidth - menuRect.width - padding);
            const top = Math.min(event.clientY, window.innerHeight - menuRect.height - padding);

            threadContextMenu.style.left = Math.max(padding, left) + 'px';
            threadContextMenu.style.top = Math.max(padding, top) + 'px';
        }

        async function copyTextToClipboard(value){
            const text = String(value || '');
            if (!text) return false;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(text);
                return true;
            }

            const input = document.createElement('textarea');
            input.value = text;
            input.setAttribute('readonly', 'readonly');
            input.style.position = 'fixed';
            input.style.left = '-9999px';
            document.body.appendChild(input);
            input.select();
            const copied = document.execCommand('copy');
            document.body.removeChild(input);
            return copied;
        }

        async function shareThreadWithColleague(threadId){
            const id = Number(threadId || contextThreadId || activeThreadId);
            if (!id) return;

            const link = buildThreadLink(activeEmployeeId, id);
            try {
                await copyTextToClipboard(link);
                showToast('URL’en er kopieret til udklipsholderen.');
            } catch (error) {
                showToast('Could not copy the URL to the clipboard.', 'error');
            }
        }

        async function markThreadAsUnread(threadId){
            const id = Number(threadId || contextThreadId || activeThreadId);
            if (!id) return;

            try {
                const index = localThreads.findIndex(item => Number(item.id) === id);
                if (index >= 0) {
                    localThreads[index] = {
                        ...localThreads[index],
                        status: 'unread',
                        thread_status: 'unread'
                    };
                }
                if (activeThread && Number(activeThread.id) === id) {
                    activeThread = {
                        ...activeThread,
                        status: 'unread',
                        thread_status: 'unread'
                    };
                }

                renderThreadList();
                if (activeThread && Number(activeThread.id) === id) {
                    renderThreadView(activeEmployee, activeThread);
                }

                const payload = await api.request(endpointPath, {
                    method: 'PATCH',
                    body: JSON.stringify({
                        action: 'mark_unread',
                        employee_id: Number(activeEmployeeId),
                        user_id: Number(activeEmployeeId),
                        thread_id: id,
                        status: 'unread',
                        thread_status: 'unread'
                    })
                });

                syncAccessOverview(payload, activeEmployeeId);
                setLocalThreads(Array.isArray(payload.threads) && payload.threads.length ? payload.threads : localThreads);
                if (payload.selected_thread) {
                    mergeSelectedThreadIntoLocalList(payload.selected_thread, 'unread');
                }
                activeEmployee = payload.employee || activeEmployee;
                updateViewerIdentity(activeEmployee);

                if (activeThread && Number(activeThread.id) === id) {
                    activeThread = {
                        ...activeThread,
                        ...(payload.selected_thread || {}),
                        status: 'unread',
                        thread_status: 'unread'
                    };
                    renderThreadView(activeEmployee, activeThread);
                }
                renderThreadList();
                showToast('Dialogen er markeret som unread.');
            } catch (error) {
                showToast(error.message || 'Could not mark the conversation as unread.', 'error');
            }
        }

        async function toggleThreadFavorite(threadId){
            const id = Number(threadId || activeThreadId);
            if (!id) return;

            const currentThread = localThreads.find(item => Number(item.id) === id) || activeThread || null;
            const previousValue = threadIsFavorite(currentThread);
            const nextValue = !previousValue;

            setLocalThreadFavorite(id, nextValue);
            renderThreadList();
            if (activeThread && Number(activeThread.id) === id) {
                renderThreadView(activeEmployee, activeThread);
            }

            try {
                const payload = await api.request(endpointPath, {
                    method: 'PATCH',
                    body: JSON.stringify({
                        action: 'set_favorite',
                        employee_id: Number(activeEmployeeId),
                        user_id: Number(activeEmployeeId),
                        thread_id: id,
                        is_favorite: nextValue ? 1 : 0,
                        favorite: nextValue ? 1 : 0
                    })
                });

                syncAccessOverview(payload, activeEmployeeId);
                setLocalThreads(Array.isArray(payload.threads) && payload.threads.length ? payload.threads : localThreads);
                if (payload.selected_thread) {
                    mergeSelectedThreadIntoLocalList(payload.selected_thread);
                }
                activeEmployee = payload.employee || activeEmployee;
                updateViewerIdentity(activeEmployee);

                if (activeThread && Number(activeThread.id) === id) {
                    activeThread = {
                        ...activeThread,
                        ...(payload.selected_thread || {}),
                        is_favorite: nextValue ? 1 : 0,
                        favorite: nextValue ? 1 : 0
                    };
                    renderThreadView(activeEmployee, activeThread);
                }
                renderThreadList();
                showToast(nextValue ? 'Conversation marked with heart.' : 'Heart removed from conversation.');
            } catch (error) {
                setLocalThreadFavorite(id, previousValue);
                renderThreadList();
                if (activeThread && Number(activeThread.id) === id) {
                    renderThreadView(activeEmployee, activeThread);
                }
                showToast(error.message || 'Could not update the heart marker.', 'error');
            }
        }

        function setLocalThreadRead(threadId){
            const id = Number(threadId);
            const index = localThreads.findIndex(item => Number(item.id) === id);
            if (index >= 0) {
                localThreads[index] = {
                    ...localThreads[index],
                    status: 'read',
                    thread_status: 'read'
                };
            }
            if (activeThread && Number(activeThread.id) === id) {
                activeThread = {
                    ...activeThread,
                    status: 'read',
                    thread_status: 'read'
                };
            }
        }

        async function markThreadAsRead(threadId){
            const id = Number(threadId || activeThreadId);
            if (!id) return;

            try {
                const payload = await api.request(endpointPath, {
                    method: 'PATCH',
                    body: JSON.stringify({
                        action: 'mark_read',
                        employee_id: Number(activeEmployeeId),
                        user_id: Number(activeEmployeeId),
                        thread_id: id,
                        status: 'read',
                        thread_status: 'read'
                    })
                });

                syncAccessOverview(payload, activeEmployeeId);
                setLocalThreads(Array.isArray(payload.threads) && payload.threads.length ? payload.threads : localThreads);
                if (payload.selected_thread) {
                    mergeSelectedThreadIntoLocalList(payload.selected_thread, readStatusForOpenedThread(payload.selected_thread));
                }
                activeEmployee = payload.employee || activeEmployee;
                updateViewerIdentity(activeEmployee);
            } catch (error) {
                showToast(error.message || 'Could not mark the conversation as read.', 'error');
            }
        }

        async function copyThreadLink(event){
            if (!activeThreadId) return;
            const link = buildThreadLink(activeEmployeeId, activeThreadId);
            try {
                await copyTextToClipboard(link);
                const btn = event?.currentTarget;
                if (btn) {
                    const original = btn.textContent;
                    btn.textContent = 'Link copied';
                    btn.classList.add('copy-feedback');
                    window.setTimeout(() => {
                        btn.textContent = original;
                        btn.classList.remove('copy-feedback');
                    }, 1400);
                }
                showToast('URL’en er kopieret til udklipsholderen.');
            } catch (error) {
                showToast('Could not copy the URL to the clipboard.', 'error');
            }
        }

        function normalizeText(value){
            const wrapper = document.createElement('div');
            wrapper.innerHTML = String(value || '');
            return (wrapper.textContent || wrapper.innerText || String(value || '')).replace(/\s+/g, ' ').trim();
        }

        function plainSnippet(thread){
            return normalizeText(thread?.snippet || '');
        }

        function hasThreadFavoriteField(thread){
            if (!thread || typeof thread !== 'object') return false;
            return ['is_favorite', 'favorite', 'isFavorited', 'is_favorited', 'hearted', 'has_heart', 'marked_with_heart', 'markedWithHeart'].some(key => Object.prototype.hasOwnProperty.call(thread, key));
        }

        function threadIsFavorite(thread){
            if (!thread) return false;
            const explicit = thread?.is_favorite ?? thread?.favorite ?? thread?.isFavorited ?? thread?.is_favorited ?? thread?.hearted ?? thread?.has_heart ?? thread?.marked_with_heart ?? thread?.markedWithHeart;
            if (explicit !== undefined && explicit !== null && explicit !== '') return truthyFlag(explicit);
            return !isNullishDate(thread?.favorite_at ?? thread?.favorited_at ?? thread?.hearted_at);
        }

        function favoriteCount(){
            return localThreads.filter(threadIsFavorite).length;
        }

        function favoriteButtonHtml(thread, variant = 'list'){
            const id = Number(thread?.id || 0);
            const isFavorite = threadIsFavorite(thread);
            const label = isFavorite ? 'Remove heart from conversation' : 'Mark conversation with heart';
            return `<button class="thread-favorite-btn ${isFavorite ? 'active' : ''} ${variant === 'header' ? 'header' : ''}" type="button" data-favorite-thread-id="${id}" aria-label="${esc(label)}" aria-pressed="${isFavorite ? 'true' : 'false'}" title="${esc(label)}"><span aria-hidden="true">${isFavorite ? '&#9829;' : '&#9825;'}</span></button>`;
        }

        function setLocalThreadFavorite(threadId, isFavorite){
            const id = Number(threadId);
            const favoriteAt = isFavorite ? new Date().toISOString() : null;
            const index = localThreads.findIndex(item => Number(item.id) === id);
            if (index >= 0) {
                localThreads[index] = {
                    ...localThreads[index],
                    is_favorite: isFavorite ? 1 : 0,
                    favorite: isFavorite ? 1 : 0,
                    favorite_at: isFavorite ? (localThreads[index].favorite_at || favoriteAt) : null
                };
            }
            if (activeThread && Number(activeThread.id) === id) {
                activeThread = {
                    ...activeThread,
                    is_favorite: isFavorite ? 1 : 0,
                    favorite: isFavorite ? 1 : 0,
                    favorite_at: isFavorite ? (activeThread.favorite_at || favoriteAt) : null
                };
            }
        }

        function normalizedThreadStatus(thread){
            return String(thread?.thread_status || '')
                .trim()
                .toLowerCase()
                .replace(/[\s-]+/g, '_');
        }

        function isUnread(thread){
            return normalizedThreadStatus(thread) === 'unread';
        }

        function threadNeedsReply(thread){
            return normalizedThreadStatus(thread) === 'needs_reply';
        }

        function threadHasNotSentMessage(thread){
            if (Number(thread?.has_not_sent_message ?? thread?.hasNotSentMessage ?? 0) === 1) return true;
            if (normalizedThreadStatus(thread) === 'not_sent') return true;

            const messages = Array.isArray(thread?.messages) ? thread.messages : [];
            return messages.some(message => {
                if (hasSentAt(message)) return false;
                if (hasPreparedSendAt(message)) return true;
                if (Number(message?.prepared || message?.is_prepared || 0) === 1) return true;
                const status = normalizeIdentity(message?.message_status || message?.send_status || message?.status).replace(/[\s-]+/g, '_');
                return ['prepared', 'ready', 'queued', 'draft'].includes(status);
            });
        }

        function messageIsEditable(message){
            return Number(message?.is_editable ?? message?.editable ?? 0) === 1 || message?.editable === true;
        }

        function messageStatusKey(message){
            return normalizeIdentity(message?.message_status || message?.send_status || message?.status).replace(/[\s-]+/g, '_');
        }

        function messageTextValue(message){
            return message?.text || message?.message_text || message?.html || message?.body || '';
        }

        function messageIsOutgoing(message, employee = activeEmployee){
            if (!message) return false;
            if (messageSide(message, employee) === 'right') return true;
            const explicit = message?.is_outgoing ?? message?.outgoing ?? message?.from_employee ?? message?.is_employee_sender;
            if (explicit !== undefined && explicit !== null && explicit !== '') return truthyFlag(explicit);
            return false;
        }

        function messagePendingApproval(message, thread = null, employee = activeEmployee){
            if (!message || hasSentAt(message)) return false;
            const status = messageStatusKey(message);
            const preparedLike = hasPreparedSendAt(message)
                || Number(message?.prepared || message?.is_prepared || 0) === 1
                || messageIsEditable(message)
                || ['prepared', 'ready', 'queued', 'draft', 'not_ready'].includes(status);

            if (!preparedLike) return false;
            return messageIsOutgoing(message, employee) || threadHasNotSentMessage(thread);
        }

        function pendingApprovalMessagesForThread(thread){
            const messages = Array.isArray(thread?.messages) ? thread.messages : [];
            return messages.filter(message => messagePendingApproval(message, thread, activeEmployee));
        }

        function pendingMessageTimestamp(message){
            const preparedAt = firstNonEmpty(
                message?.prepared_send_at,
                message?.preparedSendAt,
                message?.prepared_at,
                message?.preparedAt,
                message?.updated_at,
                message?.updatedAt,
                message?.created_at,
                message?.createdAt
            );
            const preparedDate = parseDateValue(preparedAt);
            if (preparedDate) return preparedDate.getTime();

            const messageDate = messageDateTime(message);
            return messageDate ? messageDate.getTime() : null;
        }

        function firstPendingApprovalMessage(thread){
            return pendingApprovalMessagesForThread(thread)[0] || null;
        }

        function threadHasPendingApproval(thread){
            return pendingApprovalMessagesForThread(thread).length > 0 || threadHasNotSentMessage(thread);
        }

        function threadPendingApprovalCount(thread){
            const messages = pendingApprovalMessagesForThread(thread);
            return messages.length || (threadHasPendingApproval(thread) ? 1 : 0);
        }

        function sentOutgoingMessagesForThread(thread){
            const messages = Array.isArray(thread?.messages) ? thread.messages : [];
            return messages.filter(message => messageIsOutgoing(message, activeEmployee) && hasSentAt(message));
        }

        function threadHasSentOutgoingMessage(thread){
            if (sentOutgoingMessagesForThread(thread).length > 0) return true;
            return /^you\s*:/i.test(plainSnippet(thread));
        }

        function threadHasOutboxActivity(thread){
            return threadHasPendingApproval(thread) || threadHasSentOutgoingMessage(thread);
        }

        function outboxThreadGroups(){
            const sorted = sortedThreads();
            const pending = sorted.filter(threadHasPendingApproval);
            const sent = sorted.filter(thread => !threadHasPendingApproval(thread) && threadHasSentOutgoingMessage(thread));
            return { pending, sent };
        }

        function pendingApprovalTotal(){
            return outboxThreadGroups().pending.reduce((sum, thread) => sum + threadPendingApprovalCount(thread), 0);
        }

        function outboxSnippet(thread){
            const pending = firstPendingApprovalMessage(thread);
            if (pending) return normalizeText(messageTextValue(pending));
            const sent = sentOutgoingMessagesForThread(thread)[0];
            if (sent) return normalizeText(messageTextValue(sent));
            return plainSnippet(thread);
        }

        function threadIsUnanswered(thread){
            return normalizedThreadStatus(thread) === 'unanswered';
        }

        function shouldMarkThreadReadOnOpen(thread){
            return isUnread(thread) || threadNeedsReply(thread);
        }

        function readStatusForOpenedThread(thread){
            return shouldMarkThreadReadOnOpen(thread) ? 'read' : null;
        }

        function openedThreadForDisplay(thread){
            const openedStatus = readStatusForOpenedThread(thread);
            if (!openedStatus) return thread;
            return {
                ...thread,
                status: openedStatus,
                thread_status: openedStatus
            };
        }

        function stateLabel(thread){
            if (threadNeedsReply(thread)) return 'Needs reply';
            if (isUnread(thread)) return 'Unread';
            if (threadHasPendingApproval(thread)) return 'Pending approval';
            if (threadIsUnanswered(thread)) return 'Unanswered';
            return 'Read';
        }

        function stateClass(thread){
            if (threadNeedsReply(thread)) return 'needs-reply';
            if (isUnread(thread)) return 'unread';
            if (threadHasPendingApproval(thread)) return 'pending-approval';
            if (threadIsUnanswered(thread)) return 'unanswered';
            return 'read';
        }

        function stateDescription(thread){
            if (threadNeedsReply(thread)) return 'Reply needed';
            if (isUnread(thread)) return 'New message';
            if (threadHasPendingApproval(thread)) return 'Review and send';
            if (threadIsUnanswered(thread)) return 'Unanswered';
            return 'Opened';
        }

        function parseDateValue(value){
            if (value === null || value === undefined) return null;
            const raw = String(value).trim();
            if (!raw || raw === '0000-00-00' || raw === '0000-00-00 00:00:00' || raw.toLowerCase() === 'null') return null;

            const normalizedRaw = raw.replace(/\s+/g, ' ').trim();

            const isoLike = normalizedRaw.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{1,2})[:.](\d{2})(?::(\d{2}))?)?/);
            if (isoLike) {
                const year = Number(isoLike[1]);
                const month = Number(isoLike[2]) - 1;
                const day = Number(isoLike[3]);
                const hour = Number(isoLike[4] || 0);
                const minute = Number(isoLike[5] || 0);
                const second = Number(isoLike[6] || 0);
                const dt = new Date(year, month, day, hour, minute, second);
                return Number.isNaN(dt.getTime()) ? null : dt;
            }

            const numericDanishDate = normalizedRaw.match(/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{4})(?:\s+(\d{1,2})[:.](\d{2}))?/);
            if (numericDanishDate) {
                const dt = new Date(
                    Number(numericDanishDate[3]),
                    Number(numericDanishDate[2]) - 1,
                    Number(numericDanishDate[1]),
                    Number(numericDanishDate[4] || 0),
                    Number(numericDanishDate[5] || 0),
                    0
                );
                return Number.isNaN(dt.getTime()) ? null : dt;
            }

            const danishDate = normalizedRaw.toLowerCase().match(/^(\d{1,2})\.?\s+([a-zæøå]{3,})\.?\s+(\d{4})(?:\s+(\d{1,2})[:.](\d{2}))?/i);
            if (danishDate) {
                const months = {
                    jan:0, januar:0,
                    feb:1, februar:1,
                    mar:2, marts:2,
                    apr:3, april:3,
                    maj:4,
                    jun:5, juni:5,
                    jul:6, juli:6,
                    aug:7, august:7,
                    sep:8, sept:8, september:8,
                    okt:9, oktober:9,
                    nov:10, november:10,
                    dec:11, december:11
                };
                const month = months[danishDate[2].replace('.', '')];
                if (month !== undefined) {
                    const dt = new Date(
                        Number(danishDate[3]),
                        month,
                        Number(danishDate[1]),
                        Number(danishDate[4] || 0),
                        Number(danishDate[5] || 0),
                        0
                    );
                    return Number.isNaN(dt.getTime()) ? null : dt;
                }
            }

            const parsed = Date.parse(normalizedRaw.replace(' ', 'T'));
            if (!Number.isNaN(parsed)) return new Date(parsed);

            return null;
        }

        function pad2(value){
            return String(value).padStart(2, '0');
        }

        function dateKeyFromDate(dt){
            if (!(dt instanceof Date) || Number.isNaN(dt.getTime())) return '';
            return dt.getFullYear() + '-' + pad2(dt.getMonth() + 1) + '-' + pad2(dt.getDate());
        }

        function formatClock(dt){
            if (!(dt instanceof Date) || Number.isNaN(dt.getTime())) return '';
            return pad2(dt.getHours()) + ':' + pad2(dt.getMinutes());
        }

        function formatDateLabel(dt){
            if (!(dt instanceof Date) || Number.isNaN(dt.getTime())) return '';
            return dt.toLocaleDateString('da-DK', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }

        function formatMessageDateSeparator(dt){
            if (!(dt instanceof Date) || Number.isNaN(dt.getTime())) return '';
            const today = new Date();
            const yesterday = new Date();
            yesterday.setDate(today.getDate() - 1);

            const key = dateKeyFromDate(dt);
            if (key === dateKeyFromDate(today)) return 'I dag';
            if (key === dateKeyFromDate(yesterday)) return 'I går';
            return formatDateLabel(dt);
        }

        function cleanDateCandidate(value){
            if (value === null || value === undefined) return '';
            const raw = String(value).replace(/\s+/g, ' ').trim();
            if (!raw || raw === '0000-00-00' || raw === '0000-00-00 00:00:00' || raw.toLowerCase() === 'null') return '';
            return raw;
        }

        function firstNonEmpty(...values){
            for (const value of values) {
                const cleaned = cleanDateCandidate(value);
                if (cleaned) return cleaned;
            }
            return '';
        }

        function isTimeOnlyValue(value){
            const raw = cleanDateCandidate(value);
            return /^\d{1,2}[:.]\d{2}(?::\d{2})?$/.test(raw);
        }

        function messageDatePartRaw(message){
            return firstNonEmpty(
                message?.date_heading,
                message?.dateHeading,
                message?.date_label,
                message?.dateLabel,
                message?.sent_date,
                message?.sentDate,
                message?.message_date,
                message?.messageDate,
                message?.day,
                message?.date
            );
        }

        function messageTimeRaw(message){
            return firstNonEmpty(
                message?.time,
                message?.sent_at_label,
                message?.sentAtLabel,
                message?.sent_time,
                message?.sentTime,
                message?.message_time,
                message?.messageTime
            );
        }

        function combineDateAndTime(dateValue, timeValue){
            const datePart = cleanDateCandidate(dateValue);
            if (!datePart) return '';
            const timePart = cleanDateCandidate(timeValue);
            if (timePart && isTimeOnlyValue(timePart) && !isTimeOnlyValue(datePart)) {
                return datePart + ' ' + timePart;
            }
            return datePart;
        }

        function latestMessageFromThread(thread){
            const messages = Array.isArray(thread?.messages) ? thread.messages : [];
            if (!messages.length) return null;

            let latest = messages[messages.length - 1];
            let latestTime = -Infinity;

            messages.forEach(message => {
                const dt = parseDateValue(messageDateRaw(message));
                if (dt && dt.getTime() >= latestTime) {
                    latest = message;
                    latestTime = dt.getTime();
                }
            });

            return latest;
        }

        function threadDateRaw(thread){
            const latestMessage = latestMessageFromThread(thread);

            const explicitFullDate = firstNonEmpty(
                thread?.__resolved_date_raw,
                thread?.last_message_at,
                thread?.lastMessageAt,
                thread?.latest_message_at,
                thread?.latestMessageAt,
                thread?.last_sent_at,
                thread?.lastSentAt,
                thread?.latest_sent_at,
                thread?.latestSentAt,
                thread?.last_message_sent_at,
                thread?.lastMessageSentAt,
                thread?.message_at,
                thread?.messageAt,
                thread?.sent_at,
                thread?.sentAt,
                thread?.created_at,
                thread?.createdAt,
                thread?.last_activity_at,
                thread?.lastActivityAt,
                thread?.updated_at,
                thread?.updatedAt
            );

            if (explicitFullDate && !isTimeOnlyValue(explicitFullDate)) {
                return explicitFullDate;
            }

            const datePart = firstNonEmpty(
                thread?.last_message_date,
                thread?.lastMessageDate,
                thread?.latest_message_date,
                thread?.latestMessageDate,
                thread?.thread_date,
                thread?.threadDate,
                thread?.date,
                thread?.date_label,
                thread?.dateLabel,
                thread?.day,
                latestMessage ? messageDatePartRaw(latestMessage) : ''
            );

            const timePart = firstNonEmpty(
                thread?.last_message_time,
                thread?.lastMessageTime,
                thread?.latest_message_time,
                thread?.latestMessageTime,
                thread?.time,
                thread?.time_label,
                thread?.timeLabel,
                latestMessage ? messageTimeRaw(latestMessage) : '',
                explicitFullDate
            );

            const combined = combineDateAndTime(datePart, timePart);
            if (combined) return combined;

            if (latestMessage) {
                const latestRaw = messageDateRaw(latestMessage);
                if (latestRaw) return latestRaw;
            }

            return explicitFullDate || timePart || '';
        }

        function messageDateRaw(message){
            const explicitFullDate = firstNonEmpty(
                message?.sent_at,
                message?.sentAt,
                message?.created_at,
                message?.createdAt,
                message?.message_at,
                message?.messageAt,
                message?.time_at,
                message?.timeAt,
                message?.timestamp,
                message?.datetime,
                message?.date_time,
                message?.dateTime
            );

            if (explicitFullDate && !isTimeOnlyValue(explicitFullDate)) {
                return explicitFullDate;
            }

            const datePart = messageDatePartRaw(message);
            const timePart = messageTimeRaw(message) || explicitFullDate;
            const combined = combineDateAndTime(datePart, timePart);
            if (combined) return combined;

            return explicitFullDate || timePart || datePart || '';
        }

        function threadDateTime(thread){
            const directDate = firstNonEmpty(
                thread?.date,
                thread?.thread_date,
                thread?.threadDate,
                thread?.last_message_date,
                thread?.lastMessageDate,
                thread?.latest_message_date,
                thread?.latestMessageDate
            );
            const directTime = firstNonEmpty(
                thread?.time,
                thread?.thread_time,
                thread?.threadTime,
                thread?.last_message_time,
                thread?.lastMessageTime,
                thread?.latest_message_time,
                thread?.latestMessageTime
            );

            const directCombined = combineDateAndTime(directDate, directTime);
            const directDt = parseDateValue(directCombined || directDate);
            if (directDt) return directDt;

            const raw = threadDateRaw(thread);
            return parseDateValue(raw);
        }

        function messageDateTime(message){
            const directDate = firstNonEmpty(
                message?.date,
                message?.message_date,
                message?.messageDate,
                message?.sent_date,
                message?.sentDate,
                message?.date_label,
                message?.dateLabel
            );
            const directTime = firstNonEmpty(
                message?.time,
                message?.message_time,
                message?.messageTime,
                message?.sent_time,
                message?.sentTime,
                message?.sent_at_label,
                message?.sentAtLabel
            );

            const directCombined = combineDateAndTime(directDate, directTime);
            const directDt = parseDateValue(directCombined || directDate);
            if (directDt) return directDt;

            return parseDateValue(messageDateRaw(message));
        }

        function timestampValue(thread){
            const dt = threadDateTime(thread);
            return dt ? dt.getTime() : null;
        }

        function formatDateTimeTitle(dt){
            if (!(dt instanceof Date) || Number.isNaN(dt.getTime())) return '';
            return formatDateLabel(dt) + ' ' + formatClock(dt);
        }

        function isWithinLast24Hours(dt){
            if (!(dt instanceof Date) || Number.isNaN(dt.getTime())) return false;
            const now = new Date();
            const ageMs = now.getTime() - dt.getTime();
            return (ageMs >= 0 && ageMs < 24 * 60 * 60 * 1000) || dateKeyFromDate(dt) === dateKeyFromDate(now);
        }

        function formatThreadListDate(thread){
            const dt = threadDateTime(thread);
            if (dt) {
                return isWithinLast24Hours(dt) ? formatClock(dt) : formatDateLabel(dt);
            }

            const fallbackDate = firstNonEmpty(
                thread?.date,
                thread?.last_message_date,
                thread?.lastMessageDate,
                thread?.latest_message_date,
                thread?.latestMessageDate,
                thread?.thread_date,
                thread?.threadDate,
                thread?.date_label,
                thread?.dateLabel,
                thread?.day
            );
            if (fallbackDate && !isTimeOnlyValue(fallbackDate)) return fallbackDate;

            return '';
        }

        function formatThreadListTitle(thread){
            const dt = threadDateTime(thread);
            if (dt) return formatDateTimeTitle(dt);
            return firstNonEmpty(thread?.date, thread?.time, thread?.last_message_date, thread?.lastMessageDate, thread?.last_message_time, thread?.lastMessageTime);
        }

        function formatMessageTime(message){
            const dt = messageDateTime(message);
            if (dt) return formatClock(dt);

            const raw = messageTimeRaw(message) || String(message?.time || message?.sent_at_label || '').trim();
            const timeMatch = raw.match(/(\d{1,2})[:.](\d{2})/);
            if (timeMatch) return pad2(timeMatch[1]) + ':' + timeMatch[2];
            return raw;
        }

        function sortedThreads(){
            return [...localThreads].sort((a, b) => {
                const aTime = timestampValue(a);
                const bTime = timestampValue(b);
                if (aTime !== null || bTime !== null) return (bTime || 0) - (aTime || 0);
                return Number(a.__index || 0) - Number(b.__index || 0);
            });
        }

        function shouldKeepOpenedUnreadThreadVisible(thread){
            return activeFilter === 'unread'
                && keepOpenedUnreadThreadVisibleId !== null
                && Number(thread?.id) === Number(keepOpenedUnreadThreadVisibleId)
                && Number(activeThreadId) === Number(keepOpenedUnreadThreadVisibleId);
        }

        function filteredThreads(){
            return sortedThreads().filter(thread => {
                if (activeMailbox === 'outbox') {
                    if (activeFilter === 'pending') return threadHasPendingApproval(thread);
                    if (activeFilter === 'sent') return threadHasSentOutgoingMessage(thread) && !threadHasPendingApproval(thread);
                    return threadHasOutboxActivity(thread);
                }
                if (activeFilter === 'unread') return isUnread(thread);
                if (activeFilter === 'needs-reply') return threadNeedsReply(thread);
                if (activeFilter === 'hot-threads') return threadScoreValue(thread, 'purchase_likelihood') > 70;
                if (activeFilter === 'icp') return threadScoreValue(thread, 'icp_match_likelihood') > 70;
                if (activeFilter === 'hearted') return threadIsFavorite(thread);
                return true;
            });
        }
        function inboxFilterHasMessages(filter){
            const threads = sortedThreads();
            if (filter === 'hearted') return threads.some(threadIsFavorite);
            if (filter === 'hot-threads') return threads.some(thread => threadScoreValue(thread, 'purchase_likelihood') > 70);
            if (filter === 'icp') return threads.some(thread => threadScoreValue(thread, 'icp_match_likelihood') > 70);
            if (filter === 'needs-reply') return threads.some(threadNeedsReply);
            if (filter === 'unread') return threads.some(isUnread);
            return threads.length > 0;
        }

        function resolveEmptyInboxFilter(){
            if (!applyAutomaticFilterPriority || activeMailbox !== 'inbox' || activeFilter === 'all' || selectedThreadOverridesFilter) return false;
            const fallback = { 'hearted':'needs-reply', 'needs-reply':'unread', 'unread':'all' };
            let changed = false;
            while (activeFilter !== 'all' && !inboxFilterHasMessages(activeFilter)) {
                activeFilter = fallback[activeFilter] || 'all';
                changed = true;
            }
            return changed;
        }

        function initials(value){
            return String(value || '')
                .trim()
                .split(/\s+/)
                .filter(Boolean)
                .slice(0, 2)
                .map(part => part.charAt(0).toUpperCase())
                .join('') || '?';
        }

        function avatarUrl(thread){
            if (thread && thread.image) return 'https://api.coherta.com/images/' + String(thread.image).replace(/^\/+/, '');
            if (thread && thread.avatar_url) return String(thread.avatar_url);
            return '';
        }

        function threadScoreValue(thread, key){
            const value = Number(thread?.score?.[key]);
            return Number.isFinite(value) ? Math.max(0, Math.min(100, Math.round(value))) : null;
        }

        function scoreColor(value){
            if (value === null) return '#d0d5dd';
            return 'hsl(' + Math.round(value * 1.2) + ' 72% 42%)';
        }

        function threadScoreArcsHtml(thread){
            const icp = threadScoreValue(thread, 'icp_match_likelihood');
            const purchase = threadScoreValue(thread, 'purchase_likelihood');
            const title = icp === null || purchase === null
                ? 'Thread has not been scored'
                : 'ICP Likelihood ' + icp + '/100 · Purchase Likelihood ' + purchase + '/100';
            return '<svg class="thread-score-arcs" viewBox="0 0 56 56" role="img" aria-label="' + esc(title) + '">' +
              '<title>' + esc(title) + '</title>' +
              '<path class="score-arc-track" d="M4 28 A24 24 0 0 1 52 28"></path>' +
              '<path class="score-arc-value" style="stroke:' + scoreColor(icp) + '" d="M4 28 A24 24 0 0 1 52 28"></path>' +
              '<path class="score-arc-track" d="M52 28 A24 24 0 0 1 4 28"></path>' +
              '<path class="score-arc-value" style="stroke:' + scoreColor(purchase) + '" d="M52 28 A24 24 0 0 1 4 28"></path>' +
            '</svg>';
        }

        function openScoreReasonModal(thread){
            if (!thread?.score || !scoreReasonModal || !scoreReasonContent) return;
            const icp = threadScoreValue(thread, 'icp_match_likelihood');
            const purchase = threadScoreValue(thread, 'purchase_likelihood');
            const reason = thread.score.reason || {};
            const evidence = Array.isArray(reason.evidence) ? reason.evidence : [];
            scoreReasonBasis.textContent = reason.scoring_basis === 'messages_only' ? 'Based on the messages in this thread' : String(reason.scoring_basis || 'Score explanation');
            scoreReasonContent.innerHTML =
              '<div class="score-reason-item"><strong title="Purchase likelihood">🔥 ' + purchase + '/100</strong><p>' + esc(reason.purchase_likelihood_reason || 'No explanation is available.') + '</p></div>' +
              '<div class="score-reason-item"><strong title="ICP likelihood">🎯 ' + icp + '/100</strong><p>' + esc(reason.icp_match_reason || 'No explanation is available.') + '</p></div>' +
              (evidence.length ? '<div class="score-reason-item"><strong>Evidence</strong><ul class="score-evidence">' + evidence.map(item => '<li>' + esc(typeof item === 'string' ? item : JSON.stringify(item)) + '</li>').join('') + '</ul></div>' : '');
            scoreReasonModal.classList.add('show');
        }

        function closeScoreReasonModal(){
            scoreReasonModal?.classList.remove('show');
        }

        function avatarHtml(thread, className){
            const name = thread?.name || thread?.contact_name || 'Contact';
            const imageUrl = avatarUrl(thread);
            return `
      <div class="${className}" data-avatar>
        <span>${esc(initials(name))}</span>
        ${imageUrl ? `<img data-avatar-image src="${esc(imageUrl)}" alt="${esc(name)}">` : ''}
      </div>
    `;
        }

        function applyAvatarFallbacks(container){
            if (!container) return;
            container.querySelectorAll('[data-avatar]').forEach(node => {
                const img = node.querySelector('[data-avatar-image]');
                if (!img) {
                    node.classList.add('avatar-fallback');
                    return;
                }
                if (img.complete && img.naturalWidth === 0) {
                    node.classList.add('avatar-fallback');
                    return;
                }
                img.addEventListener('error', function(){
                    node.classList.add('avatar-fallback');
                }, { once: true });
            });
        }

        function mailboxFilters(){
            if (activeMailbox === 'outbox') {
                return [
                    { key: 'pending', label: 'Pending approval' },
                    { key: 'sent', label: 'Sent' },
                    { key: 'all', label: 'All outbox' }
                ];
            }

            return [
                { key: 'hearted', label: 'Hearted' },
                { key: 'hot-threads', label: 'Hot Threads' },
                { key: 'icp', label: 'ICP' },
                { key: 'needs-reply', label: 'Needs reply' },
                { key: 'all', label: 'All' }
            ];
        }

        function defaultFilterForMailbox(mailbox){
            return mailbox === 'outbox' ? 'pending' : 'hearted';
        }
        function applyUrlViewState(state){
            const requestedMailbox = state?.mailbox === 'outbox' ? 'outbox' : 'inbox';
            const allowedFilters = requestedMailbox === 'outbox'
                ? ['pending', 'sent', 'all']
                : ['hot-threads', 'icp', 'needs-reply', 'hearted', 'unread', 'all'];
            activeMailbox = requestedMailbox;
            activeFilter = allowedFilters.includes(state?.filter)
                ? state.filter
                : defaultFilterForMailbox(requestedMailbox);
        }

        function renderFilterButtons(){
            const filters = mailboxFilters();
            if (!filters.some(filter => filter.key === activeFilter)) activeFilter = defaultFilterForMailbox(activeMailbox);
            threadFilterRow.innerHTML = filters.map(filter => (
                '<button class="thread-filter-btn ' + (filter.key === activeFilter ? 'active' : '') + '" type="button" data-filter="' + esc(filter.key) + '">' + esc(filter.label) + '</button>'
            )).join('');
        }

        function renderMailboxChrome(){
            if (messagesApp) messagesApp.dataset.mailbox = activeMailbox;

            mailboxSwitcher?.querySelectorAll('[data-mailbox]').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.mailbox === activeMailbox);
            });

            const total = localThreads.length;
            const unreadCount = localThreads.filter(isUnread).length;
            const heartedCount = favoriteCount();
            const pendingCount = pendingApprovalTotal();
            const pendingThreads = outboxThreadGroups().pending.length;

            if (inboxMailboxCount) inboxMailboxCount.textContent = String(total);
            if (inboxMailboxMeta) {
                inboxMailboxMeta.textContent = total
                    ? unreadCount + ' unread conversation' + (unreadCount === 1 ? '' : 's')
                    : 'LinkedIn-style conversations';
            }
            if (outboxMailboxCount) outboxMailboxCount.textContent = String(pendingCount);
            if (outboxMailboxMeta) {
                outboxMailboxMeta.textContent = pendingCount
                    ? pendingCount + ' pending approval' + (pendingCount === 1 ? '' : 's')
                    : 'No pending approvals';
            }

            if (mailboxPanelTitle) {
                mailboxPanelTitle.innerHTML = activeMailbox === 'outbox'
                    ? 'Outbox'
                    : '<span class="linkedin-mini-mark">in</span> Messaging';
            }

            return { total, unreadCount, heartedCount, pendingCount, pendingThreads };
        }

        function updateThreadSummary(){
            const counts = renderMailboxChrome();
            renderFilterButtons();

            if (activeMailbox === 'outbox') {
                const sentCount = outboxThreadGroups().sent.length;
                threadListMeta.textContent = counts.pendingCount
                    ? counts.pendingCount + ' prepared message' + (counts.pendingCount === 1 ? '' : 's') + ' waiting for approval.'
                    : 'No prepared messages are waiting for approval.';
                threadListSummary.innerHTML = [
                    '<span class="thread-summary-pill pending-approval">' + counts.pendingCount + ' pending approval</span>',
                    '<span class="thread-summary-pill read">' + sentCount + ' sent</span>'
                ].join('');
                return;
            }

            const needsReplyCount = localThreads.filter(threadNeedsReply).length;
            threadListMeta.textContent = needsReplyCount
                ? needsReplyCount + ' conversation' + (needsReplyCount === 1 ? '' : 's') + ' need action.'
                : 'No conversations need action right now.';
            threadListSummary.innerHTML = [
                '<span class="thread-summary-pill needs-reply">' + needsReplyCount + ' need reply</span>',
                '<span class="thread-summary-pill unread">' + counts.unreadCount + ' unread</span>',
                '<span class="thread-summary-pill read">' + counts.heartedCount + ' hearted</span>'
            ].join('');
        }

        async function selectThreadFromList(threadId){
            const id = Number(threadId);
            if (!id) return null;
            const index = localThreads.findIndex(item => Number(item.id) === id);
            const shouldMarkAsRead = activeMailbox === 'inbox' && index >= 0 && shouldMarkThreadReadOnOpen(localThreads[index]);

            if (keepOpenedUnreadThreadVisibleId !== null && Number(keepOpenedUnreadThreadVisibleId) !== id) {
                keepOpenedUnreadThreadVisibleId = null;
            }

            activeThreadId = id;
            selectedThreadOverridesFilter = true;

            if (shouldMarkAsRead) {
                setLocalThreadRead(id);
            }
            hideThreadContextMenu();
            renderThreadList();
            if (shouldMarkAsRead) {
                await markThreadAsRead(id);
            }
            return loadThread(id);
        }

        function bindFavoriteButtons(container){
            if (!container) return;
            container.querySelectorAll('[data-favorite-thread-id]').forEach(btn => {
                btn.addEventListener('click', function(event){
                    event.preventDefault();
                    event.stopPropagation();
                    toggleThreadFavorite(Number(this.dataset.favoriteThreadId));
                });
            });
        }

        function bindThreadListActions(){
            applyAvatarFallbacks(threadList);

            threadList.querySelectorAll('[data-thread-id]').forEach(item => {
                item.addEventListener('click', function(event){
                    const nestedInteractive = event.target.closest('button,a');
                    if (nestedInteractive && nestedInteractive !== this) return;
                    selectThreadFromList(Number(this.dataset.threadId));
                });
                item.addEventListener('keydown', function(event){
                    if (event.key !== 'Enter' && event.key !== ' ') return;
                    event.preventDefault();
                    selectThreadFromList(Number(this.dataset.threadId));
                });
                item.addEventListener('contextmenu', function(event){
                    showThreadContextMenu(event, Number(this.dataset.threadId));
                });
            });

            bindFavoriteButtons(threadList);

            threadList.querySelectorAll('[data-review-thread-id]').forEach(btn => {
                btn.addEventListener('click', function(event){
                    event.preventDefault();
                    event.stopPropagation();
                    reviewPendingThread(Number(this.dataset.reviewThreadId));
                });
            });
        }

        function outboxThreadHtml(thread){
            const pending = threadHasPendingApproval(thread);
            const statusClass = pending ? 'pending-approval' : 'read';
            const threadDateLabel = formatThreadListDate(thread);
            const threadDateTitle = formatThreadListTitle(thread) || threadDateLabel;
            const pendingCount = threadPendingApprovalCount(thread);
            const classes = [
                'thread-item',
                'outbox-item',
                activeThreadId === Number(thread.id) ? 'active' : '',
                pending ? 'pending-approval' : '',
                threadIsFavorite(thread) ? 'is-favorite' : ''
            ].filter(Boolean).join(' ');

            return `
        <div class="${classes}" data-thread-id="${Number(thread.id)}" tabindex="0" role="button" aria-label="${esc(thread.name || thread.contact_name || 'Outbox thread')}">
          <div class="thread-avatar-wrap">
            ${avatarHtml(thread, 'thread-avatar')}
            ${threadScoreArcsHtml(thread)}
            <span class="thread-status-dot ${statusClass}"></span>
          </div>
          <div class="thread-body">
            <div class="thread-body-top">
              <div>
                <span class="thread-name-line"><span class="thread-name">${esc(thread.name || thread.contact_name || 'Unknown contact')}</span>${threadChannelBadgesHtml(thread)}</span>
                ${thread.company || thread.company_name ? `<span class="thread-company">${esc(thread.company || thread.company_name)}</span>` : ''}
              </div>
              <div class="thread-time-wrap">
                <span class="thread-time" title="${esc(threadDateTitle)}">${esc(threadDateLabel)}</span>
                ${favoriteButtonHtml(thread)}
              </div>
            </div>
            <div class="thread-snippet"><span>${esc(outboxSnippet(thread))}</span></div>
            <div class="thread-item-meta">
              <span class="thread-state ${statusClass}">${pending ? 'Pending approval' : 'Sent'}</span>
              <small>${pending ? pendingCount + ' message' + (pendingCount === 1 ? '' : 's') : 'Outbox history'}</small>
            </div>
          </div>
          ${pending ? `<div class="outbox-actions"><button class="bubble-edit outbox-review-btn" type="button" data-review-thread-id="${Number(thread.id)}">Review</button></div>` : ''}
        </div>
      `;
        }

        function outboxSectionHtml(title, meta, threads, className){
            return `
        <div class="outbox-section ${className}">
          <div class="outbox-section-head"><strong>${esc(title)}</strong><span>${esc(meta)}</span></div>
          ${threads.map(outboxThreadHtml).join('')}
        </div>
      `;
        }

        function renderOutboxList(){
            const groups = outboxThreadGroups();
            const showPending = activeFilter !== 'sent';
            const showSent = activeFilter !== 'pending';
            const sections = [];

            if (showPending && groups.pending.length) {
                sections.push(outboxSectionHtml('Pending approval', 'Review prepared messages before sending', groups.pending, 'pending'));
            }
            if (showSent && groups.sent.length) {
                sections.push(outboxSectionHtml('Sent', 'Recently approved or manual replies', groups.sent, 'sent'));
            }

            if (!sections.length) {
                const label = activeFilter === 'sent' ? 'sent outbox messages' : 'messages pending approval';
                threadList.innerHTML = '<div class="thread-empty"><div class="card"><h4>No ' + label + '</h4><p>Prepared outgoing messages will appear here when they need review.</p></div></div>';
                return;
            }

            threadList.innerHTML = sections.join('');
            bindThreadListActions();
        }

        function renderThreadList(){
            resolveEmptyInboxFilter();
            updateThreadSummary();

            if (activeMailbox === 'outbox') {
                renderOutboxList();
                return;
            }

            const threads = filteredThreads();
            if (!threads.length) {
                const label = activeFilter === 'unread' ? 'unread threads' : (activeFilter === 'needs-reply' ? 'threads that need a reply' : (activeFilter === 'hearted' ? 'hearted threads' : 'threads'));
                const emptyCopy = activeFilter === 'hearted'
                    ? 'Use the heart on a conversation to keep it available here.'
                    : 'No conversations were found in this view.';
                threadList.innerHTML = '<div class="thread-empty"><div class="card"><h4>No ' + label + '</h4><p>' + emptyCopy + '</p></div></div>';
                return;
            }

            threadList.innerHTML = threads.map(thread => {
                const classes = [
                    'thread-item',
                    activeThreadId === Number(thread.id) ? 'active' : '',
                    isUnread(thread) ? 'unread' : '',
                    threadNeedsReply(thread) ? 'needs-reply' : '',
                    threadIsFavorite(thread) ? 'is-favorite' : ''
                ].filter(Boolean).join(' ');
                const statusClass = stateClass(thread);
                const threadDateLabel = formatThreadListDate(thread);
                const threadDateTitle = formatThreadListTitle(thread) || threadDateLabel;
                return `
        <div class="${classes}" data-thread-id="${Number(thread.id)}" tabindex="0" role="button" aria-label="${esc(thread.name || thread.contact_name || 'Conversation')}">
          <div class="thread-avatar-wrap">
            ${avatarHtml(thread, 'thread-avatar')}
            ${threadScoreArcsHtml(thread)}
            <span class="thread-status-dot ${statusClass}"></span>
          </div>
          <div class="thread-body">
            <div class="thread-body-top">
              <div>
                <span class="thread-name-line"><span class="thread-name">${esc(thread.name || thread.contact_name || 'Unknown contact')}</span>${threadChannelBadgesHtml(thread)}</span>
                ${thread.company || thread.company_name ? `<span class="thread-company">${esc(thread.company || thread.company_name)}</span>` : ''}
              </div>
              <div class="thread-time-wrap">
                <span class="thread-time" title="${esc(threadDateTitle)}">${esc(threadDateLabel)}</span>
                ${favoriteButtonHtml(thread)}
              </div>
            </div>
            <div class="thread-snippet"><span>${esc(plainSnippet(thread))}</span></div>
            <div class="thread-item-meta">
              <span class="thread-state ${statusClass}">${stateLabel(thread)}</span>
              <small>${stateDescription(thread)}</small>
            </div>
          </div>
        </div>
                `;
            }).join('');

            bindThreadListActions();
        }

        function scrollMessagesToBottom(){
            const container = threadView.querySelector('.thread-messages');
            if (!container) return;

            const run = () => {
                container.scrollTop = container.scrollHeight;
            };

            window.requestAnimationFrame(() => {
                run();
                window.setTimeout(run, 40);
                window.setTimeout(run, 180);
                window.setTimeout(run, 450);
            });
        }

        function keepThreadAtBottomWhileMediaLoads(){
            const container = threadView.querySelector('.thread-messages');
            if (!container) return;

            container.querySelectorAll('img').forEach(img => {
                if (img.complete) return;
                img.addEventListener('load', scrollMessagesToBottom, { once: true });
                img.addEventListener('error', scrollMessagesToBottom, { once: true });
            });
        }

        function removeCommentNodes(root){
            const walker = document.createTreeWalker(root, NodeFilter.SHOW_COMMENT);
            const comments = [];
            while (walker.nextNode()) comments.push(walker.currentNode);
            comments.forEach(node => node.remove());
        }

        function sanitizeMessageHtml(value){
            const raw = String(value || '').trim();
            if (!raw) return '';
            if (!/<[a-z][\s\S]*>/i.test(raw)) {
                return esc(raw).replace(/\r\n|\r|\n/g, '<br>');
            }

            const template = document.createElement('template');
            template.innerHTML = raw;
            removeCommentNodes(template.content);

            template.content.querySelectorAll('script,style,svg,use,button,iframe,object,embed,form,input,textarea,select,li-icon,.msg-s-event-listitem__actions-container,.emoji-popular-list__container,.artdeco-dropdown,.artdeco-hoverable-trigger,.visually-hidden').forEach(node => node.remove());

            const allowedTags = new Set(['A','P','BR','DIV','SPAN','STRONG','B','EM','I','UL','OL','LI','IMG']);
            const allowedAttrs = {
                'A': new Set(['href','target','rel']),
                'IMG': new Set(['src','alt','width','height'])
            };

            [...template.content.querySelectorAll('*')].forEach(node => {
                if (!allowedTags.has(node.tagName)) {
                    node.replaceWith(...node.childNodes);
                    return;
                }
                [...node.attributes].forEach(attr => {
                    const name = attr.name.toLowerCase();
                    const tagAllowed = allowedAttrs[node.tagName] && allowedAttrs[node.tagName].has(name);
                    if (!tagAllowed) node.removeAttribute(attr.name);
                });
                if (node.tagName === 'A') {
                    const href = node.getAttribute('href') || '';
                    if (/^javascript:/i.test(href)) node.removeAttribute('href');
                    node.setAttribute('target', '_blank');
                    node.setAttribute('rel', 'noopener noreferrer');
                }
                if (node.tagName === 'IMG') {
                    const src = node.getAttribute('src') || '';
                    if (!/^https?:\/\//i.test(src) && !src.startsWith('/')) node.remove();
                }
            });

            return template.innerHTML.trim();
        }

        function normalizeIdentity(value){
            return String(value || "")
                .replace(/<[^>]*>/g, " ")
                .replace(/\s+/g, " ")
                .trim()
                .toLowerCase();
        }

        function messageSenderName(message){
            return String(
                message?.from ||
                message?.sender_name ||
                message?.sender ||
                message?.author ||
                message?.created_by_name ||
                ""
            ).trim();
        }

        function truthyFlag(value){
            if (value === true || value === 1) return true;
            const raw = String(value ?? '').trim().toLowerCase();
            return ['1', 'true', 'yes', 'y', 'employee', 'current_user', 'me', 'you', 'owner', 'right', 'outgoing', 'sent'].includes(raw);
        }

        function falseyFlag(value){
            if (value === false || value === 0) return true;
            const raw = String(value ?? '').trim().toLowerCase();
            return ['0', 'false', 'no', 'n', 'contact', 'lead', 'participant', 'prospect', 'left', 'incoming', 'received'].includes(raw);
        }

        const messageSideCache = new Map();

        function messageCacheKey(message){
            const value = message?.id ??
                message?.message_id ??
                message?.crm_campaign_message_id ??
                message?.event_id ??
                message?.event_urn ??
                message?.urn ??
                '';

            return String(value || '').trim();
        }

        function detectEmployeeSender(message, employee){
            const from = normalizeIdentity(messageSenderName(message));
            const employeeNames = [
                employee?.full_name,
                employee?.name,
                employee?.email,
                activeEmployee?.full_name,
                activeEmployee?.name,
                activeEmployee?.email
            ].map(normalizeIdentity).filter(Boolean);

            if (["you", "me"].includes(from)) return true;
            if (from && employeeNames.includes(from)) return true;

            const senderType = message?.sender_type ?? message?.senderType ?? message?.sent_by_type ?? message?.origin ?? message?.direction;
            if (truthyFlag(senderType)) return true;
            if (falseyFlag(senderType)) return false;

            const explicit = message?.is_employee_sender ?? message?.isEmployeeSender ?? message?.employee_sender ?? message?.from_employee;
            if (explicit !== undefined && explicit !== null && explicit !== "") {
                if (truthyFlag(explicit)) return true;
                if (falseyFlag(explicit)) return false;
            }

            const side = message?.side ?? message?.message_side ?? message?.placement;
            if (truthyFlag(side)) return true;
            if (falseyFlag(side)) return false;

            return null;
        }

        function isEmployeeSender(message, employee){
            const detected = detectEmployeeSender(message, employee);
            if (detected !== null) return detected;

            const key = messageCacheKey(message);
            if (key && messageSideCache.has(key)) {
                return messageSideCache.get(key) === 'right';
            }

            return false;
        }

        function messageSide(message, employee){
            const detected = detectEmployeeSender(message, employee);
            const key = messageCacheKey(message);

            let side;
            if (detected !== null) {
                side = detected ? 'right' : 'left';
            } else if (key && messageSideCache.has(key)) {
                side = messageSideCache.get(key);
            } else {
                side = 'left';
            }

            if (key) {
                messageSideCache.set(key, side);
            }

            return side;
        }

        function isPrepared(message){
            if (Number(message?.prepared || message?.is_prepared || 0) === 1) return true;
            const status = normalizeIdentity(message?.message_status || message?.send_status || message?.status);
            return ['prepared', 'draft', 'queued', 'ready', 'not_ready', 'not ready'].includes(status);
        }

        function hasPreparedSendAt(message){
            return Boolean(message?.prepared_send_at || message?.preparedSendAt);
        }

        function preparedLabel(message){
            const preparedAt = message?.prepared_send_at || message?.preparedSendAt || '';
            if (preparedAt) return 'Ready to send: ' + preparedAt;
            if (message?.prepared_label) return String(message.prepared_label);
            return 'Ready to send';
        }


        function isNullishDate(value){
            if (value === null || value === undefined) return true;
            const raw = String(value).trim().toLowerCase();
            return raw === '' || raw === 'null' || raw === '0000-00-00 00:00:00';
        }

        function hasSentAt(message){
            return !isNullishDate(message?.sent_at || message?.sentAt);
        }

        function canPrepareMessage(message, side){
            const preparedSendAt = message?.prepared_send_at ?? message?.preparedSendAt;
            return side === 'right' && isPrepared(message) && !hasSentAt(message) && isNullishDate(preparedSendAt);
        }

        function findActiveMessage(messageId){
            const messages = Array.isArray(activeThread?.messages) ? activeThread.messages : [];
            return messages.find(message => Number(message.id) === Number(messageId)) || null;
        }

        function messageHelperId(message){
            return message?.helper_id ?? message?.helperId ?? null;
        }

        function messageUsesHelper(message){
            const helperId = messageHelperId(message);
            return helperId !== null && helperId !== undefined && String(helperId).trim() !== '' && Number(helperId) !== 0;
        }

        function setEditorHtml(editor, html){
            if (!editor) return;
            editor.innerHTML = sanitizeMessageHtml(html || '');
        }

        function getEditorPayload(editor){
            if (!editor) return { text: '', html: '' };
            const clone = editor.cloneNode(true);
            clone.querySelectorAll('script,style,button,input,textarea,select').forEach(node => node.remove());
            let html = sanitizeMessageHtml(clone.innerHTML || '');
            const text = normalizeText(html);
            if (!text) html = '';
            return { text, html };
        }

        function focusEditorAtEnd(editor){
            if (!editor) return;
            editor.focus();
            const range = document.createRange();
            range.selectNodeContents(editor);
            range.collapse(false);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            lastFocusedEditor = editor;
        }

        function insertIntoEditor(editor, html){
            if (!editor) return;
            editor.focus();
            document.execCommand('insertHTML', false, html);
            lastFocusedEditor = editor;
        }

        function setupEditor(editor){
            if (!editor || editor.dataset.editorReady === '1') return;
            editor.dataset.editorReady = '1';
            editor.addEventListener('focus', () => { lastFocusedEditor = editor; });
            editor.addEventListener('keyup', () => { lastFocusedEditor = editor; });
            editor.addEventListener('mouseup', () => { lastFocusedEditor = editor; });
            editor.addEventListener('paste', event => {
                event.preventDefault();
                const text = (event.clipboardData || window.clipboardData).getData('text/plain');
                document.execCommand('insertText', false, text);
            });
            editor.addEventListener('keydown', event => {
                if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'b') {
                    event.preventDefault();
                    document.execCommand('bold', false, null);
                }
                if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
                    event.preventDefault();
                    if (editor.id === 'replyEditor') sendReply();
                    if (editor.id === 'editReplyEditor') saveEditedReply();
                }
            });
        }

        function setupToolbar(container){
            (container || document).querySelectorAll('[data-editor-toolbar]').forEach(toolbar => {
                if (toolbar.dataset.toolbarReady === '1') return;
                toolbar.dataset.toolbarReady = '1';
                toolbar.addEventListener('mousedown', event => event.preventDefault());
                toolbar.addEventListener('click', event => {
                    const btn = event.target.closest('[data-command],[data-emoji]');
                    if (!btn) return;
                    const editorId = toolbar.dataset.editorToolbar;
                    const editor = document.getElementById(editorId) || lastFocusedEditor;
                    if (!editor) return;
                    if (btn.dataset.command === 'bold') {
                        editor.focus();
                        document.execCommand('bold', false, null);
                        btn.classList.toggle('active');
                        return;
                    }
                    if (btn.dataset.emoji) {
                        insertIntoEditor(editor, esc(btn.dataset.emoji));
                    }
                });
            });
        }

        function openEditReplyModal(messageId){
            const message = findActiveMessage(messageId);
            pendingEditMessageId = Number(messageId) || null;
            if (!pendingEditMessageId || !message) return;
            setEditorHtml(editReplyEditor, message.text || message.message_text || '');
            if (deleteEditReplyBtn) {
                deleteEditReplyBtn.textContent = messageUsesHelper(message)
                    ? 'Delete message and stop Workflow'
                    : 'Delete message';
            }
            setupEditor(editReplyEditor);
            setupToolbar(editReplyModal);
            editReplyModal.classList.add('show');
            window.requestAnimationFrame(() => focusEditorAtEnd(editReplyEditor));
        }

        function closeEditReplyModal(){
            pendingEditMessageId = null;
            if (editReplyEditor) editReplyEditor.innerHTML = '';
            editReplyModal.classList.remove('show');
        }

        function renderThreadView(employee, thread){
            activeEmployee = employee || activeEmployee;
            activeThread = thread || null;
            if (!thread) {
                threadView.innerHTML = '<div class="thread-messages"><div class="thread-messages-inner"><div class="card"><h4>No thread selected</h4><p>Select a thread from the inbox.</p></div></div></div>';
                return;
            }

            activeThreadId = Number(thread.id);
            const statusClass = stateClass(thread);
            const linkText = '#user_id=' + encodeURIComponent(activeEmployeeId) + '&thread_id=' + encodeURIComponent(activeThreadId);
            const threadPendingCount = threadPendingApprovalCount(thread);
            const linkedInProfileUrl = thread?.linkedin?.profile_url || thread?.linkedin_url || '';
            const linkedInDestination = linkedInProfileUrl || 'https://www.linkedin.com/feed';
            let previousMessageDateKey = '';
            const orderedMessages = (thread.messages || []).map((message, index) => ({ message, index })).sort((a, b) => {
                const aPending = messagePendingApproval(a.message, thread, employee);
                const bPending = messagePendingApproval(b.message, thread, employee);
                if (aPending !== bPending) return aPending ? 1 : -1;
                if (aPending && bPending) {
                    const timeDifference = (pendingMessageTimestamp(a.message) || 0) - (pendingMessageTimestamp(b.message) || 0);
                    if (timeDifference !== 0) return timeDifference;
                }
                return a.index - b.index;
            }).map(item => item.message);
            const messagesHtml = orderedMessages.map(message => {
                const side = messageSide(message, employee);
                const textValue = message.text || message.message_text || '';
                const sender = messageSenderName(message) || (side === 'right' ? 'You' : (thread.name || 'Contact'));
                const pendingApproval = messagePendingApproval(message, thread, employee);
                const pendingTimestamp = pendingApproval ? pendingMessageTimestamp(message) : null;
                const pendingDate = pendingTimestamp ? new Date(pendingTimestamp) : null;
                const messageDate = pendingDate instanceof Date && !Number.isNaN(pendingDate.getTime())
                    ? pendingDate
                    : messageDateTime(message);
                const messageDateKey = messageDate ? dateKeyFromDate(messageDate) : '';
                const showDateSeparator = messageDateKey && messageDateKey !== previousMessageDateKey;
                if (messageDateKey) previousMessageDateKey = messageDateKey;
                const time = pendingDate instanceof Date && !Number.isNaN(pendingDate.getTime())
                    ? formatClock(pendingDate)
                    : formatMessageTime(message);
                const canPrepare = canPrepareMessage(message, side);
                const canEdit = messageIsEditable(message);
                return `
        ${showDateSeparator ? `<div class="message-date-separator"><span>${esc(formatMessageDateSeparator(messageDate))}</span></div>` : ''}
        <div class="message-row ${side === 'right' ? 'is-outgoing' : 'is-incoming'} ${pendingApproval ? 'is-pending-approval' : ''}">
          <div class="bubble">
            <div class="bubble-text" data-message-body>${sanitizeMessageHtml(textValue)}</div>
            <small class="bubble-meta">${channelBadgeHtml(message, thread)}<span>${esc(sender)}${time ? ' · ' + esc(time) : ''}</span></small>
            ${pendingApproval ? `<span class="bubble-pending">Pending approval${hasPreparedSendAt(message) ? ' · ' + esc(preparedLabel(message)) : ''}</span>` : ''}
            ${!pendingApproval && hasPreparedSendAt(message) && !hasSentAt(message) ? `<span class="bubble-status">${esc(preparedLabel(message))}</span>` : ''}
            ${(canPrepare || canEdit || pendingApproval) ? `
              <div class="bubble-actions">
                ${pendingApproval && canEdit ? `<button class="bubble-edit" type="button" data-edit-message-id="${Number(message.id)}">Review and send</button>` : ''}
                ${!pendingApproval && canEdit ? `<button class="bubble-edit" type="button" data-edit-message-id="${Number(message.id)}">Edit</button>` : ''}
                ${canPrepare ? `<button class="bubble-prepare" type="button" data-prepare-message-id="${Number(message.id)}">Mark ready</button>` : ''}
              </div>
            ` : ''}
          </div>
        </div>
      `;
            }).join('');

            threadView.innerHTML = `
      <div class="thread-view-head">
        <div class="thread-view-contact">
          ${avatarHtml(thread, 'thread-view-avatar')}
          <div class="thread-view-copy">
            <div class="thread-view-name-line">${linkedInProfileUrl ? `<a class="thread-view-name-link" href="${esc(linkedInProfileUrl)}" target="_blank" rel="noopener noreferrer" title="Open LinkedIn profile"><strong>${esc(thread.name || thread.contact_name || 'Unknown contact')}</strong></a>` : `<strong>${esc(thread.name || thread.contact_name || 'Unknown contact')}</strong>`}${threadChannelBadgesHtml(thread)}</div>
            <span>${esc(thread.company || thread.company_name || '')}${thread.campaign_id ? ' · Campaign ' + esc(thread.campaign_id) : ''} · ${esc(employee ? employee.full_name : '')}</span>
            <div class="thread-id-chip-row">
            <a href="${esc(linkedInDestination)}" target="_blank" rel="noopener noreferrer" class="thread-id-chip link button">LinkedIn</a>
              ${thread.linkedin_url ? `<a class="thread-id-chip link" href="${esc(thread.linkedin_url)}" target="_blank" rel="noopener">Open LinkedIn</a>` : ''}
            </div>
          </div>
        </div>
        <div class="thread-view-actions">
          ${favoriteButtonHtml(thread, 'header')}
          <div class="thread-state ${statusClass}">${stateLabel(thread)}</div>
        </div>
      </div>
      ${activeMailbox === 'outbox' && threadHasPendingApproval(thread) ? `<div class="outbox-thread-alert">${threadPendingCount} prepared message${threadPendingCount === 1 ? '' : 's'} waiting for approval in this thread. Review the highlighted message and send when ready.</div>` : ''}
      <div class="thread-messages" id="threadMessages"><div class="thread-messages-inner">${messagesHtml ? '<div class="message-history-spacer" aria-hidden="true"></div>' + messagesHtml : '<div class="card"><h4>No messages</h4><p>This thread does not contain messages yet.</p></div>'}</div></div>
      <div class="thread-compose">
        <div class="thread-compose-top">
          <span class="thread-compose-title">Reply to ${esc(thread.name || thread.contact_name || 'contact')}</span>
          <div class="composer-toolbar" data-editor-toolbar="replyEditor">
            <button class="composer-tool" type="button" data-command="bold"><strong>B</strong></button>
            <button class="composer-tool" type="button" data-emoji="😊">😊</button>
            <button class="composer-tool" type="button" data-emoji="😃">😃</button>
            <button class="composer-tool" type="button" data-emoji="👍">👍</button>
            <button class="composer-tool" type="button" data-emoji="🔥">🔥</button>
            <button class="composer-tool" type="button" data-emoji="🙏">🙏</button>
          </div>
        </div>
        <div class="rich-editor" id="replyEditor" contenteditable="true" data-placeholder="Write a reply..."></div>
        <div class="thread-compose-actions">
          <span class="composer-status" id="replyStatus">Use the B button for bold, click emojis, or press Ctrl/Cmd + Enter to send.</span>
          <button class="btn btn-primary" type="button" id="sendReplyBtn" disabled>Send reply</button>
        </div>
      </div>
    `;

            applyAvatarFallbacks(threadView);
            scrollMessagesToBottom();
            keepThreadAtBottomWhileMediaLoads();

            const replyEditor = document.getElementById('replyEditor');
            const sendReplyBtn = document.getElementById('sendReplyBtn');
            const replyStatus = document.getElementById('replyStatus');
            setupEditor(replyEditor);
            setupToolbar(threadView);
            bindFavoriteButtons(threadView);
            replyEditor?.addEventListener('input', function(){
                const payload = getEditorPayload(replyEditor);
                sendReplyBtn.disabled = !payload.text;
                if (replyStatus) {
                    replyStatus.textContent = 'Use the B button for bold, click emojis, or press Ctrl/Cmd + Enter to send.';
                    replyStatus.className = 'composer-status';
                }
            });
            document.getElementById('sendReplyBtn')?.addEventListener('click', sendReply);
            document.getElementById('copyThreadLinkBtn')?.addEventListener('click', copyThreadLink);
            threadView.querySelectorAll('[data-prepare-message-id]').forEach(btn => {
                btn.addEventListener('click', function(){
                    prepareMessage(Number(this.dataset.prepareMessageId));
                });
            });
            threadView.querySelectorAll('[data-edit-message-id]').forEach(btn => {
                btn.addEventListener('click', function(){
                    openEditReplyModal(Number(this.dataset.editMessageId));
                });
            });
        }

        function setLocalThreads(threads){
            const previousById = new Map(localThreads.map(thread => [Number(thread.id), thread]));
            localThreads = (threads || []).map((thread, index) => {
                const existing = previousById.get(Number(thread.id));
                const hasIncomingFavorite = hasThreadFavoriteField(thread) || !isNullishDate(thread?.favorite_at ?? thread?.favorited_at ?? thread?.hearted_at);
                const favoriteFallback = existing && !hasIncomingFavorite ? {
                    is_favorite: threadIsFavorite(existing) ? 1 : 0,
                    favorite: threadIsFavorite(existing) ? 1 : 0,
                    favorite_at: existing.favorite_at || existing.favorited_at || existing.hearted_at || null
                } : {};
                return { ...favoriteFallback, ...thread, __index: index };
            });
        }

        function mergeSelectedThreadIntoLocalList(selectedThread, forcedStatus = null){
            if (!selectedThread || selectedThread.id === undefined || selectedThread.id === null) return;

            const selectedId = Number(selectedThread.id);
            const index = localThreads.findIndex(item => Number(item.id) === selectedId);
            const existing = index >= 0 ? localThreads[index] : { __index: localThreads.length };
            const selectedMessages = Array.isArray(selectedThread.messages) ? selectedThread.messages : null;
            const existingMessages = Array.isArray(existing.messages) ? existing.messages : [];

            const merged = {
                ...existing,
                ...selectedThread,
                messages: selectedMessages || existingMessages,
                __index: existing.__index ?? localThreads.length
            };

            if (forcedStatus) {
                merged.status = forcedStatus;
                merged.thread_status = forcedStatus;
            }

            const resolvedDateRaw = threadDateRaw(merged);
            if (resolvedDateRaw && !isTimeOnlyValue(resolvedDateRaw)) {
                merged.__resolved_date_raw = resolvedDateRaw;
            }

            if (index >= 0) {
                localThreads[index] = merged;
            } else {
                localThreads.push(merged);
            }
        }

        async function loadEmployee(employeeId, preferredThreadId = null){
            keepOpenedUnreadThreadVisibleId = null;
            const requestedEmployeeId = String(employeeId || '').trim();
            activeEmployeeId = requestedEmployeeId || '';

            const inaccessibleEmployee = requestedEmployeeId ? getInaccessibleInbox(requestedEmployeeId) : null;
            if (inaccessibleEmployee) {
                clearAccessBanner();
                renderNoAccessInbox(inaccessibleEmployee);
                return false;
            }

            threadListMeta.textContent = 'Loading conversations...';
            threadListSummary.innerHTML = '';
            threadList.innerHTML = localThreads.length ? threadList.innerHTML : '<div class="thread-empty"><div class="card"><h4>Loading</h4><p>Loading the inbox for the selected user.</p></div></div>';
            try {
                const params = new URLSearchParams();
                if (activeEmployeeId) params.set('employee_id', activeEmployeeId);
                if (preferredThreadId) params.set('thread_id', String(preferredThreadId));
                const endpoint = endpointPath + (params.toString() ? '?' + params.toString() : '');
                const payload = await api.request(endpoint);

                syncAccessOverview(payload, payload.employee?.id || activeEmployeeId);
                activeEmployee = payload.employee || null;
                updateViewerIdentity(activeEmployee);
                updateConnectionStatuses(payload);
                if (activeEmployee?.id) {
                    activeEmployeeId = String(activeEmployee.id);
                    renderEmployeeSelect(activeEmployeeId);
                }
                setLocalThreads(payload.threads || []);
                const selectedThread = payload.selected_thread ? openedThreadForDisplay(payload.selected_thread) : null;
                if (selectedThread) {
                    mergeSelectedThreadIntoLocalList(selectedThread);
                }
                activeThreadId = selectedThread ? Number(selectedThread.id) : null;
                if (preserveAccessBannerOnNextLoad) {
                    preserveAccessBannerOnNextLoad = false;
                } else {
                    clearAccessBanner();
                }
                renderThreadList();
                renderThreadView(payload.employee || null, selectedThread);
                syncThreadUrl(activeEmployeeId, activeThreadId);
                return true;
            } catch (error) {
                const fallbackInaccessible = getInaccessibleInbox(activeEmployeeId);
                if (fallbackInaccessible) {
                    renderNoAccessInbox(fallbackInaccessible);
                    return false;
                }
                threadListMeta.textContent = 'Could not load conversations.';
                threadListSummary.innerHTML = '';
                threadList.innerHTML = '<div class="thread-empty"><div class="card"><h4>Could not load</h4><p>' + esc(error.message) + '</p></div></div>';
                threadView.innerHTML = '<div class="thread-messages"><div class="thread-messages-inner"><div class="card"><h4>Could not load the thread</h4><p>Try again.</p></div></div></div>';
                return false;
            }
        }

        async function loadThread(threadId){
            if (keepOpenedUnreadThreadVisibleId !== null && Number(keepOpenedUnreadThreadVisibleId) !== Number(threadId)) {
                keepOpenedUnreadThreadVisibleId = null;
            }

            try {
                const payload = await api.request(endpointPath + '?employee_id=' + encodeURIComponent(activeEmployeeId) + '&thread_id=' + encodeURIComponent(threadId) + '&mode=thread');
                syncAccessOverview(payload, activeEmployeeId);
                const selected = payload.selected_thread ? openedThreadForDisplay(payload.selected_thread) : null;
                if (selected) {
                    mergeSelectedThreadIntoLocalList(selected);
                }
                activeEmployee = payload.employee || activeEmployee;
                updateViewerIdentity(activeEmployee);
                renderThreadList();
                renderThreadView(payload.employee || activeEmployee, selected);
                syncThreadUrl(activeEmployeeId, selected ? selected.id : threadId);
                return selected;
            } catch (error) {
                alert(error.message);
                return null;
            }
        }

        async function reviewPendingThread(threadId){
            if (activeMailbox !== 'outbox') {
                activeMailbox = 'outbox';
                activeFilter = 'pending';
            }

            const selected = await selectThreadFromList(threadId);
            const thread = selected || activeThread;
            const message = firstPendingApprovalMessage(thread);

            if (message && messageIsEditable(message)) {
                openEditReplyModal(Number(message.id));
                return;
            }

            showToast('Open the highlighted pending message in the thread to review it.');
        }

        function setReplyStatus(text, type = ''){
            const node = document.getElementById('replyStatus');
            if (!node) return;
            node.textContent = text;
            node.className = 'composer-status' + (type ? ' ' + type : '');
        }

        async function sendReply(){
            const editor = document.getElementById('replyEditor');
            const sendBtn = document.getElementById('sendReplyBtn');
            const payloadText = getEditorPayload(editor);
            if (!payloadText.text || !activeThreadId) return;
            try {
                if (sendBtn) sendBtn.disabled = true;
                setReplyStatus('Sending reply...');
                const payload = await api.request(endpointPath, {
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'reply',
                        employee_id: Number(activeEmployeeId),
                        user_id: Number(activeEmployeeId),
                        thread_id: Number(activeThreadId),
                        text: payloadText.text,
                        html: payloadText.html
                    })
                });
                syncAccessOverview(payload, activeEmployeeId);
                setLocalThreads(Array.isArray(payload.threads) && payload.threads.length ? payload.threads : localThreads);
                if (payload.selected_thread) {
                    mergeSelectedThreadIntoLocalList(payload.selected_thread, 'read');
                }
                activeEmployee = payload.employee || activeEmployee;
                updateViewerIdentity(activeEmployee);
                renderThreadList();
                renderThreadView(payload.employee || activeEmployee, payload.selected_thread || activeThread);
                syncThreadUrl(activeEmployeeId, activeThreadId);
            } catch (error) {
                if (sendBtn) sendBtn.disabled = false;
                setReplyStatus(error.message || 'Could not send reply.', 'error');
            }
        }

        async function saveEditedReply(){
            const payloadText = getEditorPayload(editReplyEditor);
            if (!pendingEditMessageId || !payloadText.text) return;
            try {
                saveEditReplyBtn.disabled = true;
                saveEditReplyBtn.textContent = 'Sending...';
                const payload = await api.request(endpointPath, {
                    method: 'PATCH',
                    body: JSON.stringify({
                        action: 'edit_reply',
                        employee_id: Number(activeEmployeeId),
                        user_id: Number(activeEmployeeId),
                        thread_id: Number(activeThreadId),
                        message_id: Number(pendingEditMessageId),
                        text: payloadText.text,
                        html: payloadText.html
                    })
                });
                syncAccessOverview(payload, activeEmployeeId);
                setLocalThreads(Array.isArray(payload.threads) && payload.threads.length ? payload.threads : localThreads);
                if (payload.selected_thread) {
                    mergeSelectedThreadIntoLocalList(payload.selected_thread, 'read');
                }
                activeEmployee = payload.employee || activeEmployee;
                updateViewerIdentity(activeEmployee);
                renderThreadList();
                renderThreadView(payload.employee || activeEmployee, payload.selected_thread || activeThread);
                closeEditReplyModal();
            } catch (error) {
                alert(error.message);
            } finally {
                saveEditReplyBtn.disabled = false;
                saveEditReplyBtn.textContent = 'Approve and send';
            }
        }

        async function deleteEditedReply(){
            const message = findActiveMessage(pendingEditMessageId);
            if (!pendingEditMessageId || !activeThreadId || !message) return;

            const stopWorkflow = messageUsesHelper(message);
            const label = stopWorkflow ? 'Delete message and stop Workflow' : 'Delete message';
            if (!window.confirm(label + '?')) return;

            try {
                deleteEditReplyBtn.disabled = true;
                deleteEditReplyBtn.textContent = 'Deleting...';
                saveEditReplyBtn.disabled = true;
                const payload = await api.request(endpointPath, {
                    method: 'PATCH',
                    body: JSON.stringify({
                        action: 'delete_message',
                        employee_id: Number(activeEmployeeId),
                        user_id: Number(activeEmployeeId),
                        thread_id: Number(activeThreadId),
                        message_id: Number(pendingEditMessageId),
                        helper_id: stopWorkflow ? messageHelperId(message) : undefined,
                        stop_workflow: stopWorkflow
                    })
                });
                syncAccessOverview(payload, activeEmployeeId);
                setLocalThreads(Array.isArray(payload.threads) ? payload.threads : localThreads);
                if (payload.selected_thread) mergeSelectedThreadIntoLocalList(payload.selected_thread);
                activeEmployee = payload.employee || activeEmployee;
                updateViewerIdentity(activeEmployee);
                renderThreadList();
                renderThreadView(payload.employee || activeEmployee, payload.selected_thread || activeThread);
                closeEditReplyModal();
                showToast(stopWorkflow ? 'Message deleted and workflow stopped.' : 'Message deleted.');
            } catch (error) {
                alert(error.message || 'Could not delete the message.');
            } finally {
                deleteEditReplyBtn.disabled = false;
                saveEditReplyBtn.disabled = false;
                deleteEditReplyBtn.textContent = label;
            }
        }

        async function prepareMessage(messageId){
            try {
                const payload = await api.request(endpointPath, {
                    method: 'PATCH',
                    body: JSON.stringify({ action: 'prepare_message', employee_id: Number(activeEmployeeId), thread_id: Number(activeThreadId), message_id: Number(messageId) })
                });
                syncAccessOverview(payload, activeEmployeeId);
                setLocalThreads(Array.isArray(payload.threads) && payload.threads.length ? payload.threads : localThreads);
                if (payload.selected_thread) {
                    mergeSelectedThreadIntoLocalList(payload.selected_thread);
                }
                renderThreadList();
                renderThreadView(payload.employee || activeEmployee, payload.selected_thread || activeThread);
            } catch (error) {
                alert(error.message);
            }
        }


        threadContextMenu?.addEventListener('click', function(event){
            const btn = event.target.closest('[data-context-action]');
            if (!btn || !contextThreadId) return;
            const action = btn.dataset.contextAction;
            const threadId = contextThreadId;
            hideThreadContextMenu();

            if (action === 'score') {
                const thread = localThreads.find(item => Number(item.id) === threadId) || activeThread;
                openScoreReasonModal(thread);
                return;
            }

            if (action === 'mark-unread') {
                markThreadAsUnread(threadId);
                return;
            }

            if (action === 'favorite') {
                toggleThreadFavorite(threadId);
                return;
            }

            if (action === 'share') {
                shareThreadWithColleague(threadId);
            }
        });

        document.addEventListener('click', function(event){
            const accessBtn = event.target.closest('[data-request-inbox-access]');
            if (accessBtn) {
                event.preventDefault();
                requestInboxAccess(accessBtn.dataset.requestInboxAccess, accessBtn);
                return;
            }

            if (threadContextMenu && !threadContextMenu.contains(event.target)) {
                hideThreadContextMenu();
            }
        });

        document.addEventListener('contextmenu', function(event){
            if (!event.target.closest('.thread-item') && !event.target.closest('#threadContextMenu')) {
                hideThreadContextMenu();
            }
        });

        window.addEventListener('resize', hideThreadContextMenu);
        window.addEventListener('scroll', hideThreadContextMenu, true);

        function setActiveMailbox(mailbox){
            const next = mailbox === 'outbox' ? 'outbox' : 'inbox';
            if (activeMailbox === next) return;
            activeMailbox = next;
            activeFilter = defaultFilterForMailbox(activeMailbox);
            applyAutomaticFilterPriority = false;
            selectedThreadOverridesFilter = false;
            renderThreadList();
            if (activeThread) renderThreadView(activeEmployee, activeThread);
            syncThreadUrl(activeEmployeeId, activeThreadId);
        }

        mailboxSwitcher?.addEventListener('click', function(event){
            const btn = event.target.closest('[data-mailbox]');
            if (!btn) return;
            setActiveMailbox(btn.dataset.mailbox);
        });

        threadFilterRow.addEventListener('click', function(event){
            const btn = event.target.closest('[data-filter]');
            if (!btn) return;
            activeFilter = btn.dataset.filter;
            applyAutomaticFilterPriority = false;
            selectedThreadOverridesFilter = false;
            renderThreadList();
            syncThreadUrl(activeEmployeeId, activeThreadId);
        });

        employeeSelect.addEventListener('change', function(){
            activeFilter = defaultFilterForMailbox(activeMailbox);
            loadEmployee(this.value);
        });

        messageSearchScopes?.addEventListener('click', function(event){
            const button = event.target.closest('[data-search-scope]');
            if (!button) return;
            activeSearchScope = button.dataset.searchScope;
            messageSearchScopes.querySelectorAll('[data-search-scope]').forEach(scopeButton => {
                const active = scopeButton === button;
                scopeButton.classList.toggle('active', active);
                scopeButton.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            messageSearchInput.focus();
        });

        messageSearchForm?.addEventListener('submit', function(event){
            event.preventDefault();
            searchMessageThreads();
        });

        messageSearchResults?.addEventListener('click', async function(event){
            const result = event.target.closest('[data-search-thread-id]');
            if (!result) return;
            const threadId = Number(result.dataset.searchThreadId || 0);
            if (!threadId) return;
            closeMessageSearchModal();
            await selectThreadFromList(threadId);
            window.requestAnimationFrame(() => {
                threadList.querySelector('[data-thread-id=' + threadId + ']')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            });
        });

        closeMessageSearchModalBtn?.addEventListener('click', closeMessageSearchModal);
        closeScoreReasonModalBtn?.addEventListener('click', closeScoreReasonModal);
        scoreReasonModal?.addEventListener('click', function(event){
            if (event.target === scoreReasonModal) closeScoreReasonModal();
        });

        messageSearchModal?.addEventListener('click', function(event){
            if (event.target === messageSearchModal) closeMessageSearchModal();
        });
        closeEditReplyModalBtn?.addEventListener('click', closeEditReplyModal);
        cancelEditReplyBtn?.addEventListener('click', closeEditReplyModal);
        deleteEditReplyBtn?.addEventListener('click', deleteEditedReply);
        saveEditReplyBtn?.addEventListener('click', saveEditedReply);
        editReplyModal?.addEventListener('click', function(event){
            if (event.target === editReplyModal) closeEditReplyModal();
        });
        document.addEventListener('keydown', function(event){
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'f') {
                event.preventDefault();
                openMessageSearchModal();
                return;
            }
            if (event.key === 'Escape' && messageSearchModal.classList.contains('show')) {
                closeMessageSearchModal();
                return;
            }
            if (event.key === 'Escape' && editReplyModal.classList.contains('show')) closeEditReplyModal();
        });
        window.addEventListener('hashchange', function(){
            if (isChangingHashFromCode) return;
            const state = parseUrlState();
            selectedThreadOverridesFilter = Boolean(state.threadId);
            applyUrlViewState(state);
            renderThreadList();
            if (activeThread) renderThreadView(activeEmployee, activeThread);
            if (state.employeeId && state.employeeId !== activeEmployeeId && optionHasValue(state.employeeId)) {
                employeeSelect.value = state.employeeId;
                loadEmployee(state.employeeId, state.threadId || null);
                return;
            }
            if (state.threadId && Number(state.threadId) !== Number(activeThreadId)) {
                loadThread(Number(state.threadId));
            }
        });

        setupToolbar(document);
        setupEditor(editReplyEditor);

        const initialUrlState = parseUrlState();
        selectedThreadOverridesFilter = Boolean(initialUrlState.threadId);
        applyUrlViewState(initialUrlState);
        (async function initMessagesPage(){
            if (initialUrlState.requestToken || initialUrlState.acceptId) {
                await acceptInboxAccessRequest(initialUrlState.requestToken, initialUrlState.acceptId);
            }

            await loadEmployee('', initialUrlState.employeeId ? null : (initialUrlState.threadId || null));

            if (initialUrlState.employeeId && optionHasValue(initialUrlState.employeeId)) {
                employeeSelect.value = initialUrlState.employeeId;
                await loadEmployee(initialUrlState.employeeId, initialUrlState.threadId || null);
            }
            applyAutomaticFilterPriority = false;
        })();
    })();
</script>
<?php include('../partial/footer.php'); include('../partial/scripts.php'); include('../partial/footer-end.php'); ?>
