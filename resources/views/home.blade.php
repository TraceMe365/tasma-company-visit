@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    <div class="row">
                        <div class="input-group mb-3">
                            <div class="col-3">
                                <label for="from_date">From:</label>
                                <input type="date" name="from_date" id="from_date">
                            </div>
                            <div class="col-3">
                                <label for="to_date">To:</label>
                                <input type="date" name="to_date" id="to_date">
                            </div>
                            <div class="col-3">
                                <button class="btn btn-primary btn-sm" id="getReportBtn">Get Report</button>
                            </div>
                            <a href="{{ route('export.users') }}" class="btn btn-primary">Download Users</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $("#getReportBtn").on("click",function(){
        let from     = $("#from_date").val();
        let to       = $("#to_date").val();
        let fromUnix = moment(from).unix();
        let toUnix   = moment(to).unix();

        console.log(fromUnix)
        console.log(toUnix)
        
        $.ajax({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url:"{{ route('report.execute-by-range') }}",
            type: 'POST',
            data: {
                "from"      : fromUnix,
                "to"        : toUnix,
                "from_human": from,
                "to_human"  : to
            },
            success: function(response) {
                console.log(response);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        })
    });
</script>
@endsection
