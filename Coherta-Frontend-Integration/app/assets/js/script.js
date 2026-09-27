(function ($) {
    "use strict";
    $(document).on('click', function (e) {
        var outside_space = $(".outside");
        if (!outside_space.is(e.target) &&
            outside_space.has(e.target).length === 0) {
            $(".menu-to-be-close").removeClass("d-block");
            $('.menu-to-be-close').css('display', 'none');
        }
    })

    $('.prooduct-details-box .close').on('click', function (e) {
        var tets = $(this).parent().parent().parent().parent().addClass('d-none');
    })



    if ($('.page-wrapper').hasClass('horizontal-wrapper')){

        $(".sidebar-list").hover(
            function () {
              $(this).addClass("hoverd");
            },
            function () {
              $(this).removeClass("hoverd");
            }
        );
        $(window).on('scroll', function () {
            if ($(this).scrollTop() < 600) {
                $(".sidebar-list").removeClass("hoverd");
            }         
        });   
      }

    /*----------------------------------------
     passward show hide
     ----------------------------------------*/
    $('.show-hide').show();
    $('.show-hide span').addClass('show');

    $('.show-hide span').click(function () {
        if ($(this).hasClass('show')) {
            $('input[name="login[password]"]').attr('type', 'text');
            $(this).removeClass('show');
        } else {
            $('input[name="login[password]"]').attr('type', 'password');
            $(this).addClass('show');
        }
    });
    $('form button[type="submit"]').on('click', function () {
        $('.show-hide span').addClass('show');
        $('.show-hide').parent().find('input[name="login[password]"]').attr('type', 'password');
    });

    /*=====================
      02. Background Image js
      ==========================*/
    $(".bg-center").parent().addClass('b-center');
    $(".bg-img-cover").parent().addClass('bg-size');
    $('.bg-img-cover').each(function () {
        var el = $(this),
            src = el.attr('src'),
            parent = el.parent();
        parent.css({
            'background-image': 'url(' + src + ')',
            'background-size': 'cover',
            'background-position': 'center',
            'display': 'block'
        });
        el.hide();
    });

    $(".mega-menu-container").css("display", "none");
    $(".header-search").click(function () {
        $(".search-full").addClass("open");
        $("#searchInput").focus();
    });
    $(".close-search").click(function () {
        $(".search-full").removeClass("open");
        $("body").removeClass("offcanvas");
    });
    $(".mobile-toggle").click(function () {
        $(".nav-menus").toggleClass("open");
    });
    $(".mobile-toggle-left").click(function () {
        $(".left-header").toggleClass("open");
    });
    $(".bookmark-search").click(function () {
        $(".form-control-search").toggleClass("open");
    })
    $(".filter-toggle").click(function () {
        $(".product-sidebar").toggleClass("open");
    });
    $(".toggle-data").click(function () {
        $(".product-wrapper").toggleClass("sidebaron");
    });
    $(".form-control-search input").keyup(function (e) {
        if (e.target.value) {
            $(".page-wrapper").addClass("offcanvas-bookmark");
        } else {
            $(".page-wrapper").removeClass("offcanvas-bookmark");
        }
    });
    $(".search-full input").keyup(function (e) {
        if (e.target.value) {
            $("body").addClass("offcanvas");
        } else {
            $("body").removeClass("offcanvas");
        }
    });

    $('body').keydown(function (e) {
        if (e.keyCode == 27) {
            $('.search-full input').val('');
            $('.form-control-search input').val('');
            $('.page-wrapper').removeClass('offcanvas-bookmark');
            $('.search-full').removeClass('open');
            $('.search-form .form-control-search').removeClass('open');
            $("body").removeClass("offcanvas");
        }
    });
   $(".mode").on("click", function () {
        const bodyModeDark = $("body").hasClass("dark-only") 

        if(!bodyModeDark){
            $(".mode").addClass("active")
            localStorage.setItem("mode","dark-only")
            $("body").addClass("dark-only")
            $("body").removeClass("light")
        }
        if(bodyModeDark){

            $(".mode").removeClass("active")
            localStorage.setItem("mode","light")
            $("body").removeClass("dark-only")
            $("body").addClass("light")
        }

    })
    $("body").addClass(localStorage.getItem("mode") ? localStorage.getItem("mode") : "light")
    $(".mode").addClass(localStorage.getItem("mode") === "dark-only" ? "active" : " ")

    // sidebar filter
    $('.md-sidebar .md-sidebar-toggle ').on('click', function(e) {
        $(".md-sidebar .md-sidebar-aside ").toggleClass("open");   
    });

})(jQuery);

$('.loader-wrapper').fadeOut('slow', function () {
    $(this).remove();
});

$(window).on('scroll', function () {
    if ($(this).scrollTop() > 600) {
        $('.tap-top').fadeIn();
    } else {
        $('.tap-top').fadeOut();
    }
});



