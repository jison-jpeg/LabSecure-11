@props(['modalTitle', 'eventName'])

<div class="modal fade" id="verticalycentered" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{$modalTitle}}</h5>
                <button @click="$dispatch('{{$eventName}}-close')" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{$slot}}
            </div>
            <div class="modal-footer">
                <button @click="$dispatch('{{$eventName}}-close')" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button @click="$dispatch('{{$eventName}}')" type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>
