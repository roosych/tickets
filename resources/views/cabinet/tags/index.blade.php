@extends('layouts.app')

@section('title', trans('common.tags.title'))

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">
                {{trans('common.mainpage')}}
            </a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">
            {{trans('common.tags.title')}}
        </li>
    </ul>
@endsection

@section('content')
    <div class="card card-flush mb-6">
        <div class="card-body py-5 d-flex flex-wrap align-items-center gap-4">
            <div class="d-flex align-items-center position-relative">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="tag_search" class="form-control form-control-solid w-250px ps-12" placeholder="{{trans('common.tags.search')}}">
            </div>
            <div class="w-200px">
                <select id="tag_sort" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                    <option value="name">{{trans('common.tags.sort_name')}}</option>
                    <option value="tickets">{{trans('common.tags.sort_tickets')}}</option>
                </select>
            </div>
            <label class="form-check form-check-custom form-check-solid form-switch">
                <input class="form-check-input" type="checkbox" id="tag_unused_only">
                <span class="form-check-label text-gray-700">{{trans('common.tags.unused')}}</span>
            </label>
            <div class="ms-auto text-muted fs-7">{{$tags->count()}}</div>
        </div>
    </div>

    <div class="row g-6 g-xl-9 mb-6 mb-xl-9" id="tags_grid">
        @can('create', \App\Models\Tag::class)
            <div class="col-md-6 col-lg-4 col-xl-3 tag_add_card">
                <div class="card h-100 flex-center p-8">
                    <button type="button" class="btn btn-clear d-flex flex-column flex-center" id="tag_add_btn"
                            data-bs-toggle="modal"
                            data-bs-target="#modal_add_tag"
                    >
                        <img src="{{asset('assets/media/misc/2.svg')}}" alt="" class="mw-100 mh-100px mb-7">
                        <div class="fw-bold fs-3 text-gray-600 text-hover-primary">
                            {{trans('common.tags.add_tag')}}
                        </div>
                    </button>
                </div>
            </div>
        @endcan

        @foreach($tags as $tag)
            <div class="col-md-6 col-lg-4 col-xl-3 tag_item tag_item_{{$tag->id}}"
                 data-name="{{mb_strtolower($tag->text)}}"
                 data-count="{{$tag->tickets_count}}">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column p-8">
                        <div class="d-flex align-items-center mb-3">
                            <span class="w-12px h-12px rounded-circle me-3 flex-shrink-0" style="background-color: {{$tag->color ?: '#6C757D'}}"></span>
                            <div class="fs-4 fw-bold text-break tag_title">{{$tag->text}}</div>
                        </div>

                        <div class="d-flex align-items-end gap-4 mb-2">
                            <div class="fs-2hx fw-bold lh-1 {{$tag->tickets_count ? 'text-gray-800' : 'text-gray-400'}}">{{$tag->tickets_count}}</div>
                            <div class="text-gray-500 fw-semibold fs-6">
                                {{mb_strtolower(trans('common.tags.tickets'))}}
                                @if($tag->open_tickets_count)
                                    · <span class="text-primary">{{$tag->open_tickets_count}} {{trans('common.tags.open')}}</span>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-auto pt-6">
                            @can('update', $tag)
                                <button class="btn btn-sm btn-light-primary edit_tag"
                                        data-id="{{$tag->id}}" data-name="{{$tag->text}}" data-color="{{$tag->color ?: '#6C757D'}}">
                                    {{trans('common.tags.edit')}}
                                </button>
                            @endcan
                            @can('delete', $tag)
                                <button class="btn btn-sm btn-light-danger delete_tag"
                                        data-name="{{$tag->text}}" data-id="{{$tag->id}}" data-count="{{$tag->tickets_count}}">
                                    {{trans('common.tags.delete_tag')}}
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div id="tags_empty" class="text-center text-muted py-10 {{$tags->isEmpty() ? '' : 'd-none'}}">
        {{trans('common.tags.empty')}}
    </div>

    @include('partials.modals.tags.add_tag')
@endsection



@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/plugins/datatables.bundle.js')}}"></script>
@endpush

@push('modals')

@endpush