$('.tap-top').click(function () {
    $("html, body").animate({
        scrollTop: 0
    }, 600);
    return false;
});
(function ($, window, document, undefined) {
    "use strict";
    var $ripple = $(".js-ripple");
    $ripple.on("click.ui.ripple", function (e) {
        var $this = $(this);
        var $offset = $this.parent().offset();
        var $circle = $this.find(".c-ripple__circle");
        var x = e.pageX - $offset.left;
        var y = e.pageY - $offset.top;
        $circle.css({
            top: y + "px",
            left: x + "px"
        });
        $this.addClass("is-active");
    });
    $ripple.on(
        "animationend webkitAnimationEnd oanimationend MSAnimationEnd",
        function (e) {
            $(this).removeClass("is-active");
        });


})(jQuery, window, document);


// active link

$(".chat-menu-icons .toogle-bar").click(function () {
    $(".chat-menu").toggleClass("show");
});

$(".mobile-title svg").click(function () {
    $(".header-mega").toggleClass("d-block");
});

$(".onhover-dropdown").on("click", function () {
    $(this).children('.onhover-show-div').toggleClass("active");
});

// if ($(window).width() <= 991) {
//     $(".left-header .link-section").children('ul').css('display', 'none');
//     $(this).parent().children('ul').toggleClass("d-block").slideToggle();
// }


// if ($(window).width() < 991) {
//     $('<div class="bg-overlay"></div>').appendTo($('body'));
//     $(".bg-overlay").on("click", function () {
//         $(".page-header").addClass("close_icon");
//         $(".sidebar-wrapper").addClass("close_icon");
//         $(this).removeClass("active");
//     });

//     $(".toggle-sidebar").on("click", function () {
//         $(".bg-overlay").addClass("active");
//     });
//     $(".back-btn").on("click", function () {
//         $(".bg-overlay").removeClass("active");
//     });
// }
$("#flip-btn").click(function(){
    $(".flip-card-inner").addClass("flipped")
});

$("#flip-back").click(function(){
    $(".flip-card-inner").removeClass("flipped")
});

function getIdFromUrl() {
    const raw = new URLSearchParams(window.location.search).get('id');
    // send number if numeric, else the raw string
    return raw && /^\d+$/.test(raw) ? Number(raw) : raw;
}

