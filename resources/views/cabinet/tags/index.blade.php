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
    <div class="row g-6 g-xl-9 mb-6 mb-xl-9">
        @can('create', \App\Models\Tag::class)
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 flex-center p-8">
                    <button type="button" class="btn btn-clear d-flex flex-column flex-center"
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
            <div class="col-md-6 col-lg-4 col-xl-3 tag_item_{{$tag->id}}">
                <div class="card h-100 ">
                    <div class="card-body d-flex justify-content-center text-center flex-column p-8">
                        <div class="fs-4 fw-bold mb-2">
                            {{$tag->text}}
                        </div>

                        <div class="fs-6 fw-semibold text-gray-500">
                            {{trans('common.tags.tickets')}}: {{count($tag->tickets)}}
                        </div>
                        @can('delete', $tag)
                            <div class="text-center mt-10">
                                <button class="btn btn-sm btn-light-danger delete_tag" data-name="{{$tag->text}}" data-id="{{$tag->id}}">
                                    {{trans('common.tags.delete_tag')}}
                                </button>
                            </div>
                        @endcan

                    </div>
                </div>
            </div>
        @endforeach
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

        @can('create', \App\Models\Tag::class)
            //add tag
            let form = $('#add_tag_form');
            let modal = $('#modal_add_tag');
            let button = $('#add_tag_submit_btn');
            button.on('click', function(e){
                e.preventDefault();
                applyWait($('body'));
                $.ajax({
                    url: '{{route('cabinet.tags.store')}}',
                    method: 'POST',
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

        //delete tag
        $(".delete_tag").on('click', function (){
            let tag_id = $(this).data('id');
            let tag_name = $(this).data('name');
            let item = $('.tag_item_' + tag_id);
            Swal.fire({
                html: `{{trans('common.tags.swal_text')}}`,
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