@push('custom_js')
    <script>
        let token = $('meta[name="csrf-token"]').attr('content');

        function setColor(color) {
            $('#tag_color').val(color);
            $('#tag_palette [data-color]').removeClass('border-primary').addClass('border-white')
                .filter(function () { return $(this).data('color').toLowerCase() === color.toLowerCase(); })
                .removeClass('border-white').addClass('border-primary');
        }

        @can('create', \App\Models\Tag::class)
            let form = $('#add_tag_form');
            let modal = $('#modal_add_tag');
            let button = $('#add_tag_submit_btn');
            const $title = $('#tag_modal_title');

            $('#tag_palette').on('click', '[data-color]', function () { setColor($(this).data('color')); });
            $('#tag_color').on('input', function () { setColor(this.value); });

            $('#tag_add_btn').on('click', function () {
                $('#tag_id').val('');
                form.trigger('reset');
                $title.text($title.data('add'));
                setColor('#3E97FF');
            });

            $(document).on('click', '.edit_tag', function () {
                $('#tag_id').val($(this).data('id'));
                $('#tag_text').val($(this).data('name'));
                $title.text($title.data('edit'));
                setColor(String($(this).data('color')));
                modal.modal('show');
            });

            button.on('click', function(e){
                e.preventDefault();
                const tagId = $('#tag_id').val();
                applyWait($('body'));
                $.ajax({
                    url: tagId
                        ? '{{route('cabinet.tags.update', ':id')}}'.replace(':id', tagId)
                        : '{{route('cabinet.tags.store')}}',
                    method: tagId ? 'PUT' : 'POST',
                    headers: {'X-CSRF-TOKEN': token},
                    data: form.serialize(),
                    success: function(response)
                    {
                        if(response.success) {
                            form.trigger('reset');
                            location.reload();
                        } else {
                            removeWait($('body'));
                            Swal.fire('{{trans('common.swal.error_title')}}', '{{trans('common.swal.error_text')}}', 'error');
                        }
                    },
                    error: function (response)
                    {
                        removeWait($('body'));
                        let errorMessage = '';
                        if (response.status === 422) {
                            const errors = response.responseJSON.errors;
                            for (const key in errors) {
                                errorMessage += `<p>${errors[key][0]}</p>`;
                            }
                        }
                        Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                    },
                });
            });
        @endcan

        // search / sort / unused filter
        function applyTagFilters() {
            const q = ($('#tag_search').val() || '').toLowerCase().trim();
            const unusedOnly = $('#tag_unused_only').prop('checked');
            const sort = $('#tag_sort').val();
            const $grid = $('#tags_grid');
            const $items = $grid.find('.tag_item').get();

            $items.sort(function (a, b) {
                if (sort === 'tickets') {
                    return Number($(b).data('count')) - Number($(a).data('count')) || String($(a).data('name')).localeCompare(String($(b).data('name')));
                }
                return String($(a).data('name')).localeCompare(String($(b).data('name')));
            });
            $grid.append($items);

            let visible = 0;
            $items.forEach(function (el) {
                const $el = $(el);
                const ok = (!q || String($el.data('name')).includes(q)) && (!unusedOnly || Number($el.data('count')) === 0);
                $el.toggle(ok);
                if (ok) visible++;
            });
            $('#tags_empty').toggleClass('d-none', visible > 0)
                .text(visible === 0 && $('.tag_item').length ? '{{trans('common.tags.nothing_found')}}' : '{{trans('common.tags.empty')}}');
        }
        $('#tag_search').on('keyup', applyTagFilters);
        $('#tag_unused_only').on('change', applyTagFilters);
        $('#tag_sort').on('change', applyTagFilters);

        //delete tag
        $(document).on('click', '.delete_tag', function (){
            let tag_id = $(this).data('id');
            let tag_name = $(this).data('name');
            let tag_count = Number($(this).data('count'));
            let confirmText = tag_count > 0
                ? `{{trans('common.tags.swal_text_used')}}`.replace(':name', $('<div>').text(tag_name).html()).replace(':count', tag_count)
                : `{{trans('common.tags.swal_text')}}`;
            let item = $('.tag_item_' + tag_id);
            Swal.fire({
                html: confirmText,
                icon: "info",
                buttonsStyling: false,
                showCancelButton: true,
                confirmButtonText: "{{trans('common.swal.yes')}}",
                cancelButtonText: '{{trans('common.swal.no')}}',
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: 'btn fw-bold btn-active-light-primary'
                }
            }).then(async (result) => {
                if(result.value) {
                    try {
                        applyWait($('body'));
                        const response = await $.ajax({
                            url: '{{route('cabinet.tags.destroy', ':id')}}'.replace(':id', tag_id),
                            method: "DELETE",
                            headers: {'X-CSRF-TOKEN': token},
                            success: function(response)
                            {
                                if (response.success) {
                                    removeWait($('body'));
                                    Swal.fire('{{trans('common.swal.success_title')}}', '{{trans('common.tags.tag')}} <b>"' + tag_name + '"</b> {{trans('common.tags.tag_deleted')}}.', 'success');
                                    setTimeout(function(){
                                        Swal.close();
                                        item.remove();
                                    }, 1000)
                                } else {
                                    removeWait($('body'));
                                    Swal.fire('{{trans('common.swal.error_title')}}', '{{trans('common.swal.error_text')}}', 'error');
                                }
                            },
                            error: function (response)
                            {
                                removeWait($('body'));
                                Swal.fire('{{trans('common.swal.error_title')}}', '{{trans('common.swal.error_text')}}', 'error');
                            },
                        });
                        //console.log(response.status)
                    } catch (error) {
                        removeWait($('body'));
                        Swal.fire('{{trans('common.swal.error_title')}}', '{{trans('common.swal.error_text')}}', 'error');
                    }
                }
            });
        });
    </script>
@endpush