function postStage(stageId) {
    const bearerToken = document.body.getAttribute('session_id');
    //        headers: { "Content-Type": "application/json", "Authorization": "Bearer " + bearerToken },
    return fetch(`https://api.coherta.com/pipeline/leads/stage/${encodeURIComponent(stageId)}`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${document.body.getAttribute('session_id')}`,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ pipeline_company_id: getIdFromUrl() })
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // capture:true lets us see the click even if something stops propagation later
    document.addEventListener('click', async (e) => {
        const link = e.target.closest('#top-tab a.nav-link[data-stage-id]');
        if (!link) return;
        await postStage(link.dataset.stageId);
    }, true);
});

document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', async (e) => {
        const el = e.target.closest('#deal-won-btn, #deal-lost-btn, .deal-close');
        if (!el) return;
        e.preventDefault();
        await handleDealClose(el);
    });

    // Optional: keyboard shortcuts (W = won, L = lost)
    document.addEventListener('keydown', (e) => {
        if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;
        if (document.getElementById('deal-won-btn')) {
            if (e.key.toLowerCase() === 'w') document.getElementById('deal-won-btn')?.click();
            if (e.key.toLowerCase() === 'l') document.getElementById('deal-lost-btn')?.click();
        }
    });
});

async function handleDealClose(el) {
    const status = el.dataset.status || (el.id === 'deal-won-btn' ? 'won' : 'lost');
    const pipelineId = el.dataset.pipelineId || getQueryParam('id');
    const action = el.dataset.action;

    if (!pipelineId) {
    alert('Missing pipeline id.');
    return;
}

// For "lost" ask for a reason
let reason = null;
if (status === 'lost') {
    reason = prompt('Reason for losing the deal?', '') ?? '';
    if (!reason.trim()) {
        alert('Please provide a reason to mark the deal as lost.');
        return;
    }
}

    setLoading(el, true);
    try {
    const payload = { pipeline_company_id: pipelineId, status, reason };
    const bearerToken = document.body.getAttribute('session_id');

    const res = await fetch(action, {
    method: 'POST',
    headers: headersWithCsrf({ 'Content-Type': 'application/json', "Authorization": "Bearer " + bearerToken  }),
    credentials: 'same-origin',
    body: JSON.stringify(payload)
});

    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json().catch(() => ({}));

    // Adjust these checks to your API shape
    if (data.success === false) {
    throw new Error(data.message || 'Request failed');
}

    if (status === 'open') {
        alert('Deal have been reopened.');
    } else {
        alert(status === 'won' ? 'Deal marked as WON.' : 'Deal marked as LOST.');
    }
    // Refresh to reflect new state (or navigate as you prefer)
    } catch (err) {
        alert('Could not update deal. ' + (err.message || 'Unknown error'));
    } finally {
        setLoading(el, false);
    }
}

    function headersWithCsrf(base = {}) {
    // If you use a meta tag like: <meta name="csrf-token" content="...">
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) base['X-CSRF-TOKEN'] = token;
    return base;
}

function getQueryParam(key) {
    return new URLSearchParams(location.search).get(key);
}

function setLoading(el, isLoading) {
    const btn = el.matches('button, a') ? el : el.querySelector('button, a');
    if (!btn) return;

    if (isLoading) {
    btn.dataset.originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                     <span class="ms-1">Saving…</span>`;
    } else {
        if (btn.dataset.originalText) btn.innerHTML = btn.dataset.originalText;
        btn.disabled = false;
    }
}
// Modal for delete confirmation
function showDeletePipelineModal(btn, pipelineId, infoEl) {
    let modal = document.getElementById('deletePipelineModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'deletePipelineModal';
        modal.innerHTML = `
            <div style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:9999;display:flex;align-items:center;justify-content:center;">
                <div style="background:#fff;padding:2rem;border-radius:8px;min-width:300px;box-shadow:0 2px 16px rgba(0,0,0,0.2);text-align:center;">
                    <h5>Delete Pipeline</h5>
                    <p>Are you sure you want to delete this pipeline?</p>
                    <p id="deletePipelineInfo">${infoEl}</p>
                    
                    <button id="deletePipelineCancel" class="btn btn-secondary">Cancel</button>
                    <button id="deletePipelineConfirm" class="btn btn-danger" style="margin-right:1rem;">Delete</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    modal.style.display = 'flex';
    modal.querySelector('#deletePipelineCancel').onclick = () => {
        modal.style.display = 'none';
        btn.disabled = false;
    };
    modal.querySelector('#deletePipelineConfirm').onclick = async () => {
        modal.style.display = 'none';
        btn.disabled = true;
        try {
            const bearerToken = document.body.getAttribute('session_id');
            const res = await fetch('https://api.coherta.com/pipeline', {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${bearerToken}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ pipeline_id: pipelineId })
            });
            if (!res.ok) throw new Error('Delete failed');
            alert('Pipeline deleted.');
            window.location.href = btn.getAttribute('data-delete-redirect') || 'leads.php';
        } catch (err) {
            alert('Could not delete pipeline. ' + (err.message || 'Unknown error'));
        } finally {
            btn.disabled = false;
        }
    };
}
document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('click', async (e) => {
        const btn = e.target.closest('.delete-pipeline[data-pipeline-id]');
        if (!btn) return;
        const pipelineId = btn.getAttribute('data-pipeline-id');
        if (!pipelineId) return;
        let infoEl = "";
        console.log(btn.dataset.count);
        if (btn.dataset.count) {
            try {
                const counts = JSON.parse(btn.dataset.count.replace(/&quot;/g, '"'));

                infoEl = counts
                    .map(({ total_leads, status }) => {
                        const statusText =
                            status == 0 ? 'Open' :
                                status == 1 ? 'Won'  :
                                    status == 2 ? 'Lost' : 'Unknown';

                        return `<div>Total leads: ${total_leads}, Status: ${statusText}</div>`;
                    })
                    .join('');
            } catch {
                infoEl = "";
            }
        }
        showDeletePipelineModal(btn, pipelineId, infoEl);
        //if (!confirm('Are you sure you want to delete this pipeline?')) return;
    });
});

document.body.addEventListener('submit', async (e) => {
    const form = e.target;
    const btn = form.querySelector('.btn-leadsListUpdate');
    if (!btn) return;
    e.preventDefault();
    const pipelineinput = form.querySelector('input[name="pipeline_id"]');
    const pipelineId = pipelineinput.value.trim();
    let newTitle = "Default Pipeline";
    if(pipelineId !== "-1") {
        const input = form.querySelector('input[name="leadListName"]');
        if (!pipelineinput || !input) return;
        newTitle = input.value.trim();
        if (!newTitle) {
            alert('Please enter a new title.');
            return;
        }
    }
    const checkedItems = Array.from(document.querySelectorAll('#column-list li input[type="checkbox"]:checked'))
        .map(cb => cb.closest('li'));
    const checkedColumns = checkedItems.map(li => li.getAttribute('data-column'));
    btn.disabled = true;
    try {
        const bearerToken = document.body.getAttribute('session_id');
        const res = await fetch('https://api.coherta.com/pipeline', {
            method: 'PATCH',
            headers: {
                'Authorization': `Bearer ${bearerToken}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ pipeline_id: pipelineId, title: newTitle, columns: checkedColumns })
        });
        if (!res.ok) throw new Error('Update failed');
        document.querySelector('h5.leads-pageTitle').textContent = `Leads Overview - ${newTitle}`;
        $('#editLeadListModal').modal('hide');
        window.location.reload();
    } catch (err) {
        alert('Could not update pipeline. ' + (err.message || 'Unknown error'));
    } finally {
        btn.disabled = false;
    }
});
