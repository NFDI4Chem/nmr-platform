@env(['local', 'development'])
<div class="bg-red-500 block font-medium text-sm text-white py-2" style="position:fixed; bottom:0; left:0; width:100%; z-index:1000001; text-align:center;">
    You are currently viewing the site in {{ strtoupper(env('APP_ENV')) }} mode. Please note: Data will be reset periodically.
</div>
@endenv