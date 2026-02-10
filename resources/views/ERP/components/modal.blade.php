<div class="modal fade" id="{{$modal_id}}" data-backdrop="{{$dataBackDrop}}" tabindex="-1" aria-labelledby="{{$modal_id}}" aria-hidden="true">
    <div class="modal-dialog {{ $modal_class ?? 'modal-lg' }}">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h1 class="modal-title fs-5" id="{{$modal_id}}">{{$title}}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{$slot}}
            </div>
        </div>
    </div>
</div>
