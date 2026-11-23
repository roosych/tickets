$(function() {
    let SettingsUsers = (function() {
        let table, datatable;

        const matchers = {
            all: () => true,
            active: row => Number(row.attr('data-active')) === 1,
            inactive: row => Number(row.attr('data-active')) === 0,
            hidden: row => Number(row.attr('data-hidden')) === 1,
            approver: row => Number(row.attr('data-approver')) === 1,
            inactive_tickets: row => Number(row.attr('data-active')) === 0 && Number(row.attr('data-open')) > 0
        };

        function init() {
            table = $("#user_report_table");
            if (!table.length) return;

            datatable = table.DataTable({
                info: false,
                order: [],
                pageLength: 25,
                columnDefs: [{ orderable: false, targets: [] }]
            });

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable !== table[0]) return true;
                const row = $(datatable.row(dataIndex).node());
                const dept = $('#filterDept').val();
                const rowDept = String(row.attr('data-dept') || '');
                if (dept === 'none' ? rowDept !== '' : (dept && rowDept !== dept)) return false;
                return (matchers[$('#filterStatus').val()] || matchers.all)(row);
            });

            $('[user-report-filter="search"]').on("keyup", function() {
                datatable.search($(this).val()).draw();
            });
            $('#filterDept, #filterStatus').on('change', () => datatable.draw());

            $(document).on('click', '[data-quick-filter]', function(e) {
                e.preventDefault();
                $('#filterStatus').val($(this).data('quick-filter')).trigger('change');
            });

            $(document).on('click', '[data-dept-filter]', function(e) {
                e.preventDefault();
                $('#filterStatus').val('active').trigger('change');
                $('#filterDept').val(String($(this).data('dept-filter'))).trigger('change');
            });

            initDepartmentModal();
        }

        // Привязка сотрудника к департаменту тикет-системы прямо из таблицы
        function initDepartmentModal() {
            const $modal = $('#modal_user_department');
            if (!$modal.length) return;

            const $select = $('#udm_department');
            const $save = $('#udm_save');
            const modal = new bootstrap.Modal($modal[0]);
            let $current = null; // ссылка в ячейке, по которой кликнули

            $select.select2({ dropdownParent: $modal, width: '100%' });

            const norm = s => String(s || '').trim().toLowerCase();

            function findByName(name) {
                const target = norm(name);
                if (!target) return null;
                const opt = $select.find('option').filter(function() {
                    return this.value && norm($(this).text().replace(/ \(неактивен\)$/, '')) === target;
                }).first();
                return opt.length ? opt.val() : null;
            }

            $(document).on('click', '[data-dept-edit]', function(e) {
                e.preventDefault();
                $current = $(this);
                const deptId = String($current.attr('data-dept-id') || '');
                const adDept = $current.attr('data-ad-dept') || '';

                $('#udm_user_name').text($current.attr('data-user-name'));
                $('#udm_ad_dept').text(adDept ? 'В AD: ' + adDept : 'В AD департамент не указан');

                let value = deptId;
                let hint = '';
                if (!deptId) {
                    const match = findByName(adDept);
                    if (match) {
                        value = match;
                        hint = 'Подобран по названию из AD — проверьте перед сохранением.';
                    }
                }
                $select.val(value).trigger('change');
                $('#udm_hint').text(hint);
                modal.show();
            });

            function toast(ok, text) {
                const kind = ok ? 'Success' : 'Error';
                $('#toastTitle' + kind).text(ok ? 'Success' : 'Error');
                $('#toastMessage' + kind).text(text);
                new bootstrap.Toast($('#toast' + kind)[0], { delay: 4000 }).show();
            }

            $save.on('click', function() {
                if (!$current) return;
                const userId = $current.attr('data-user-id');
                const wasEmpty = !$current.attr('data-dept-id');

                $save.attr('data-kt-indicator', 'on').prop('disabled', true);
                $.ajax({
                    url: window.userDepartmentUrl.replace(':id', userId),
                    type: 'POST',
                    data: { department_id: $select.val() || '' },
                    success: function(res) {
                        const $td = $current.closest('td');
                        const $row = $td.closest('tr');
                        const name = $select.val() ? $select.find('option:selected').text() : '';

                        $row.attr('data-dept', res.department_id || '');
                        $td.attr('data-order', name);
                        $td.html(res.html);
                        datatable.row($row[0]).invalidate('dom').draw(false);

                        // Обновляем счётчик в плашке «без департамента» (считаются только активные)
                        if (Number($row.attr('data-active')) === 1 && wasEmpty !== !res.department_id) {
                            const $count = $('#noDeptCount');
                            const n = Math.max(0, Number($count.text()) + (res.department_id ? -1 : 1));
                            $count.text(n);
                            $('#noDeptAlert').toggleClass('d-none', n === 0);
                        }

                        modal.hide();
                        toast(true, 'Департамент сохранён');
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Не удалось сохранить департамент.';
                        toast(false, msg);
                    },
                    complete: function() {
                        $save.removeAttr('data-kt-indicator').prop('disabled', false);
                    }
                });
            });
        }

        return { init: init };
    })();

    SettingsUsers.init();
});
