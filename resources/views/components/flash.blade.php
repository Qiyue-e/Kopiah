@if (session('success'))<div class="flash flash-ok mb-4">{{ session('success') }}</div>@endif
@if (session('warning'))<div class="flash flash-warn mb-4">{{ session('warning') }}</div>@endif
@if ($errors->any())
  <div class="flash flash-error mb-4">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
@endif
