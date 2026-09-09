/**
 * Global DataTable Debounce Configuration
 * Mencegah multiple AJAX requests saat user mengetik filter pencarian.
 */
(function ($) {
    'use strict';

    // 1. Generic Debounce Function
    function dtDebounce(func, wait) {
        let timeout;
        return function (...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    // Default Debounce Delay (ms)
    const SEARCH_DELAY_MS = 500;

    // 2. Global Defaults Override
    if ($.fn.dataTable) {
        $.extend(true, $.fn.dataTable.defaults, {
            searchDelay: SEARCH_DELAY_MS, // Native support DataTables
            initComplete: function (settings, json) {
                const api = this.api();
                const tableWrapper = $(api.table().container());

                // A. Debounce Global Search Filter (.dataTables_filter input)
                const globalSearchInput = tableWrapper.find('.dataTables_filter input');
                if (globalSearchInput.length) {
                    // Unbind event default bawaan DataTables
                    globalSearchInput.unbind('keyup.DT search.DT input.DT paste.DT cut.DT');

                    const debouncedGlobalSearch = dtDebounce(function (val) {
                        if (api.search() !== val) {
                            api.search(val).draw();
                        }
                    }, SEARCH_DELAY_MS);

                    globalSearchInput.on('input propertychange', function () {
                        debouncedGlobalSearch(this.value);
                    });
                }

                // B. Debounce Per-Column Filter (Class: .dt-column-filter)
                tableWrapper.find('.dt-column-filter').each(function () {
                    const input = $(this);
                    const colIdx = input.data('column-index') ?? input.parent().index();

                    const debouncedColumnSearch = dtDebounce(function (val) {
                        if (api.column(colIdx).search() !== val) {
                            api.column(colIdx).search(val).draw();
                        }
                    }, SEARCH_DELAY_MS);

                    input.on('input propertychange', function () {
                        debouncedColumnSearch(this.value);
                    });
                });
            }
        });
    }
})(jQuery);