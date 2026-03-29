
<script>
        @if(Session::has('message'))
    var type="{{Session::get('alert-type','info')}}"

    switch(type){
        case 'success':
            toastr.success("{{ Session::get('message') }}");
            break;
        case 'error':
            toastr.error("{{ Session::get('message') }}");
            break;
    }
    @endif
</script>

