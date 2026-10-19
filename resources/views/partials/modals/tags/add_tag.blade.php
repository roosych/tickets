<div class="modal fade" id="modal_add_tag" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="tag_modal_title" data-add="{{trans('common.tags.add_tag')}}" data-edit="{{trans('common.tags.edit_tag')}}">
                    {{trans('common.tags.add_tag')}}
                </h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body mx-lg-5 my-7">
                <form id="add_tag_form" class="form" method="POST">
                    @csrf
                    <input type="hidden" name="tag_id" id="tag_id" value="">
                    <div class="d-flex flex-column scroll-y me-n7 pe-7" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-offset="300px">
                        <div class="row">
                            <label class="fs-5 fw-bold form-label mb-2">
                                <span class="required">
                                    {{trans('common.tags.tag_title')}}
                                </span>
                            </label>
                            <div class="fv-row mb-10">
                                <input class="form-control" id="tag_text" maxlength="100" placeholder="{{trans('common.tags.tag_title')}}" name="text" autocomplete="false"/>
                            </div>
                        </div>
                        <div class="row">
                            <label class="fs-5 fw-bold form-label mb-2">
                                <span>{{trans('common.tags.tag_color')}}</span>
                            </label>
                            <div class="fv-row mb-10">
                                <div class="d-flex flex-wrap gap-2 mb-4" id="tag_palette">
                                    @foreach(['#3E97FF', '#17C653', '#F8285A', '#F6C000', '#7239EA', '#FF6B00', '#0DB5A8', '#6C757D'] as $swatch)
                                        <button type="button" class="btn btn-icon w-30px h-30px rounded-circle border border-2 border-white shadow-sm"
                                                style="background-color: {{$swatch}}" data-color="{{$swatch}}"></button>
                                    @endforeach
                                </div>
                                <input class="form-control form-control-lg rounded-2" type="color" name="color" id="tag_color" value="#3E97FF" autocomplete="false"/>
                            </div>
                        </div>
                    </div>
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">
                            {{trans('common.roles.buttons.cancel')}}
                        </button>
                        <button id="add_tag_submit_btn" type="submit" class="btn btn-primary" data-kt-roles-modal-action="submit">
                            <span class="indicator-label">
                                {{trans('common.roles.buttons.save')}}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
