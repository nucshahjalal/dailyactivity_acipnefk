<style>
    .card {
        width: 100% !important;
        /* Forces the card to take full width */
    }

    /* Add overflow styling to the card-body */
    .card-body {
        overflow-x: auto;
    }

    /* Ensure the header uses flexbox for alignment */
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table {
        width: 100% !important;
        /* Ensures table takes full width */
        margin: 0 !important;
    }

    .table-container {
        overflow-x: auto;
        /* Enables horizontal scrolling */
        max-width: 100%;
        /* Ensures container fits within modal width */
    }

    .modal-dialog {
        max-width: 90vw;
        /* Adjust width as needed */
        width: auto;
        /* Allows modal to resize based on content */
    }

    .modal-body {
        overflow-x: auto;
        /* Enables horizontal scrolling within the modal */
    }

    #table img {
        max-width: 100%;
        /* Ensures images fit within their cells */
        height: auto;
        /* Maintains aspect ratio */
    }
</style>
<table id="table" class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Rated By</th>
            <th>Rate</th>
            <th>Details</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Purpose</th>
            {{-- <th>Territory</th> --}}
            <th>Created Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rates as $key => $rate)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $rate->user_id }}</td>
                <td>{{ $rate->rate }}</td>
                <td>{{ $rate->details }}</td>
                <td>{{ $rate->start_date }}</td>

                <td>{{ $rate->end_date }}</td>
                <td>{{ $rate->purpose }}</td>
                {{-- <td>{{ $rate->territory }}</td> --}}
                <td>{{ $rate->created_at->format('Y-m-d') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
