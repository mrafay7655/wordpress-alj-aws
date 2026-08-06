document.addEventListener('DOMContentLoaded', () => {
    initTabs();
// 	changeRentalProductBtnText();
	addClassOnShopBreadcrumb();
	mapTabs();
})

function initElementorSlideProgress() {

    const wrapper = document.querySelector('.elementor-slides-wrapper');

    // Swiper not ready yet
    if (!wrapper || !wrapper.swiper) {
        return false;
    }

    const swiper = wrapper.swiper;

    const autoplayDuration = 5000;
    const transitionSpeed = 1500;
    const totalSlides = 3; // 👈 hard-coded total (loop safe)

    const bar = document.querySelector('.slide-progress-bar');
    const current = document.querySelector('.slide-current');
    const total = document.querySelector('.slide-total');

    if (!bar || !current || !total) return true;

    // set fixed total
    total.textContent = String(totalSlides).padStart(2, '0');

    function resetBar() {
        bar.style.transition = 'none';
        bar.style.width = '0%';
    }

    function animateBar() {
        bar.style.transition = `width ${autoplayDuration}ms linear`;
        bar.style.width = '100%';
    }

    function updateCounter() {
        // realIndex is loop-safe
        current.textContent = String(swiper.realIndex + 1).padStart(2, '0');
    }

    // init
    updateCounter();
    resetBar();
    setTimeout(animateBar, transitionSpeed);

    swiper.on('slideChangeTransitionStart', () => {
        resetBar();
        updateCounter();
    });

    swiper.on('slideChangeTransitionEnd', () => {
        animateBar();
    });

    return true;
}

/* ✅ Elementor-safe initializer */
document.addEventListener('DOMContentLoaded', function () {

    const checkSwiper = setInterval(() => {
        if (initElementorSlideProgress()) {
            clearInterval(checkSwiper);
        }
    }, 100);

});

// Custom Tabs 
function initTabs() {
    const tabs = document.querySelectorAll('.man-tabs ul li');
    const tabContents = document.querySelectorAll('.tableData');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab');
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            tabContents.forEach(content => content.classList.remove('active'));
            document.querySelector(`.tableData[data-tab="${target}"]`).classList.add('active');
        });
    });
}


// add active class to category list
document.addEventListener("DOMContentLoaded", () => {
  const currentUrl = window.location.href.replace(/\/$/, "");

  const svgIcon = `
    <svg class="active-icon" xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11" fill="none">
      <path d="M8.8486 9.8125H3.6456V8.84758H8.17694L0.353516 1.02416L1.02518 0.3525L8.8486 8.17592V3.64458H9.81352V9.8125H8.8486Z"
            fill="#140A9A" stroke="#140A9A" stroke-width="0.5"/>
    </svg>
  `;

  document.querySelectorAll(".subcategory-card").forEach(link => {
    const linkUrl = link.href.replace(/\/$/, "");

    if (currentUrl === linkUrl || currentUrl.startsWith(linkUrl + "/")) {
      link.classList.add("active");

      if (!link.querySelector(".active-icon")) {
        link.insertAdjacentHTML("afterbegin", svgIcon);
      }
    }
  });
});




// Change Button Text 
document.addEventListener('DOMContentLoaded', () => {
    const productTitleEl = document.querySelector('h2.product_title');
    const buttonSpan = document.querySelector(
        '.brochureBtn.global-btn a span.elementor-button-text'
    );

    // Exit safely if elements are missing
    if (!productTitleEl || !buttonSpan) return;

    const productName = productTitleEl.textContent.trim();
    if (!productName) return;

    // Arabic detection via RTL body class
    const isArabic = document.body.classList.contains('rtl');

    if (isArabic) {
        buttonSpan.textContent = `ملف المواصفات ${productName}`;
    } else {
        buttonSpan.textContent = `${productName} Brochure Download`;
    }
});




// function changeRentalProductBtnText() {
//     const buttons = document.querySelectorAll('.productsGridGlobalStyled .elementor-widget-button a span.elementor-button-text');
//     const isRTL = document.documentElement.dir === 'rtl'; // check if page is RTL

//     buttons.forEach(button => {
//         const productCard = button.closest('.e-loop-item'); // adjust wrapper if needed

