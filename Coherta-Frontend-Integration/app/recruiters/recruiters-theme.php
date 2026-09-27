<style>
    :root {
        --rec-ink: #08133a;
        --rec-muted: #4e5b7e;
        --rec-soft: #7581a4;
        --rec-line: #dfe7f4;
        --rec-line-soft: #edf2fa;
        --rec-blue: #1f5eff;
        --rec-blue-dark: #1551f2;
        --rec-blue-soft: #eff4ff;
        --rec-purple-soft: #f4f1ff;
        --rec-green: #15985a;
        --rec-green-soft: #eaf8ef;
        --rec-bg: #fbfdff;
        --rec-shadow: 0 10px 30px rgba(17, 43, 91, .055);
        --rec-shadow-soft: 0 4px 20px rgba(17, 43, 91, .035);
    }

    body:has(.recruiters-page-body) {
        background: linear-gradient(180deg, #fff 0%, var(--rec-bg) 62%, #f8fbff 100%);
        color: var(--rec-ink);
    }

    .recruiters-page-body {
        background: var(--rec-bg);
        color: var(--rec-ink);
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
    }

    .recruiters-app-shell {
        max-width: 1680px;
        padding: 34px 32px 52px;
    }

    .recruiters-content {
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    .recruiters-content .panel,
    .recruiters-content .summary-card {
        border: 1px solid var(--rec-line);
        border-radius: 13px;
        background: #fff;
        box-shadow: var(--rec-shadow-soft);
        overflow: hidden;
    }

    .recruiters-content .panel-inner,
    .recruiters-content .summary-card {
        padding: 24px;
    }

    .recruiters-hero-panel {
        background: linear-gradient(180deg, #fff 0%, #f8fbff 100%);
    }

    .recruiters-content .hero-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(360px, .8fr);
        gap: 26px;
        align-items: stretch;
    }

    .recruiters-content .create-hero-grid {
        grid-template-columns: 1fr;
    }

    .recruiters-content h2 {
        margin: 0;
        color: var(--rec-ink);
        font-size: clamp(34px, 3vw, 43px);
        line-height: 1.05;
        font-weight: 840;
        letter-spacing: 0;
    }

    .recruiters-content h3 {
        margin: 0;
        color: var(--rec-ink);
        font-size: 24px;
        line-height: 1.2;
        font-weight: 840;
        letter-spacing: 0;
    }

    .recruiters-content p {
        color: var(--rec-muted);
        font-size: 16px;
        line-height: 1.55;
        font-weight: 520;
    }

    .hero-actions,
    .campaign-card-actions,
    .candidate-actions,
    .table-actions,
    .stage-actions,
    .form-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .recruiters-content .btn {
        min-height: 43px;
        border-radius: 8px;
        border: 1px solid #dbe5f3;
        padding: 0 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        font-weight: 800;
        line-height: 1;
        text-decoration: none;
        box-shadow: 0 8px 16px rgba(17, 43, 91, .07);
    }

    .recruiters-content .btn-success {
        border-color: transparent;
        background: linear-gradient(180deg, #245fff, #104fe9);
        color: #fff;
        box-shadow: 0 8px 16px rgba(31, 94, 255, .22);
    }

    .recruiters-content .btn-light {
        background: linear-gradient(180deg, #fff, #f8fafc);
        color: var(--rec-ink);
    }

    .recruiters-content .btn-sm {
        min-height: 38px;
        padding: 0 14px;
    }

    .recruiters-content .btn-xs {
        min-height: 34px;
        padding: 0 11px;
        font-size: 12px;
        box-shadow: none;
    }

    .candidate-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-items: stretch;
    }

    .candidate-actions .btn,
    .candidate-actions .recruiters-link {
        width: 100%;
        min-width: 0;
        flex: none;
        white-space: normal;
        overflow-wrap: anywhere;
        text-align: center;
        line-height: 1.15;
    }

    .candidate-actions .js-deactivate-candidate {
        grid-column: 1 / -1;
    }

    .hero-stats,
    .metric-stack {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .hero-stats:has(.hero-stat:nth-child(3):last-child) {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .hero-stat,
    .metric-box,
    .stage-mini,
    .campaign-reply-view {
        border: 1px solid var(--rec-line);
        border-radius: 11px;
        background: #fff;
        padding: 17px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.9);
    }

    .hero-stat strong,
    .metric-box strong,
    .stage-mini strong,
    .campaign-reply-view strong {
        display: block;
        color: var(--rec-ink);
        font-size: 25px;
        line-height: 1;
        font-weight: 850;
        letter-spacing: 0;
    }

    .hero-stat span,
    .metric-box span,
    .stage-mini span,
    .campaign-reply-view span {
        display: block;
        margin-top: 8px;
        color: var(--rec-soft);
        font-size: 14px;
        font-weight: 700;
    }

    .section-head {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .count-pill,
    .meta-pill,
    .pill {
        min-height: 31px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid var(--rec-line);
        background: #f8fafc;
        color: #526080;
        padding: 0 12px;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    .pill,
    .is-primary-mini .pill {
        background: var(--rec-green-soft);
        border-color: #d8efdf;
        color: var(--rec-green);
    }

    .campaign-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
        gap: 16px;
    }

    .campaign-grid-thirds {
        grid-template-columns: repeat(auto-fill, minmax(320px, calc(33.333% - 11px)));
    }

    .campaign-card,
    .candidate-card,
    .create-step-card {
        border: 1px solid var(--rec-line);
        border-radius: 13px;
        background: #fff;
        box-shadow: var(--rec-shadow-soft);
    }

    .campaign-card {
        padding: 18px;
    }

    .campaign-card-main {
        display: block;
        color: inherit;
        text-decoration: none;
    }

    .campaign-card-top,
    .candidate-card-top,
    .stage-card-head,
    .create-step-head {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
    }

    .candidate-card-top {
        display: grid;
        grid-template-columns: 52px minmax(0, 1fr) auto;
        align-items: center;
        gap: 10px;
    }

    .campaign-card-top strong,
    .candidate-card-top strong,
    .stage-card-head strong,
    .create-step-head strong {
        color: var(--rec-ink);
        font-size: 17px;
        font-weight: 850;
    }

    .campaign-card-top span,
    .candidate-card-top span,
    .stage-started,
    .stage-description,
    .candidate-meta-line {
        color: var(--rec-muted);
        font-size: 14px;
        line-height: 1.45;
    }

    .recruiters-progress {
        height: 9px;
        border-radius: 999px;
        background: #edf2fa;
        overflow: hidden;
        margin: 18px 0;
    }

    .recruiters-progress span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--rec-blue), #6547ed);
    }

    .stage-mini-grid-horizontal,
    .campaign-stage-grid,
    .stage-overview {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .stage-mini.is-primary,
    .stage-mini.is-primary-mini,
    .create-step-card.is-active {
        border-color: #90adff;
        background: linear-gradient(180deg, #fff, var(--rec-blue-soft));
    }

    .campaign-reply-view {
        margin-top: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .campaign-reply-view em,
    .match-badge {
        min-width: 48px;
        height: 40px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        background: var(--rec-purple-soft);
        color: #6547ed;
        font-weight: 850;
        font-style: normal;
    }

    .recruiters-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    .stage-filter,
    .view-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 18px;
    }

    .candidate-toolbar {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .candidate-toolbar .stage-filter,
    .candidate-toolbar .view-tabs {
        margin-bottom: 0;
    }

    .stage-filter a,
    .view-tabs a {
        height: 42px;
        border-radius: 9px;
        border: 1px solid var(--rec-line);
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 16px;
        color: var(--rec-muted);
        font-weight: 800;
        text-decoration: none;
    }

    .stage-filter a.is-active,
    .view-tabs a.is-active {
        color: var(--rec-blue);
        border-color: #90adff;
        background: var(--rec-blue-soft);
    }

    .recruiter-flow {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .flow-step {
        position: relative;
        border: 1px solid var(--rec-line);
        border-radius: 13px;
        background: #fff;
        padding: 18px;
    }

    .flow-step.is-current {
        border-color: #90adff;
        background: var(--rec-blue-soft);
    }

    .flow-number {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        background: #f1f5f9;
        color: var(--rec-muted);
        font-weight: 850;
        margin-bottom: 12px;
    }

    .flow-step.is-current .flow-number {
        background: var(--rec-blue);
        color: #fff;
    }

    .flow-step strong {
        display: block;
        color: var(--rec-ink);
        font-weight: 850;
    }

    .flow-step small {
        display: block;
        margin-top: 7px;
        color: var(--rec-muted);
        font-weight: 650;
    }

    .flow-arrow {
        display: none;
    }

    .candidate-card-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
    }

    .candidate-card {
        padding: 18px;
        min-width: 0;
        overflow: hidden;
    }

    .candidate-avatar {
        width: 52px;
        height: 52px;
        border-radius: 999px;
        object-fit: cover;
        border: 1px solid var(--rec-line);
        background: var(--rec-blue-soft);
        flex: 0 0 auto;
    }

    .candidate-heading {
        min-width: 0;
        flex: 1;
    }

    .candidate-name-line {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .candidate-avatar-sm {
        width: 38px;
        height: 38px;
    }

    .candidate-table-person {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 210px;
    }

    .candidate-card p {
        margin: 14px 0;
        overflow-wrap: anywhere;
    }

    .candidate-loading-card {
        pointer-events: none;
    }

    .candidate-loading-card,
    .candidate-loading-card .pill,
    .candidate-loading-card .match-badge {
        color: #8b98b8;
    }

    .candidate-avatar-placeholder,
    .candidate-loading-lines span {
        background: linear-gradient(90deg, #eef3fb 0%, #f8fbff 48%, #eef3fb 100%);
        background-size: 180% 100%;
        animation: recruiterLoading 1s ease-in-out infinite;
    }

    .candidate-loading-lines {
        display: grid;
        gap: 10px;
        margin-top: 16px;
    }

    .candidate-loading-lines span {
        display: block;
        height: 12px;
        border-radius: 999px;
    }

    .candidate-loading-lines span:nth-child(2) {
        width: 74%;
    }

    .candidate-loading-lines span:nth-child(3) {
        width: 52%;
    }

    .btn.is-disabled {
        opacity: .45;
        pointer-events: none;
        cursor: not-allowed;
    }

    @keyframes recruiterLoading {
        0% { background-position: 100% 0; }
        100% { background-position: -100% 0; }
    }

    .stage-card {
        display: grid;
        grid-template-rows: auto minmax(58px, auto) 34px auto 1fr;
        min-height: 280px;
    }

    .stage-card .stage-actions {
        align-self: end;
        padding-top: 14px;
    }

    .settings-step-controls {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .settings-step-controls .stage-started {
        margin: 0;
        white-space: nowrap;
    }

    .stage-filter-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: start;
    }

    .stage-filter-summary span,
    .stage-filter-summary strong {
        min-height: 31px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-top: 0;
        padding: 0 9px;
        border: 1px solid var(--rec-line);
        border-radius: 999px;
        background: #f8fbff;
        color: var(--rec-muted);
        font-size: 14px;
        line-height: 1;
        font-weight: 750;
        white-space: nowrap;
    }

    .table-shell {
        border: 1px solid var(--rec-line);
        border-radius: 13px;
        overflow: auto;
        background: #fff;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 15px 16px;
        border-bottom: 1px solid #e6edf7;
        text-align: left;
        vertical-align: middle;
    }

    .data-table th {
        color: #4c5b7d;
        font-size: 13px;
        font-weight: 850;
        background: #fbfdff;
    }

    .data-table td {
        color: #182955;
        font-size: 14px;
    }

    .linkedin-banner {
        min-height: 32px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 12px;
        background: #0a66c2;
        color: #fff;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
        white-space: nowrap;
    }

    .table-actions .btn-xs {
        min-width: 138px;
    }

    .create-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 22px;
        align-items: start;
    }

    .create-preview-card {
        margin-bottom: 22px;
        position: static;
        top: auto;
    }

    .settings-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .field-wide {
        grid-column: 1 / -1;
    }

    .field-note {
        margin: 0;
        color: var(--rec-muted);
        font-size: 14px;
        line-height: 1.55;
    }

    .field label,
    .skill-criteria-field label {
        display: block;
        margin-bottom: 8px;
        color: #55637f;
        font-size: 13px;
        font-weight: 800;
    }

    .input,
    .select,
    .textarea {
        width: 100%;
        border: 1px solid #dce5f2;
        border-radius: 9px;
        background: #fff;
        color: #172a57;
        font-size: 15px;
        font-weight: 650;
        outline: 0;
    }

    .input,
    .select {
        min-height: 48px;
        padding: 0 14px;
    }

    .textarea {
        min-height: 110px;
        padding: 14px;
        resize: vertical;
    }

    .input:focus,
    .select:focus,
    .textarea:focus {
        border-color: var(--rec-blue);
        box-shadow: 0 0 0 4px #dbeafe;
    }

    .template-field {
        position: relative;
    }

    .template-autocomplete {
        position: absolute;
        left: 0;
        right: 0;
        top: 100%;
        z-index: 50;
    }

    .template-autocomplete-list {
        margin-top: 6px;
        max-height: 240px;
        overflow: auto;
        border: 1px solid var(--rec-line);
        border-radius: 13px;
        background: #fff;
        box-shadow: var(--rec-shadow-soft);
    }

    .template-autocomplete-item {
        padding: 10px 12px;
        border-bottom: 1px solid #eef2f7;
        cursor: pointer;
    }

    .template-autocomplete-item:last-child {
        border-bottom: none;
    }

    .template-autocomplete-item.is-active,
    .template-autocomplete-item:hover {
        background: #f4f8ff;
    }

    .template-autocomplete-main {
        color: var(--rec-ink);
        font-weight: 800;
    }

    .template-autocomplete-meta {
        margin-top: 2px;
        color: var(--rec-muted);
        font-size: 12px;
    }

    .create-step-card {
        padding: 20px;
        margin-top: 18px;
    }

    .create-step-head {
        margin-bottom: 16px;
    }

    .create-step-head > span {
        min-width: 92px;
        height: 36px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--rec-blue-soft);
        color: var(--rec-blue);
        font-size: 13px;
        font-weight: 850;
    }

    .create-step-head small {
        display: block;
        margin-top: 5px;
        color: var(--rec-muted);
        font-weight: 600;
    }

    .skill-criteria-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .skill-criteria-row {
        display: grid;
        grid-template-columns: minmax(180px, 1fr) 150px 150px 100px auto;
        gap: 10px;
        align-items: end;
    }

    .skill-value-field span {
        min-height: 48px;
        border: 1px solid var(--rec-line);
        border-radius: 9px;
        display: flex;
        align-items: center;
        padding: 0 12px;
        color: var(--rec-blue);
        font-weight: 850;
        background: var(--rec-blue-soft);
    }

    .recruiter-modal {
        background: rgba(8, 19, 58, .42);
        backdrop-filter: blur(3px);
    }

    .recruiter-modal-card {
        border: 1px solid var(--rec-line);
        border-radius: 16px;
        box-shadow: 0 24px 70px rgba(8, 19, 58, .18);
    }

    @media (max-width: 1440px) {
        .candidate-card-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 1180px) {
        .recruiters-content .hero-grid,
        .create-layout {
            grid-template-columns: 1fr;
        }

        .stage-mini-grid-horizontal,
        .campaign-stage-grid,
        .stage-overview,
        .recruiter-flow {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 980px) {
        .candidate-card-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 760px) {
        .recruiters-app-shell {
            padding: 20px 14px 36px;
        }

        .recruiters-content .panel-inner,
        .recruiters-content .summary-card {
            padding: 18px;
        }

        .hero-stats,
        .metric-stack,
        .settings-grid,
        .stage-mini-grid-horizontal,
        .campaign-stage-grid,
        .stage-overview,
        .recruiter-flow,
        .candidate-card-grid {
            grid-template-columns: 1fr;
        }

        .section-head {
            flex-direction: column;
        }

        .candidate-toolbar {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .candidate-toolbar .stage-filter,
        .candidate-toolbar .view-tabs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            width: 100%;
        }

        .candidate-toolbar .view-tabs {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .stage-filter a,
        .view-tabs a {
            width: 100%;
            min-width: 0;
            padding: 0 10px;
            font-size: 13px;
            white-space: normal;
            text-align: center;
            line-height: 1.15;
        }

        .candidate-card {
            padding: 16px;
        }

        .candidate-card-top {
            grid-template-columns: 46px minmax(0, 1fr) auto;
        }

        .candidate-avatar {
            width: 46px;
            height: 46px;
        }

        .candidate-card-top strong,
        .candidate-card-top span,
        .candidate-meta-line,
        .candidate-card p {
            overflow-wrap: anywhere;
        }

        .candidate-name-line {
            gap: 6px;
        }

        .candidate-name-line .pill {
            min-height: 26px;
            padding: 0 8px;
            font-size: 12px;
        }

        .match-badge {
            min-width: 44px;
            height: 36px;
            font-size: 13px;
        }

        .candidate-actions .btn,
        .candidate-actions .recruiters-link {
            padding: 0 8px;
        }

        .skill-criteria-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 430px) {
        .candidate-toolbar .stage-filter {
            grid-template-columns: 1fr;
        }

        .candidate-toolbar .view-tabs {
            grid-template-columns: 1fr;
        }

        .candidate-card-top {
            grid-template-columns: 44px minmax(0, 1fr);
        }

        .candidate-card-top .match-badge {
            grid-column: 1 / -1;
            width: 100%;
            justify-self: stretch;
        }

        .candidate-actions {
            grid-template-columns: 1fr;
        }
    }
</style>
