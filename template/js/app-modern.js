(function ($) {
    'use strict';

    function closeSidebar() {
        document.body.classList.remove('sidebar-open');
    }

    $(function () {
        $('[data-app-sidebar-toggle]').on('click', function () {
            document.body.classList.toggle('sidebar-open');
        });

        $('[data-app-sidebar-close]').on('click', closeSidebar);

        $('.app-sidebar a').on('click', function () {
            if (window.innerWidth < 768 && !$(this).attr('data-toggle')) {
                closeSidebar();
            }
        });

        $(window).on('resize', function () {
            if (window.innerWidth >= 768) {
                closeSidebar();
            }
        });

        $('.app-sidebar [data-toggle="collapse"]').on('click', function () {
            var $trigger = $(this);
            window.setTimeout(function () {
                $trigger.attr('aria-expanded', $($trigger.attr('href')).hasClass('in') ? 'true' : 'false');
            }, 220);
        });
    });
})(jQuery);