//         if (productCard && productCard.classList.contains('product_cat-rental-solutions')) {
//             button.textContent = isRTL ? 'استفسر عن هذا المنتج' : 'Inquire About This Product';
//         }
//     });
// }
// 
(function () {
    // Run only on RTL pages
    if (!document.body.classList.contains('rtl')) return;

    const NEW_TEXT = 'عرض المنتج';
    const SELECTOR = '.productsGridGlobalStyled .elementor-widget-button a span.elementor-button-text';

    function updateButtonText(root = document) {
        const buttons = root.querySelectorAll(SELECTOR);

        buttons.forEach(btn => {
            // Prevent reprocessing
            if (btn.dataset.rtlUpdated) return;

            btn.textContent = NEW_TEXT;
            btn.dataset.rtlUpdated = 'true';
        });
    }

    // Initial run
    updateButtonText();

    // Watch for pagination / AJAX injected content
    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            mutation.addedNodes.forEach(node => {
                if (node.nodeType === 1) {
                    updateButtonText(node);
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
})();


(function () {

    const TARGET_SELECTOR = '.productsFilterSec';

    function attachScrollListeners() {
        const buttons = document.querySelectorAll(
            '.productsFilterSec .elementor-pagination a'
        );

        buttons.forEach(button => {
            if (!button.dataset.listenerAttached) {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    window.scrollTo({
                        top: 650,
                        behavior: 'smooth'
                    });
                });

                button.dataset.listenerAttached = 'true';
            }
        });
    }

    function runAll() {
        attachScrollListeners();

        if (typeof window.changeRentalProductBtnText === 'function') {
            window.changeRentalProductBtnText();
        }
    }

    if (!document.querySelector(TARGET_SELECTOR)) return;

    runAll();

    const observer = new MutationObserver(() => {
        if (!document.querySelector(TARGET_SELECTOR)) return;
        runAll();
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

})();



// Add class on shop breadcrumb 
function addClassOnShopBreadcrumb() {
    const breadcrumb = document.querySelector('#breadcrumbs');
    if (!breadcrumb) return;

    const spans = breadcrumb.querySelectorAll('span');

    spans.forEach((span) => {
        const text = span.textContent.trim().toLowerCase();

        // Match "shop" OR "السوق"
        if (text === 'shop' || text === 'السوق') {
            span.classList.add('shop');

            // Add class to the chevron right after Shop / السوق
            const nextChevron = span.nextElementSibling;
            if (nextChevron && nextChevron.tagName === 'IMG') {
                nextChevron.classList.add('chev-shop');
            }
        }
    });
}


// (function() {
//     // Only run if body has class 'komatsu'
//     if (!document.body.classList.contains('komatsu')) return;

//     // Function to set the ascending filter
//     function applyAscendingSort() {
//         var komatsuSort = document.querySelector('select[name="sort_by_komatsu"]');

//         if (komatsuSort) {
//             // Only apply if no value is selected yet
//             if (!komatsuSort.value) {
//                 // Set the value to ascending operating_weight
//                 komatsuSort.value = 'acf/operating_weight_asc';

//                 // Trigger the change event to let WP Grid Builder apply the filter
//                 var event = new Event('change', { bubbles: true });
//                 komatsuSort.dispatchEvent(event);
//             }
//         }
//     }

//     // Run immediately in case grid is already loaded
//     applyAscendingSort();

//     // Observe DOM for AJAX-loaded grids
//     var observer = new MutationObserver(function(mutations) {
//         applyAscendingSort();
//     });

//     observer.observe(document.body, { childList: true, subtree: true });
// })();


// Brochure Download button
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.brochureBtn a').forEach(function (link) {
    link.setAttribute('download', '');
  });
});


// header sticky add class on body 
document.addEventListener("DOMContentLoaded", function () {
    const header = document.querySelector('.header');

    if (!header) return;

    const observer = new MutationObserver(() => {
        if (header.classList.contains('elementor-sticky--active')) {
            document.body.classList.add('header-is-sticky');
        } else {
            document.body.classList.remove('header-is-sticky');
        }
    });

    observer.observe(header, {
        attributes: true,
        attributeFilter: ['class']
    });
});

// rantal product btn text change 
jQuery(document).ready(function ($) {
  var textArabic = 'استفسر عن هذا المنتج';
  var textEnglish = 'Inquire About This Product';

  var $buttons = $(
    'body.rental-solutions .productsGridGlobalStyled .elementor-widget-button a .elementor-button-text'
  );

  if ($('body').hasClass('rtl')) {
    $buttons.text(textArabic);
  } else {
    $buttons.text(textEnglish);
  }
}); 


// Add validation message on contact form 7 multiselect
document.addEventListener('wpcf7invalid', function () {

  const isRTL =
    document.documentElement.dir === 'rtl' ||
    document.documentElement.lang.startsWith('ar');

  const message = isRTL
    ? 'يرجى ملء هذا الحقل.'
    : 'Please fill out this field.';

  document.querySelectorAll('.wpcf7-selct-multiselct').forEach(function (select) {

    const values = Array.from(select.selectedOptions).map(opt => opt.value).filter(v => v);

    // Only show error if no option is selected
    if (values.length === 0) {
      const wrap = select.closest('.wpcf7-form-control-wrap');
      if (!wrap) return;

      // Remove existing error (prevents duplicates)
      const existing = wrap.querySelector('.wpcf7-not-valid-tip');
      if (existing) existing.remove();

      const span = document.createElement('span');
      span.className = 'wpcf7-not-valid-tip';
      span.setAttribute('role', 'alert');
      span.innerText = message;

      wrap.appendChild(span);
    } else {
      // Remove error if field is filled
      const wrap = select.closest('.wpcf7-form-control-wrap');
      if (!wrap) return;
      const existing = wrap.querySelector('.wpcf7-not-valid-tip');
      if (existing) existing.remove();
    }

  });
});

// Products test change in arabic
document.addEventListener("DOMContentLoaded", function () {

    function fixBreadcrumb() {

        if (document.body.classList.contains('rtl')) {

            var items = document.querySelectorAll('#breadcrumbs span span');

            items.forEach(function(el) {
                if (el.textContent.trim() === 'Products') {
                    el.textContent = 'المنتجات';
                }
            });
        }
    }
    // Run after delay
    setTimeout(fixBreadcrumb, 500);

});


function mapTabs(){
    const tabs = document.querySelectorAll(".mapTabs .elementor-column");
    const sections = document.querySelectorAll(".locations-map-text");

    const types = ["machinery", "material", "rental"];

    tabs.forEach(tab => {
        tab.addEventListener("click", function () {

            let selectedType = "";

            // Detect type from tab classes
            types.forEach(type => {
                if (this.classList.contains(type)) {
                    selectedType = type;
                }
            });

            if (!selectedType) return;

            // Remove active from all map sections
            sections.forEach(sec => sec.classList.remove("active"));

            // Add active to matching section
            document.querySelectorAll(".locations-map-text." + selectedType)
                .forEach(sec => sec.classList.add("active"));

            // Optional: active tab UI
            tabs.forEach(t => t.classList.remove("active"));
            this.classList.add("active");

        });
    });
}


// Add colors on stores list 
(function () {

    function applyClasses() {

        document.querySelectorAll('ul li').forEach(function (li) {

            // prevent re-processing
            if (li.dataset.classProcessed === "1") return;

            const textEl = li.querySelector('.wpsl-street');
            if (!textEl) return;

            const text = textEl.textContent.toLowerCase().trim();

            if (text.includes('machinery')) {
                li.classList.add('machinery');
            }

            if (text.includes('materials handling')) {
                li.classList.add('material');
            }

            if (text.includes('rental solutions')) {
                li.classList.add('rental');
            }

            li.dataset.classProcessed = "1";
        });
    }

    // Initial run (for already loaded items)
    applyClasses();

    // Observe DOM changes
    const targetNode = document.body; // or more specific container if you know it

    const observer = new MutationObserver(function () {
        applyClasses();
    });

    observer.observe(targetNode, {
        childList: true,
        subtree: true
    });

})();





// Reordering stores list 
(function () {

    function applyAddressClasses() {
        document.querySelectorAll('#wpsl-stores ul li').forEach(function (li) {

            // Prevent re-processing
            if (li.dataset.addrClassDone === "1") return;

            const addressPara = li.querySelector('.wpsl-store-location p');
            if (!addressPara) return;

            // --- 1. HANDLE CITY (Works with Arabic & English) ---
            const strongTag = addressPara.querySelector('strong');
            if (strongTag) {
                const cityName = strongTag.textContent.trim()
                    .toLowerCase()
                    .replace(/\s+/g, '-')
                    // REMOVED: .replace(/[^a-z0-9-]/g, '') 
                    // This was deleting Arabic characters. 
                    // We now keep all characters but remove specific punctuation.
                    .replace(/[.,\/#!$%\^&\*;:{}=\_`~()]/g, "");
                
                if (cityName) {
                    li.classList.add(cityName);
                }
            }

            // --- 2. HANDLE CATEGORY (Second .wpsl-street span) ---
            const streetSpans = addressPara.querySelectorAll('.wpsl-street');
            if (streetSpans.length >= 2) {
                const categoryText = streetSpans[1].textContent.trim();
                if (categoryText) {
                    // Split by space or comma and take the first word
                    const words = categoryText.split(/[\s,]+/);
                    const firstWord = words[0].toLowerCase();
                    if (firstWord) {
                        li.classList.add(firstWord);
                    }
                }
            }
            
            // --- 3. ADD 'ADDRESS' CLASS TO FIRST SPAN ---
            if (streetSpans.length > 0) {
                streetSpans[0].classList.add('address');
            }

            // Mark as done
            li.dataset.addrClassDone = "1";
        });
    }

    function initObserver() {
        const target = document.querySelector('#wpsl-stores');
        if (!target) return;

        const observer = new MutationObserver(function (mutations) {
            const hasNewNodes = mutations.some(m =>
                Array.from(m.addedNodes).some(n => n.nodeType === 1)
            );

            if (hasNewNodes) {
                applyAddressClasses();
            }
        });

        observer.observe(target, {
            childList: true,
            subtree: true
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            applyAddressClasses();
            initObserver();
        });
    } else {
        applyAddressClasses();
        initObserver();
    }

})();



// Remove duplicates in store list on locations page 
const resultsContainer = document.querySelector('#wpsl-stores');

if (resultsContainer) {
    const observer = new MutationObserver(() => {
        const seen = new Set();

        resultsContainer.querySelectorAll('li[data-store-id]').forEach(li => {
            const storeId = li.dataset.storeId;

            if (seen.has(storeId)) {
                li.remove();
            } else {
                seen.add(storeId);
            }
        });
    });

    observer.observe(resultsContainer, {
        childList: true,
        subtree: true
    });
}